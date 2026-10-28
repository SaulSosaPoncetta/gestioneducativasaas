<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\AnaliticoController;
use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\CicloLectivo;
use App\Models\Estudiante;
use App\Models\Tutor;
use App\Services\AsistenciaService;
use App\Services\CalificacionService;
use App\Services\TrayectoriaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal de la familia: sus hijos, con boletín, inasistencias y avisos.
 */
class FamiliaController extends Controller
{
    public function hijos(Request $request, AsistenciaService $asistencia): View
    {
        $tutor = $this->tutor($request);
        $ciclo = CicloLectivo::actual();

        $hijos = $tutor->estudiantes()->where('estado', 'activo')->orderBy('apellido')->orderBy('nombre')->get()
            ->map(function (Estudiante $e) use ($ciclo, $asistencia) {
                $inscripcion = $e->inscripcionesActivas($ciclo?->id)->with(['curso.nivel', 'plan'])->first();
                $resumen = $asistencia->resumenEstudiante($e, $ciclo);

                // Avisos automáticos: inasistencias recientes y notas desaprobadas.
                $recientes = $resumen['registros']->where('estado', '!=', 'presente')
                    ->filter(fn ($a) => $a->fecha->gte(today()->subDays(7)));
                $desaprobadas = $ciclo
                    ? Calificacion::with(['espacio', 'periodo.nivel'])->where('estudiante_id', $e->id)->where('ciclo_lectivo_id', $ciclo->id)->get()
                        ->filter(fn ($c) => $c->periodo->nivel->aprueba($c->valor, $c->valor_numerico) === false)
                    : collect();

                return (object) compact('e', 'inscripcion', 'resumen', 'recientes', 'desaprobadas');
            });

        return view('portal.familia.hijos', compact('tutor', 'ciclo', 'hijos'));
    }

    public function hijo(Request $request, Estudiante $estudiante, CalificacionService $calificaciones, AsistenciaService $asistencia, TrayectoriaService $trayectoria): View
    {
        $tutor = $this->tutor($request);
        abort_unless($tutor->estudiantes()->whereKey($estudiante->id)->exists(), 403, 'Este estudiante no está a tu cargo.');

        $ciclo = CicloLectivo::actual();
        $carreras = $trayectoria->carrerasActivas($estudiante);
        $plan = $carreras->first();

        return view('portal.familia.hijo', [
            'estudiante' => $estudiante,
            'ciclo'      => $ciclo,
            'datos'      => $ciclo && ! $plan ? $calificaciones->boletin($estudiante, $ciclo) : null,
            'plan'       => $plan,
            'analitico'  => $plan ? AnaliticoController::datos($estudiante, $plan, $trayectoria) : null,
            'asistencia' => $asistencia->resumenEstudiante($estudiante, $ciclo),
        ]);
    }

    protected function tutor(Request $request): Tutor
    {
        $tutor = Tutor::where('user_id', $request->user()->id)->first();

        abort_unless($tutor, 403, 'Tu usuario no está vinculado como tutor. Consultá en secretaría.');

        return $tutor;
    }
}
