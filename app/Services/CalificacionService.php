<?php

namespace App\Services;

use App\Enums\EscalaCalificacion;
use App\Models\Calificacion;
use App\Models\CicloLectivo;
use App\Models\Docente;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\InstitucionNivel;
use App\Models\PeriodoEvaluacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalificacionService
{
    public function __construct(protected AsistenciaService $asistencia)
    {
    }

    // -------------------------------------------------------------------------
    // Recepción desde GestiónAula (API)
    // -------------------------------------------------------------------------

    /**
     * @param array $datos docente_dni, espacio?, periodo, calificaciones[{dni, nota?, informe?, observacion?}]
     */
    public function recibir(array $datos): array
    {
        $docente = Docente::where('documento', preg_replace('/\D/', '', $datos['docente_dni']))->first();
        if (! $docente) {
            throw ValidationException::withMessages(['docente_dni' => 'No hay un docente con ese DNI en la institución.']);
        }

        $ciclo = CicloLectivo::enCurso()->first();
        if (! $ciclo) {
            throw ValidationException::withMessages(['periodo' => 'No hay un ciclo lectivo en curso.']);
        }

        $dnis = collect($datos['calificaciones'])->pluck('dni')->map(fn ($d) => preg_replace('/\D/', '', (string) $d))->filter()->unique();
        $estudiantes = Estudiante::where('tipo_documento', 'dni')->whereIn('documento', $dnis)->get()->keyBy('documento');
        $inscripciones = Inscripcion::where('ciclo_lectivo_id', $ciclo->id)
            ->where('estado', Inscripcion::ACTIVA)
            ->whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->get();

        $asignacion = $this->asistencia->resolverAsignacion($docente, $ciclo, $inscripciones, $datos['espacio'] ?? null);
        $asignacion->loadMissing(['curso.nivel', 'espacio.plan.nivel']);

        /** @var InstitucionNivel $nivel */
        $nivel   = $asignacion->curso?->nivel ?? $asignacion->espacio->plan->nivel;
        $periodo = $this->resolverPeriodo($ciclo, $nivel, $datos['periodo']);

        if ($periodo->cerrado) {
            throw ValidationException::withMessages(['periodo' => "El período \"{$periodo->nombre}\" está cerrado: no se reciben más notas."]);
        }

        $escala = $nivel->escalaCalificacion();
        $delGrupo = $asignacion->curso_id
            ? $inscripciones->where('curso_id', $asignacion->curso_id)->pluck('estudiante_id')
            : $inscripciones->where('plan_estudio_id', $asignacion->espacio->plan_estudio_id)->pluck('estudiante_id');

        $res = ['registradas' => 0, 'no_encontrados' => [], 'fuera_del_grupo' => [], 'invalidas' => [], 'corregidas_en_secretaria' => []];

        DB::transaction(function () use ($datos, $estudiantes, $delGrupo, $asignacion, $ciclo, $periodo, $docente, $escala, &$res) {
            foreach ($datos['calificaciones'] as $fila) {
                $dni = preg_replace('/\D/', '', (string) $fila['dni']);
                $estudiante = $estudiantes->get($dni);

                if (! $estudiante) {
                    $res['no_encontrados'][] = $dni;
                    continue;
                }
                if (! $delGrupo->contains($estudiante->id)) {
                    $res['fuera_del_grupo'][] = $dni;
                    continue;
                }

                $valor = $numerico = $informe = null;

                if ($escala === EscalaCalificacion::Informe) {
                    $informe = trim((string) ($fila['informe'] ?? $fila['nota'] ?? ''));
                    if ($informe === '') {
                        $res['invalidas'][] = $dni;
                        continue;
                    }
                } else {
                    $normal = $escala->normalizar($fila['nota'] ?? null);
                    if (! $normal) {
                        $res['invalidas'][] = $dni;
                        continue;
                    }
                    [$valor, $numerico] = $normal;
                }

                $existente = Calificacion::where('estudiante_id', $estudiante->id)
                    ->where('espacio_curricular_id', $asignacion->espacio_curricular_id)
                    ->where('periodo_evaluacion_id', $periodo->id)
                    ->first();

                // Lo que corrigió la secretaría no se pisa desde GestiónAula.
                if ($existente?->esManual()) {
                    $res['corregidas_en_secretaria'][] = $dni;
                    continue;
                }

                ($existente ?? new Calificacion([
                    'estudiante_id'         => $estudiante->id,
                    'espacio_curricular_id' => $asignacion->espacio_curricular_id,
                    'periodo_evaluacion_id' => $periodo->id,
                ]))->fill([
                    'ciclo_lectivo_id' => $ciclo->id,
                    'curso_id'         => $asignacion->curso_id,
                    'docente_id'       => $docente->id,
                    'valor'            => $valor,
                    'valor_numerico'   => $numerico,
                    'informe'          => $informe,
                    'observacion'      => isset($fila['observacion']) ? mb_substr($fila['observacion'], 0, 255) : null,
                    'origen'           => 'gestionaula',
                ])->save();

                $res['registradas']++;
            }
        });

        return ['ok' => true] + $res + [
            'asignacion' => ['espacio' => $asignacion->espacio->nombre, 'grupo' => $asignacion->grupo],
            'periodo'    => $periodo->nombre,
            'escala'     => $escala->value,
        ];
    }

    /** Período por número de orden (1, 2, 3) o por nombre ("2° Trimestre"). */
    protected function resolverPeriodo(CicloLectivo $ciclo, InstitucionNivel $nivel, mixed $periodo): PeriodoEvaluacion
    {
        $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)
            ->where('institucion_nivel_id', $nivel->id)
            ->orderBy('orden')
            ->get();

        $encontrado = is_numeric($periodo)
            ? $periodos->firstWhere('orden', (int) $periodo)
            : $periodos->first(fn ($p) => $this->asistencia->normalizar($p->nombre) === $this->asistencia->normalizar((string) $periodo));

        if (! $encontrado) {
            throw ValidationException::withMessages([
                'periodo' => 'Período no encontrado. Opciones: ' . $periodos->map(fn ($p) => "{$p->orden} = {$p->nombre}")->join('; ') . '.',
            ]);
        }

        return $encontrado;
    }

    // -------------------------------------------------------------------------
    // Consultas
    // -------------------------------------------------------------------------

    /**
     * Situación de un espacio a partir de sus notas por período:
     * promedio de los períodos regulares (escala numérica) y condición.
     *
     * @param Collection<Calificacion> $notas keyed por periodo_evaluacion_id
     * @param Collection<PeriodoEvaluacion> $periodos del nivel
     */
    public function situacion(Collection $notas, Collection $periodos, InstitucionNivel $nivel): array
    {
        $escala      = $nivel->escalaCalificacion();
        $regulares   = $periodos->where('tipo', PeriodoEvaluacion::REGULAR);
        $intensif    = $periodos->where('tipo', PeriodoEvaluacion::INTENSIFICACION);
        $cargadas    = $regulares->filter(fn ($p) => $notas->has($p->id));
        $completo    = $regulares->isNotEmpty() && $cargadas->count() === $regulares->count();

        if ($escala === EscalaCalificacion::Informe) {
            return ['final' => null, 'condicion' => $completo ? 'completo' : 'en_curso', 'aprueba' => null];
        }

        if ($escala === EscalaCalificacion::Numerica) {
            $valores = $cargadas->map(fn ($p) => $notas[$p->id]->valor_numerico)->filter(fn ($v) => $v !== null);
            $final   = $valores->isNotEmpty() ? round($valores->avg(), 1) : null;
            $aprueba = $final !== null ? $nivel->aprueba(null, $final) : null;
        } else {
            $ultima  = $cargadas->last();
            $final   = $ultima ? $notas[$ultima->id]->valor : null;
            $aprueba = $final !== null ? $nivel->aprueba($final, null) : null;
        }

        if (! $completo) {
            return ['final' => $final, 'condicion' => 'en_curso', 'aprueba' => $aprueba];
        }

        if ($aprueba) {
            return ['final' => $final, 'condicion' => 'aprobada', 'aprueba' => true];
        }

        // Desaprobada: ¿la aprobó en intensificación?
        foreach ($intensif as $p) {
            $n = $notas->get($p->id);
            if ($n && $nivel->aprueba($n->valor, $n->valor_numerico)) {
                return ['final' => $n->valor_numerico ?? $n->valor, 'condicion' => 'aprobada_intensificacion', 'aprueba' => true];
            }
        }

        return ['final' => $final, 'condicion' => $intensif->isNotEmpty() ? 'intensificacion' : 'desaprobada', 'aprueba' => false];
    }

    public static function condicionLabel(string $condicion): array
    {
        return match ($condicion) {
            'aprobada'                 => ['Aprobada', 'success'],
            'aprobada_intensificacion' => ['Aprobada en intensificación', 'success'],
            'intensificacion'          => ['Debe intensificar', 'warning'],
            'desaprobada'              => ['Desaprobada', 'danger'],
            'completo'                 => ['Completo', 'success'],
            default                    => ['En curso', 'secondary'],
        };
    }

    /** Datos del boletín de un estudiante en un ciclo. */
    public function boletin(Estudiante $estudiante, CicloLectivo $ciclo): ?array
    {
        $inscripcion = Inscripcion::with(['curso.nivel', 'curso.plan', 'plan.nivel', 'nivel'])
            ->where('estudiante_id', $estudiante->id)
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->orderByRaw("case when estado = 'activa' then 0 else 1 end")
            ->first();

        if (! $inscripcion) {
            return null;
        }

        $nivel = $inscripcion->nivel;
        $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)
            ->where('institucion_nivel_id', $nivel->id)->orderBy('orden')->get();

        $notas = Calificacion::with('docente')
            ->where('estudiante_id', $estudiante->id)
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->get()
            ->groupBy('espacio_curricular_id');

        // Espacios: los del curso (plan + año) o, en superior, los que tienen notas.
        $espacios = $inscripcion->curso
            ? EspacioCurricular::where('plan_estudio_id', $inscripcion->curso->plan_estudio_id)
                ->where('grado', $inscripcion->curso->grado)->orderBy('orden')->orderBy('id')->get()
            : EspacioCurricular::whereIn('id', $notas->keys())->orderBy('grado')->orderBy('orden')->get();

        $filas = $espacios->map(fn ($e) => (object) [
            'espacio'   => $e,
            'notas'     => $notas->get($e->id, collect())->keyBy('periodo_evaluacion_id'),
            'situacion' => $this->situacion($notas->get($e->id, collect())->keyBy('periodo_evaluacion_id'), $periodos, $nivel),
        ]);

        return [
            'inscripcion' => $inscripcion,
            'nivel'       => $nivel,
            'periodos'    => $periodos,
            'filas'       => $filas,
            'asistencia'  => $this->asistencia->resumenEstudiante($estudiante, $ciclo),
        ];
    }
}
