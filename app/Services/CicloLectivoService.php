<?php

namespace App\Services;

use App\Enums\EsquemaEvaluacion;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\InstitucionNivel;
use App\Models\PeriodoEvaluacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Alta de ciclos lectivos con sus períodos por nivel, copia de cursos
 * del ciclo anterior y cambios de estado.
 */
class CicloLectivoService
{
    /**
     * @param array $datos   anio, nombre, fecha_inicio, fecha_fin
     * @param array $niveles [institucion_nivel_id => ['esquema' => ..., 'intensificacion' => bool, 'copiar_cursos' => bool]]
     */
    public function crear(array $datos, array $niveles): CicloLectivo
    {
        return DB::transaction(function () use ($datos, $niveles) {
            $ciclo = CicloLectivo::create($datos + ['estado' => CicloLectivo::PLANIFICACION]);

            $anterior = CicloLectivo::where('anio', '<', $ciclo->anio)->orderByDesc('anio')->first();

            foreach ($niveles as $nivelId => $config) {
                $nivel = InstitucionNivel::findOrFail($nivelId);

                $this->generarPeriodos(
                    $ciclo,
                    $nivel,
                    EsquemaEvaluacion::from($config['esquema']),
                    (bool) ($config['intensificacion'] ?? false),
                );

                if (! empty($config['copiar_cursos']) && $anterior) {
                    $this->copiarCursos($anterior, $ciclo, $nivel);
                }
            }

            return $ciclo;
        });
    }

    /**
     * (Re)genera los períodos de un nivel dividiendo el ciclo en partes iguales.
     * Las fechas quedan como sugerencia: se ajustan después desde el ciclo.
     */
    public function generarPeriodos(
        CicloLectivo $ciclo,
        InstitucionNivel $nivel,
        EsquemaEvaluacion $esquema,
        bool $intensificacion = false
    ): void {
        PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)
            ->where('institucion_nivel_id', $nivel->id)
            ->delete();

        $nombres = $esquema->periodos();
        $inicio  = $ciclo->fecha_inicio->copy();
        $dias    = (int) $ciclo->fecha_inicio->diffInDays($ciclo->fecha_fin) + 1;
        $tramo   = intdiv($dias, count($nombres));

        foreach ($nombres as $i => $nombre) {
            $ultimo = $i === count($nombres) - 1;
            $fin    = $ultimo ? $ciclo->fecha_fin->copy() : $inicio->copy()->addDays($tramo - 1);

            $this->crearPeriodo($ciclo, $nivel, $nombre, PeriodoEvaluacion::REGULAR, $i + 1, $inicio, $fin);

            $inicio = $fin->copy()->addDay();
        }

        if ($intensificacion) {
            $orden = count($nombres);

            // Diciembre: desde el fin de clases hasta antes de las fiestas.
            $dicInicio = $ciclo->fecha_fin->copy()->addDay();
            $dicFin    = Carbon::create($ciclo->anio, 12, 30);
            if ($dicInicio->gte($dicFin)) {
                $dicInicio = Carbon::create($ciclo->anio, 12, 1);
                $dicFin    = Carbon::create($ciclo->anio, 12, 22);
            }

            $this->crearPeriodo($ciclo, $nivel, 'Intensificación diciembre', PeriodoEvaluacion::INTENSIFICACION,
                $orden + 1, $dicInicio, $dicFin);

            $this->crearPeriodo($ciclo, $nivel, 'Intensificación febrero', PeriodoEvaluacion::INTENSIFICACION,
                $orden + 2, Carbon::create($ciclo->anio + 1, 2, 10), Carbon::create($ciclo->anio + 1, 2, 28));
        }
    }

    /** Copia los cursos (grado, división, plan, turno, aula, cupo y preceptor) de un ciclo a otro. */
    public function copiarCursos(CicloLectivo $origen, CicloLectivo $destino, InstitucionNivel $nivel): int
    {
        $copiados = 0;

        Curso::where('ciclo_lectivo_id', $origen->id)
            ->where('institucion_nivel_id', $nivel->id)
            ->get()
            ->each(function (Curso $curso) use ($destino, &$copiados) {
                Curso::firstOrCreate(
                    [
                        'ciclo_lectivo_id'     => $destino->id,
                        'institucion_nivel_id' => $curso->institucion_nivel_id,
                        'grado'                => $curso->grado,
                        'division'             => $curso->division,
                    ],
                    $curso->only(['plan_estudio_id', 'turno', 'preceptor_id', 'aula', 'cupo'])
                );
                $copiados++;
            });

        return $copiados;
    }

    /** Solo puede haber un ciclo en curso: al iniciar uno, el anterior en curso se cierra. */
    public function cambiarEstado(CicloLectivo $ciclo, string $estado): void
    {
        DB::transaction(function () use ($ciclo, $estado) {
            if ($estado === CicloLectivo::EN_CURSO) {
                CicloLectivo::enCurso()
                    ->whereKeyNot($ciclo->id)
                    ->update(['estado' => CicloLectivo::CERRADO]);
            }

            $ciclo->update(['estado' => $estado]);
        });
    }

    /** Esquema con el que se generaron los períodos de un nivel (null si se editaron a mano). */
    public function esquemaDe($periodos): ?EsquemaEvaluacion
    {
        $nombres = $periodos->where('tipo', PeriodoEvaluacion::REGULAR)->pluck('nombre')->values()->all();

        foreach (EsquemaEvaluacion::cases() as $esquema) {
            if ($esquema->periodos() === $nombres) {
                return $esquema;
            }
        }

        return null;
    }

    protected function crearPeriodo(
        CicloLectivo $ciclo,
        InstitucionNivel $nivel,
        string $nombre,
        string $tipo,
        int $orden,
        Carbon $inicio,
        Carbon $fin
    ): void {
        PeriodoEvaluacion::create([
            'ciclo_lectivo_id'     => $ciclo->id,
            'institucion_nivel_id' => $nivel->id,
            'nombre'               => $nombre,
            'tipo'                 => $tipo,
            'orden'                => $orden,
            'fecha_inicio'         => $inicio->toDateString(),
            'fecha_fin'            => $fin->toDateString(),
        ]);
    }
}
