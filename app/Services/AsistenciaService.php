<?php

namespace App\Services;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\AsistenciaEspacio;
use App\Models\AsistenciaToma;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Cursada;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    // -------------------------------------------------------------------------
    // Asistencia diaria (preceptoría)
    // -------------------------------------------------------------------------

    /** Estudiantes del curso en esa fecha (inscriptos y sin baja anterior), ordenados. */
    public function inscriptos(Curso $curso, Carbon $fecha): Collection
    {
        return Inscripcion::with('estudiante')
            ->where('curso_id', $curso->id)
            ->whereDate('fecha', '<=', $fecha)
            ->where(fn ($q) => $q->where('estado', Inscripcion::ACTIVA)->orWhereDate('fecha_baja', '>', $fecha))
            ->get()
            ->pluck('estudiante')
            ->sortBy(fn ($e) => mb_strtolower(Str::ascii($e->apellido . ' ' . $e->nombre)))
            ->values();
    }

    /** Verifica que se pueda tomar asistencia ese día en el curso. */
    public function validarFecha(Curso $curso, Carbon $fecha): void
    {
        $ciclo = $curso->ciclo;

        if ($ciclo->estaCerrado()) {
            throw ValidationException::withMessages(['fecha' => "El {$ciclo->nombre} está cerrado."]);
        }

        if ($fecha->isFuture()) {
            throw ValidationException::withMessages(['fecha' => 'No se puede tomar asistencia de un día futuro.']);
        }

        if ($fecha->lt($ciclo->fecha_inicio) || $fecha->gt($ciclo->fecha_fin)) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha está fuera del ciclo (' . $ciclo->fecha_inicio->format('d/m/Y') . ' al ' . $ciclo->fecha_fin->format('d/m/Y') . ').',
            ]);
        }
    }

    /**
     * Guarda (o reemplaza) la asistencia del día.
     *
     * @param array<int, array{estado:string, justificada?:bool, motivo?:?string}> $datos por estudiante_id
     */
    public function guardarToma(Curso $curso, Carbon $fecha, array $datos, ?int $userId): AsistenciaToma
    {
        $this->validarFecha($curso, $fecha);

        $validos = $this->inscriptos($curso, $fecha)->pluck('id')->all();

        return DB::transaction(function () use ($curso, $fecha, $datos, $userId, $validos) {
            // Se busca por fecha (whereDate) para no depender del formato guardado.
            $toma = AsistenciaToma::where('curso_id', $curso->id)->whereDate('fecha', $fecha)->first()
                ?? new AsistenciaToma(['curso_id' => $curso->id, 'fecha' => $fecha->toDateString()]);
            $toma->fill(['ciclo_lectivo_id' => $curso->ciclo_lectivo_id, 'user_id' => $userId])->save();

            foreach ($validos as $estudianteId) {
                $fila   = $datos[$estudianteId] ?? ['estado' => 'presente'];
                $estado = array_key_exists($fila['estado'] ?? '', Asistencia::ESTADOS) ? $fila['estado'] : 'presente';
                $justif = $estado !== 'presente' && ! empty($fila['justificada']);

                Asistencia::updateOrCreate(
                    ['asistencia_toma_id' => $toma->id, 'estudiante_id' => $estudianteId],
                    [
                        'fecha'       => $fecha->toDateString(),
                        'estado'      => $estado,
                        'justificada' => $justif,
                        'motivo'      => $estado !== 'presente' ? ($fila['motivo'] ?? null) : null,
                    ]
                );
            }

            return $toma;
        });
    }

    /** Totales de asistencia diaria de un estudiante en un ciclo. */
    public function resumenEstudiante(Estudiante $estudiante, ?CicloLectivo $ciclo): array
    {
        $registros = $ciclo
            ? Asistencia::where('estudiante_id', $estudiante->id)
                ->whereHas('toma', fn ($q) => $q->where('ciclo_lectivo_id', $ciclo->id))
                ->orderByDesc('fecha')
                ->get()
            : collect();

        return $this->totales($registros) + ['registros' => $registros];
    }

    /** @param Collection<Asistencia> $registros */
    public function totales(Collection $registros): array
    {
        $dias = $registros->count();
        $inasistencias = $registros->sum(fn ($a) => $a->valor);
        $justificadas  = $registros->where('justificada', true)->sum(fn ($a) => $a->valor);

        return [
            'dias'           => $dias,
            'inasistencias'  => $inasistencias,
            'justificadas'   => $justificadas,
            'injustificadas' => $inasistencias - $justificadas,
            'porcentaje'     => $dias ? round(($dias - $inasistencias) * 100 / $dias, 1) : null,
        ];
    }

    /** Asistencia por materia (GestiónAula) de un estudiante en un ciclo. */
    public function porMateria(Estudiante $estudiante, ?CicloLectivo $ciclo): Collection
    {
        if (! $ciclo) {
            return collect();
        }

        return AsistenciaEspacio::with(['espacio', 'docente'])
            ->where('estudiante_id', $estudiante->id)
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->get()
            ->groupBy('espacio_curricular_id')
            ->map(function ($lista) {
                $clases = $lista->count();
                $faltas = $lista->sum(fn ($a) => Asistencia::VALORES[$a->estado] ?? 0);

                return (object) [
                    'espacio'    => $lista->first()->espacio,
                    'docente'    => $lista->first()->docente,
                    'clases'     => $clases,
                    'faltas'     => $faltas,
                    'porcentaje' => $clases ? round(($clases - $faltas) * 100 / $clases, 1) : null,
                    'ultima'     => $lista->max('fecha'),
                ];
            })
            ->sortBy(fn ($r) => $r->espacio->nombre)
            ->values();
    }

    // -------------------------------------------------------------------------
    // Asistencia por materia recibida desde GestiónAula (API)
    // -------------------------------------------------------------------------

    /**
     * Registra una clase enviada por GestiónAula.
     *
     * @param array $datos docente_dni, fecha, espacio?, referencia?, asistencias[{dni, estado, observacion?}]
     */
    public function recibirClase(array $datos): array
    {
        $fecha = Carbon::parse($datos['fecha'])->startOfDay();

        if ($fecha->isFuture()) {
            throw ValidationException::withMessages(['fecha' => 'La fecha no puede ser futura.']);
        }

        $docente = Docente::where('documento', preg_replace('/\D/', '', $datos['docente_dni']))->first();
        if (! $docente) {
            throw ValidationException::withMessages(['docente_dni' => 'No hay un docente con ese DNI en la institución.']);
        }

        $ciclo = CicloLectivo::where('anio', $fecha->year)->first()
            ?? CicloLectivo::where('anio', $fecha->year - 1)->first(); // intensificación de febrero
        if (! $ciclo || $ciclo->estaCerrado()) {
            throw ValidationException::withMessages(['fecha' => 'No hay un ciclo lectivo abierto para esa fecha.']);
        }

        // Estudiantes por DNI.
        $dnis = collect($datos['asistencias'])->pluck('dni')->map(fn ($d) => preg_replace('/\D/', '', (string) $d))->filter()->unique();
        $estudiantes = Estudiante::where('tipo_documento', 'dni')->whereIn('documento', $dnis)->get()->keyBy('documento');

        // Inscripciones activas de esos estudiantes en el ciclo.
        $inscripciones = Inscripcion::where('ciclo_lectivo_id', $ciclo->id)
            ->where('estado', Inscripcion::ACTIVA)
            ->whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->get();

        $asignacion = $this->resolverAsignacion($docente, $ciclo, $inscripciones, $datos['espacio'] ?? null);

        // Estudiantes que corresponden a esa asignación.
        $delGrupo = $this->delGrupo($asignacion, $inscripciones);

        $referencia = mb_substr((string) ($datos['referencia'] ?? ''), 0, 64);
        $registrados = 0;
        $noEncontrados = [];
        $fueraDelGrupo = [];

        DB::transaction(function () use ($datos, $estudiantes, $delGrupo, $asignacion, $ciclo, $docente, $fecha, $referencia, &$registrados, &$noEncontrados, &$fueraDelGrupo) {
            foreach ($datos['asistencias'] as $fila) {
                $dni = preg_replace('/\D/', '', (string) $fila['dni']);
                $estudiante = $estudiantes->get($dni);

                if (! $estudiante) {
                    $noEncontrados[] = $dni;
                    continue;
                }

                if (! $delGrupo->contains($estudiante->id)) {
                    $fueraDelGrupo[] = $dni;
                    continue;
                }

                // Reenviar la misma clase (fecha + referencia) actualiza el registro.
                $registro = AsistenciaEspacio::where('espacio_curricular_id', $asignacion->espacio_curricular_id)
                    ->where('estudiante_id', $estudiante->id)
                    ->whereDate('fecha', $fecha)
                    ->where('referencia', $referencia)
                    ->first()
                    ?? new AsistenciaEspacio([
                        'espacio_curricular_id' => $asignacion->espacio_curricular_id,
                        'estudiante_id'         => $estudiante->id,
                        'fecha'                 => $fecha->toDateString(),
                        'referencia'            => $referencia,
                    ]);

                $registro->fill([
                        'ciclo_lectivo_id' => $ciclo->id,
                        'asignacion_id'    => $asignacion->id,
                        'curso_id'         => $asignacion->curso_id,
                        'docente_id'       => $docente->id,
                        'estado'           => $fila['estado'],
                        'observacion'      => isset($fila['observacion']) ? mb_substr($fila['observacion'], 0, 200) : null,
                        'origen'           => 'gestionaula',
                ])->save();
                $registrados++;
            }
        });

        return [
            'ok'              => true,
            'registrados'     => $registrados,
            'no_encontrados'  => $noEncontrados,
            'fuera_del_grupo' => $fueraDelGrupo,
            'asignacion'      => [
                'id'      => $asignacion->id,
                'espacio' => $asignacion->espacio->nombre,
                'grupo'   => $asignacion->grupo,
                'ciclo'   => $ciclo->anio,
            ],
        ];
    }

    /**
     * Encuentra a qué asignación del docente corresponde la clase:
     * 1) el curso donde están inscriptos la mayoría de los estudiantes enviados;
     * 2) dentro de ese curso (o de las carreras), el espacio por nombre o código;
     * 3) si el docente tiene un solo espacio ahí, ese.
     */
    public function resolverAsignacion(Docente $docente, CicloLectivo $ciclo, Collection $inscripciones, ?string $espacio): Asignacion
    {
        $asignaciones = Asignacion::with(['espacio', 'curso.nivel'])->activas()
            ->where('docente_id', $docente->id)
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->get();

        if ($asignaciones->isEmpty()) {
            throw ValidationException::withMessages(['docente_dni' => 'El docente no tiene espacios asignados en el ciclo.']);
        }

        $cursoMayoritario = $inscripciones->whereNotNull('curso_id')->countBy('curso_id')->sortDesc()->keys()->first();

        $candidatas = $cursoMayoritario
            ? $asignaciones->where('curso_id', $cursoMayoritario)
            : $asignaciones->whereNull('curso_id');

        if ($candidatas->isEmpty()) {
            throw ValidationException::withMessages([
                'asistencias' => 'Los estudiantes enviados no son de ningún curso o carrera donde el docente tenga espacios asignados.',
            ]);
        }

        if (filled($espacio)) {
            $buscado = $this->normalizar($espacio);
            $coinciden = $candidatas->filter(fn ($a) => $this->normalizar($a->espacio->nombre) === $buscado
                || ($a->espacio->codigo && $this->normalizar($a->espacio->codigo) === $buscado));

            if ($coinciden->count() === 1) {
                return $coinciden->first();
            }

            // Mismo espacio en varias comisiones: la comisión donde cursan los estudiantes enviados.
            if ($coinciden->count() > 1 && $coinciden->pluck('espacio_curricular_id')->unique()->count() === 1) {
                $comision = Cursada::where('espacio_curricular_id', $coinciden->first()->espacio_curricular_id)
                    ->where('ciclo_lectivo_id', $ciclo->id)
                    ->whereIn('estudiante_id', $inscripciones->pluck('estudiante_id'))
                    ->get()->countBy('comision')->sortDesc()->keys()->first();

                if ($comision && ($elegida = $coinciden->firstWhere('comision', $comision))) {
                    return $elegida;
                }
            }

            // Coincidencia parcial ("Matemática" en "Matemática y su Didáctica I") si es única.
            $parciales = $candidatas->filter(fn ($a) => str_contains($this->normalizar($a->espacio->nombre), $buscado));
            if ($parciales->count() === 1) {
                return $parciales->first();
            }
        } elseif ($candidatas->count() === 1) {
            return $candidatas->first();
        }

        throw ValidationException::withMessages([
            'espacio' => 'No se pudo identificar el espacio curricular. Opciones del docente: '
                . $candidatas->map(fn ($a) => $a->espacio->nombre . ' (' . $a->grupo . ')')->join('; ') . '.',
        ]);
    }

    /**
     * Estudiantes de la asignación: los del curso (escolar) o, en superior,
     * los inscriptos a la cursada de esa unidad y comisión (si todavía no hay
     * cursadas cargadas, los inscriptos a la carrera).
     */
    public function delGrupo(Asignacion $asignacion, Collection $inscripciones): Collection
    {
        if ($asignacion->curso_id) {
            return $inscripciones->where('curso_id', $asignacion->curso_id)->pluck('estudiante_id');
        }

        $cursadas = Cursada::where('espacio_curricular_id', $asignacion->espacio_curricular_id)
            ->where('ciclo_lectivo_id', $asignacion->ciclo_lectivo_id)
            ->where('comision', $asignacion->comision ?: 'Única')
            ->where('condicion', '!=', 'abandono')
            ->pluck('estudiante_id');

        return $cursadas->isNotEmpty()
            ? $cursadas
            : $inscripciones->where('plan_estudio_id', $asignacion->espacio->plan_estudio_id)->pluck('estudiante_id');
    }

    public function normalizar(string $texto): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtolower(Str::ascii($texto))));
    }
}
