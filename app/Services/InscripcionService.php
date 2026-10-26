<?php

namespace App\Services;

use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\PlanEstudio;
use Illuminate\Validation\ValidationException;

/**
 * Reglas de inscripción:
 * - No se inscribe en un ciclo cerrado.
 * - Niveles escolares: un solo curso activo por estudiante y ciclo; se respeta el cupo.
 * - Nivel superior: una inscripción activa por carrera y ciclo (puede cursar dos carreras).
 */
class InscripcionService
{
    /**
     * @param array $datos ciclo_lectivo_id, curso_id | plan_estudio_id, fecha, condicion, observaciones, forzar_cupo
     */
    public function inscribir(Estudiante $estudiante, array $datos): Inscripcion
    {
        $ciclo = CicloLectivo::findOrFail($datos['ciclo_lectivo_id']);
        $this->verificarCiclo($ciclo);

        if (! empty($datos['curso_id'])) {
            $curso = Curso::with(['nivel', 'plan'])->where('ciclo_lectivo_id', $ciclo->id)->findOrFail($datos['curso_id']);

            $otra = Inscripcion::with('curso.nivel')
                ->where('estudiante_id', $estudiante->id)
                ->where('ciclo_lectivo_id', $ciclo->id)
                ->where('estado', Inscripcion::ACTIVA)
                ->whereNotNull('curso_id')
                ->first();

            if ($otra) {
                throw ValidationException::withMessages([
                    'curso_id' => "Ya está inscripto en {$otra->curso->nombre} en el {$ciclo->nombre}. Para cambiarlo, editá esa inscripción.",
                ]);
            }

            $this->verificarCupo($curso, ! empty($datos['forzar_cupo']));

            return Inscripcion::create([
                'estudiante_id'        => $estudiante->id,
                'ciclo_lectivo_id'     => $ciclo->id,
                'institucion_nivel_id' => $curso->institucion_nivel_id,
                'curso_id'             => $curso->id,
                'plan_estudio_id'      => $curso->plan_estudio_id,
                'fecha'                => $datos['fecha'],
                'condicion'            => $datos['condicion'] ?? 'regular',
                'observaciones'        => $datos['observaciones'] ?? null,
            ]);
        }

        $plan = PlanEstudio::with('nivel')->findOrFail($datos['plan_estudio_id']);

        if (! $plan->esCarrera() || ! $plan->activo) {
            throw ValidationException::withMessages(['plan_estudio_id' => 'Elegí una carrera vigente.']);
        }

        $repetida = Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->where('plan_estudio_id', $plan->id)
            ->where('estado', Inscripcion::ACTIVA)
            ->exists();

        if ($repetida) {
            throw ValidationException::withMessages([
                'plan_estudio_id' => "Ya está inscripto en {$plan->nombre} en el {$ciclo->nombre}.",
            ]);
        }

        return Inscripcion::create([
            'estudiante_id'        => $estudiante->id,
            'ciclo_lectivo_id'     => $ciclo->id,
            'institucion_nivel_id' => $plan->institucion_nivel_id,
            'plan_estudio_id'      => $plan->id,
            'fecha'                => $datos['fecha'],
            'condicion'            => $datos['condicion'] ?? 'ingresante',
            'observaciones'        => $datos['observaciones'] ?? null,
        ]);
    }

    /** Cambio de curso dentro del mismo ciclo y nivel (ej. de 3° A a 3° B). */
    public function cambiarCurso(Inscripcion $inscripcion, int $cursoId, bool $forzarCupo = false): void
    {
        if ($inscripcion->curso_id === $cursoId) {
            return;
        }

        $curso = Curso::where('ciclo_lectivo_id', $inscripcion->ciclo_lectivo_id)
            ->where('institucion_nivel_id', $inscripcion->institucion_nivel_id)
            ->findOrFail($cursoId);

        $this->verificarCupo($curso, $forzarCupo);

        $inscripcion->update([
            'curso_id'        => $curso->id,
            'plan_estudio_id' => $curso->plan_estudio_id,
        ]);
    }

    /** Baja, pase o finalización. */
    public function cerrar(Inscripcion $inscripcion, string $estado, string $fecha, ?string $motivo): void
    {
        $inscripcion->update([
            'estado'      => $estado,
            'fecha_baja'  => $fecha,
            'motivo_baja' => $motivo,
        ]);
    }

    /** Vuelve a activar una inscripción dada de baja (con los mismos controles del alta). */
    public function reactivar(Inscripcion $inscripcion): void
    {
        $this->verificarCiclo($inscripcion->ciclo);

        $conflicto = Inscripcion::where('estudiante_id', $inscripcion->estudiante_id)
            ->where('ciclo_lectivo_id', $inscripcion->ciclo_lectivo_id)
            ->where('estado', Inscripcion::ACTIVA)
            ->whereKeyNot($inscripcion->id)
            ->when(
                $inscripcion->curso_id,
                fn ($q) => $q->whereNotNull('curso_id'),
                fn ($q) => $q->where('plan_estudio_id', $inscripcion->plan_estudio_id)
            )
            ->exists();

        if ($conflicto) {
            throw ValidationException::withMessages(['estado' => 'Ya tiene otra inscripción activa equivalente en este ciclo.']);
        }

        $inscripcion->update(['estado' => Inscripcion::ACTIVA, 'fecha_baja' => null, 'motivo_baja' => null]);
    }

    protected function verificarCiclo(CicloLectivo $ciclo): void
    {
        if ($ciclo->estaCerrado()) {
            throw ValidationException::withMessages(['ciclo_lectivo_id' => "El {$ciclo->nombre} está cerrado."]);
        }
    }

    protected function verificarCupo(Curso $curso, bool $forzar): void
    {
        if (! $forzar && ! $curso->tieneCupo()) {
            throw ValidationException::withMessages([
                'curso_id' => "{$curso->nombre} no tiene cupo ({$curso->cupo}). Marcá \"Inscribir aunque supere el cupo\" para hacerlo igual.",
            ]);
        }
    }
}
