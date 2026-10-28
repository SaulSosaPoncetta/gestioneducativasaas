<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\AsistenciaEspacio;
use App\Models\Calificacion;
use App\Models\CicloLectivo;
use App\Models\Cursada;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\MesaExamen;
use App\Models\MesaInscripcion;
use App\Models\PlanEstudio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Trayectoria académica en el nivel superior: qué aprobó, qué tiene
 * regular, qué está cursando; correlatividades para cursar y rendir;
 * inscripciones a cursadas y a mesas; cierre de cursada y actas.
 */
class TrayectoriaService
{
    /**
     * Estado de cada unidad curricular del plan para el estudiante.
     *
     * @return Collection<int, object> keyed por espacio_curricular_id:
     *   estado (aprobada|regular|cursando|libre|pendiente), nota, fecha, forma, vence, cursada
     */
    public function estados(Estudiante $estudiante, PlanEstudio $plan): Collection
    {
        $espacios = $plan->espacios()->get();

        $cursadas = Cursada::with('ciclo')->where('estudiante_id', $estudiante->id)
            ->whereIn('espacio_curricular_id', $espacios->pluck('id'))
            ->get()->sortByDesc(fn ($c) => $c->ciclo->anio)->groupBy('espacio_curricular_id');

        $finales = MesaInscripcion::with('mesa')->where('estudiante_id', $estudiante->id)
            ->whereHas('mesa', fn ($q) => $q->whereIn('espacio_curricular_id', $espacios->pluck('id')))
            ->get()->groupBy(fn ($m) => $m->mesa->espacio_curricular_id);

        return $espacios->mapWithKeys(function (EspacioCurricular $e) use ($cursadas, $finales) {
            $suyas   = $cursadas->get($e->id, collect());
            $examenes = $finales->get($e->id, collect());

            $aprobadoFinal = $examenes->where('resultado', 'aprobado')->sortByDesc(fn ($m) => $m->mesa->fecha)->first();
            $promocion     = $suyas->firstWhere('condicion', 'promocionado');
            $regular       = $suyas->first(fn ($c) => $c->regularVigente());
            $cursando      = $suyas->firstWhere('condicion', 'cursando');

            $estado = match (true) {
                (bool) $aprobadoFinal => (object) ['estado' => 'aprobada', 'nota' => $aprobadoFinal->nota, 'fecha' => $aprobadoFinal->mesa->fecha, 'forma' => 'Examen final'],
                (bool) $promocion     => (object) ['estado' => 'aprobada', 'nota' => $promocion->nota, 'fecha' => $promocion->fecha_condicion, 'forma' => 'Promoción'],
                (bool) $regular       => (object) ['estado' => 'regular', 'nota' => $regular->nota, 'fecha' => $regular->fecha_condicion, 'forma' => null, 'vence' => $regular->vence],
                (bool) $cursando      => (object) ['estado' => 'cursando', 'nota' => null, 'fecha' => $cursando->fecha_inscripcion, 'forma' => null],
                $suyas->isNotEmpty()  => (object) ['estado' => 'libre', 'nota' => null, 'fecha' => null, 'forma' => null],
                default               => (object) ['estado' => 'pendiente', 'nota' => null, 'fecha' => null, 'forma' => null],
            };

            $estado->espacio  = $e;
            $estado->vence   ??= null;
            $estado->aplazos  = $examenes->where('resultado', 'desaprobado')->count();
            $estado->examenes = $examenes;

            return [$e->id => $estado];
        });
    }

    public static function estadoLabel(string $estado): array
    {
        return match ($estado) {
            'aprobada' => ['Aprobada', 'success'],
            'regular'  => ['Regular', 'primary'],
            'cursando' => ['Cursando', 'info'],
            'libre'    => ['Libre', 'secondary'],
            default    => ['Pendiente', 'light'],
        };
    }

    /**
     * Correlativas que le faltan para una acción.
     *
     * @param string $accion cursar | rendir
     * @return string[] mensajes de lo que falta
     */
    public function faltantes(EspacioCurricular $espacio, Collection $estados, string $accion): array
    {
        $espacio->loadMissing('correlativas');
        $faltan = [];

        foreach ($espacio->correlativas as $req) {
            $tipo   = $req->pivot->tipo;
            $estado = $estados->get($req->id)?->estado ?? 'pendiente';

            if ($accion === 'cursar' && $tipo === 'regular_para_cursar' && ! in_array($estado, ['regular', 'aprobada'], true)) {
                $faltan[] = "{$req->nombre} (regular)";
            }
            if ($accion === 'cursar' && $tipo === 'aprobada_para_cursar' && $estado !== 'aprobada') {
                $faltan[] = "{$req->nombre} (aprobada)";
            }
            if ($accion === 'rendir' && $tipo === 'aprobada_para_rendir' && $estado !== 'aprobada') {
                $faltan[] = "{$req->nombre} (aprobada)";
            }
        }

        return $faltan;
    }

    /** Carrera activa del estudiante en el ciclo (para el plan de la unidad). */
    public function inscripcionCarrera(Estudiante $estudiante, PlanEstudio $plan, ?CicloLectivo $ciclo = null): ?Inscripcion
    {
        return Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('plan_estudio_id', $plan->id)
            ->where('estado', Inscripcion::ACTIVA)
            ->when($ciclo, fn ($q) => $q->where('ciclo_lectivo_id', $ciclo->id))
            ->first();
    }

    // -------------------------------------------------------------------------
    // Cursadas
    // -------------------------------------------------------------------------

    /**
     * Inscribe a cursadas. Devuelve [inscriptas, rechazadas con motivo].
     *
     * @param array<int, string> $espacios espacio_id => comisión
     */
    public function inscribirCursadas(Estudiante $estudiante, PlanEstudio $plan, CicloLectivo $ciclo, array $espacios, bool $forzar = false): array
    {
        if ($ciclo->estaCerrado()) {
            throw ValidationException::withMessages(['ciclo' => "El {$ciclo->nombre} está cerrado."]);
        }
        if (! $this->inscripcionCarrera($estudiante, $plan, $ciclo)) {
            throw ValidationException::withMessages(['ciclo' => "No está inscripto en {$plan->nombre} en el {$ciclo->nombre}."]);
        }

        $estados = $this->estados($estudiante, $plan);
        $ok = [];
        $rechazadas = [];

        foreach (EspacioCurricular::with('correlativas')->where('plan_estudio_id', $plan->id)->whereIn('id', array_keys($espacios))->get() as $e) {
            $estado = $estados->get($e->id)?->estado;

            if (in_array($estado, ['aprobada', 'cursando'], true)) {
                $rechazadas[$e->nombre] = $estado === 'aprobada' ? 'ya está aprobada' : 'ya la está cursando';
                continue;
            }

            $faltan = $this->faltantes($e, $estados, 'cursar');
            if ($faltan && ! $forzar) {
                $rechazadas[$e->nombre] = 'le falta ' . implode(', ', $faltan);
                continue;
            }

            Cursada::updateOrCreate(
                ['estudiante_id' => $estudiante->id, 'espacio_curricular_id' => $e->id, 'ciclo_lectivo_id' => $ciclo->id],
                ['comision' => trim($espacios[$e->id]) ?: 'Única', 'fecha_inscripcion' => today(), 'condicion' => 'cursando',
                 'nota' => null, 'fecha_condicion' => null, 'vence' => null]
            );
            $ok[] = $e->nombre;
        }

        return [$ok, $rechazadas];
    }

    /**
     * Datos para el cierre de una cursada (comisión): asistencia y promedio
     * de cada estudiante y condición sugerida.
     */
    public function datosCierre(EspacioCurricular $espacio, CicloLectivo $ciclo, string $comision): Collection
    {
        $cursadas = Cursada::with('estudiante')->where('espacio_curricular_id', $espacio->id)
            ->where('ciclo_lectivo_id', $ciclo->id)->where('comision', $comision)->get()
            ->sortBy(fn ($c) => mb_strtolower(\Illuminate\Support\Str::ascii($c->estudiante->apellido . ' ' . $c->estudiante->nombre)))->values();

        $asist = AsistenciaEspacio::where('espacio_curricular_id', $espacio->id)->where('ciclo_lectivo_id', $ciclo->id)
            ->whereIn('estudiante_id', $cursadas->pluck('estudiante_id'))->get()->groupBy('estudiante_id');

        $notas = Calificacion::where('espacio_curricular_id', $espacio->id)->where('ciclo_lectivo_id', $ciclo->id)
            ->whereIn('estudiante_id', $cursadas->pluck('estudiante_id'))->get()->groupBy('estudiante_id');

        return $cursadas->map(function (Cursada $c) use ($asist, $notas) {
            $registros = $asist->get($c->estudiante_id, collect());
            $clases = $registros->count();
            $faltas = $registros->sum(fn ($a) => Asistencia::VALORES[$a->estado] ?? 0);
            $porcentaje = $clases ? round(($clases - $faltas) * 100 / $clases, 1) : null;
            $promedio = $notas->get($c->estudiante_id, collect())->whereNotNull('valor_numerico')->avg('valor_numerico');
            $promedio = $promedio !== null ? round($promedio, 1) : null;

            $sugerida = match (true) {
                $porcentaje !== null && $porcentaje < Cursada::ASISTENCIA_MINIMA => 'libre',
                $promedio === null                                              => null,
                $promedio >= Cursada::NOTA_PROMOCION                            => 'promocionado',
                $promedio >= Cursada::NOTA_REGULARIDAD                          => 'regular',
                default                                                         => 'libre',
            };

            return (object) compact('c', 'clases', 'porcentaje', 'promedio', 'sugerida');
        });
    }

    /** Guarda las condiciones finales de la cursada. */
    public function cerrarCursadas(CicloLectivo $ciclo, array $datos): int
    {
        $n = 0;

        DB::transaction(function () use ($ciclo, $datos, &$n) {
            foreach ($datos as $id => $fila) {
                $cursada = Cursada::find($id);
                if (! $cursada || $cursada->ciclo_lectivo_id !== $ciclo->id) {
                    continue;
                }

                $condicion = $fila['condicion'];
                $cursada->update([
                    'condicion'       => $condicion,
                    'nota'            => $fila['nota'] !== null && $fila['nota'] !== '' ? (float) str_replace(',', '.', $fila['nota']) : null,
                    'fecha_condicion' => $condicion === 'cursando' ? null : today(),
                    'vence'           => $condicion === 'regular' ? $ciclo->fecha_fin->copy()->addYears(Cursada::VIGENCIA_ANIOS) : null,
                ]);
                $n++;
            }
        });

        return $n;
    }

    // -------------------------------------------------------------------------
    // Mesas de examen
    // -------------------------------------------------------------------------

    /** Inscribe a una mesa verificando condición y correlativas. */
    public function inscribirMesa(MesaExamen $mesa, Estudiante $estudiante, bool $forzar = false): MesaInscripcion
    {
        $mesa->loadMissing('espacio.plan', 'espacio.correlativas');

        if ($mesa->estaCerrada()) {
            throw ValidationException::withMessages(['estudiante' => 'El acta de la mesa ya está cerrada.']);
        }
        if (! $forzar && ! $mesa->inscripcionAbierta()) {
            throw ValidationException::withMessages(['estudiante' => 'La inscripción a esta mesa no está abierta (' .
                $mesa->inscripcion_desde->format('d/m') . ' al ' . $mesa->inscripcion_hasta->format('d/m') . ').']);
        }
        if ($mesa->inscripciones()->where('estudiante_id', $estudiante->id)->exists()) {
            throw ValidationException::withMessages(['estudiante' => "{$estudiante->nombre_completo} ya está inscripto en esta mesa."]);
        }
        if (! $this->inscripcionCarrera($estudiante, $mesa->espacio->plan)) {
            throw ValidationException::withMessages(['estudiante' => "{$estudiante->nombre_completo} no está inscripto en {$mesa->espacio->plan->nombre}."]);
        }

        $estados = $this->estados($estudiante, $mesa->espacio->plan);
        $estado  = $estados->get($mesa->espacio_curricular_id)?->estado ?? 'pendiente';

        if ($estado === 'aprobada') {
            throw ValidationException::withMessages(['estudiante' => "{$estudiante->nombre_completo} ya tiene aprobada {$mesa->espacio->nombre}."]);
        }

        $condicion = $estado === 'regular' ? 'regular' : 'libre';

        if ($condicion === 'libre' && ! $mesa->admite_libres && ! $forzar) {
            throw ValidationException::withMessages(['estudiante' => "{$estudiante->nombre_completo} no es regular y la mesa no admite libres."]);
        }

        $faltan = $this->faltantes($mesa->espacio, $estados, 'rendir');
        if ($faltan && ! $forzar) {
            throw ValidationException::withMessages(['estudiante' => "{$estudiante->nombre_completo} no puede rendir: le falta " . implode(', ', $faltan) . '.']);
        }

        return $mesa->inscripciones()->create(['estudiante_id' => $estudiante->id, 'condicion' => $condicion]);
    }

    /** Candidatos sugeridos: regulares vigentes de la unidad que todavía no se inscribieron. */
    public function candidatos(MesaExamen $mesa): Collection
    {
        $inscriptos = $mesa->inscripciones()->pluck('estudiante_id');

        return Cursada::with('estudiante')->where('espacio_curricular_id', $mesa->espacio_curricular_id)
            ->where('condicion', 'regular')
            ->where(fn ($q) => $q->whereNull('vence')->orWhereDate('vence', '>=', today()))
            ->whereNotIn('estudiante_id', $inscriptos)
            ->get()
            ->unique('estudiante_id')
            ->filter(fn ($c) => ! MesaInscripcion::where('estudiante_id', $c->estudiante_id)->where('resultado', 'aprobado')
                ->whereHas('mesa', fn ($q) => $q->where('espacio_curricular_id', $c->espacio_curricular_id))->exists())
            ->pluck('estudiante')
            ->sortBy('apellido')
            ->values();
    }

    /** Guarda las notas del acta (nota 1-10 o ausente). */
    public function guardarActa(MesaExamen $mesa, array $datos, float $aprobacion): void
    {
        if ($mesa->estaCerrada()) {
            throw ValidationException::withMessages(['acta' => 'El acta ya está cerrada.']);
        }

        foreach ($mesa->inscripciones as $insc) {
            $fila = $datos[$insc->id] ?? [];

            if (! empty($fila['ausente'])) {
                $insc->update(['nota' => null, 'resultado' => 'ausente']);
                continue;
            }

            $nota = isset($fila['nota']) && $fila['nota'] !== '' ? (float) str_replace(',', '.', $fila['nota']) : null;

            if ($nota !== null && ($nota < 1 || $nota > 10)) {
                throw ValidationException::withMessages(['acta' => "Nota inválida para {$insc->estudiante->nombre_completo}: tiene que ser de 1 a 10."]);
            }

            $insc->update([
                'nota'      => $nota,
                'resultado' => $nota === null ? null : ($nota >= $aprobacion ? 'aprobado' : 'desaprobado'),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Portal del estudiante
    // -------------------------------------------------------------------------

    /** Carreras en las que el estudiante tiene inscripción activa. */
    public function carrerasActivas(Estudiante $estudiante): Collection
    {
        return Inscripcion::with('plan')->where('estudiante_id', $estudiante->id)
            ->where('estado', Inscripcion::ACTIVA)->whereNull('curso_id')->whereNotNull('plan_estudio_id')
            ->get()->pluck('plan')->unique('id')->values();
    }

    /**
     * Mesas abiertas de sus carreras, con si se puede inscribir y por qué no.
     *
     * @return Collection<object> mesa, inscripcion (si ya está), puede, motivo, condicion
     */
    public function mesasParaEstudiante(Estudiante $estudiante): Collection
    {
        $planes = $this->carrerasActivas($estudiante);
        $estadosPorPlan = $planes->mapWithKeys(fn ($p) => [$p->id => $this->estados($estudiante, $p)]);

        return MesaExamen::with(['espacio.plan', 'espacio.correlativas'])
            ->where('estado', 'abierta')
            ->where('fecha', '>=', today())
            ->whereHas('espacio', fn ($q) => $q->whereIn('plan_estudio_id', $planes->pluck('id')))
            ->orderBy('fecha')
            ->get()
            ->map(function (MesaExamen $mesa) use ($estudiante, $estadosPorPlan) {
                $estados = $estadosPorPlan[$mesa->espacio->plan_estudio_id];
                $estado  = $estados->get($mesa->espacio_curricular_id)?->estado ?? 'pendiente';
                $insc    = $mesa->inscripciones()->where('estudiante_id', $estudiante->id)->first();
                $faltan  = $this->faltantes($mesa->espacio, $estados, 'rendir');
                $condicion = $estado === 'regular' ? 'regular' : 'libre';

                $motivo = match (true) {
                    $estado === 'aprobada'                       => 'Ya la tenés aprobada.',
                    ! $mesa->inscripcionAbierta()                => 'Inscripción del ' . $mesa->inscripcion_desde->format('d/m') . ' al ' . $mesa->inscripcion_hasta->format('d/m') . '.',
                    $condicion === 'libre' && ! $mesa->admite_libres => 'No sos regular y la mesa no admite libres.',
                    (bool) $faltan                               => 'Te falta: ' . implode(', ', $faltan) . '.',
                    default                                      => null,
                };

                return (object) [
                    'mesa'        => $mesa,
                    'inscripcion' => $insc,
                    'puede'       => ! $insc && $motivo === null,
                    'motivo'      => $motivo,
                    'condicion'   => $condicion,
                    'estado'      => $estado,
                ];
            })
            ->filter(fn ($m) => $m->inscripcion || $m->estado !== 'aprobada')
            ->values();
    }

    /** El estudiante se da de baja de una mesa (solo mientras la inscripción está abierta). */
    public function bajaMesa(MesaExamen $mesa, Estudiante $estudiante): void
    {
        if (! $mesa->inscripcionAbierta()) {
            throw ValidationException::withMessages(['mesa' => 'La inscripción ya cerró: no podés darte de baja. Consultá en secretaría.']);
        }

        $mesa->inscripciones()->where('estudiante_id', $estudiante->id)->whereNull('resultado')->delete();
    }
}
