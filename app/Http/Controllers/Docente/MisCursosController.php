<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\AsistenciaEspacio;
use App\Models\Calificacion;
use App\Models\PeriodoEvaluacion;
use App\Models\CicloLectivo;
use App\Models\Docente;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Portal docente: sus cursos y espacios del ciclo, y la lista de estudiantes.
 */
class MisCursosController extends Controller
{
    public function index(Request $request): View
    {
        $docente = Docente::delUsuario($request->user());
        $ciclo   = CicloLectivo::actual();

        $grupos = collect();

        if ($docente && $ciclo) {
            $grupos = Asignacion::with(['espacio.plan', 'curso.nivel', 'curso.plan'])
                ->activas()
                ->where('docente_id', $docente->id)
                ->where('ciclo_lectivo_id', $ciclo->id)
                ->get()
                ->sortBy(fn ($a) => [$a->curso?->institucion_nivel_id ?? 99, $a->curso?->grado ?? $a->espacio->grado, $a->grupo])
                ->groupBy(fn ($a) => $a->curso_id ? "c{$a->curso_id}" : "p{$a->espacio->plan_estudio_id}-{$a->comision}");

            // Estudiantes por curso (activos).
            $porCurso = Inscripcion::where('ciclo_lectivo_id', $ciclo->id)
                ->where('estado', Inscripcion::ACTIVA)
                ->whereIn('curso_id', $grupos->flatten()->pluck('curso_id')->filter())
                ->selectRaw('curso_id, count(*) as total')
                ->groupBy('curso_id')
                ->pluck('total', 'curso_id');

            $grupos = $grupos->map(fn ($lista) => (object) [
                'asignaciones' => $lista,
                'primera'      => $lista->first(),
                'estudiantes'  => $lista->first()->curso_id ? ($porCurso[$lista->first()->curso_id] ?? 0) : null,
            ]);
        }

        return view('mis-cursos.index', compact('docente', 'ciclo', 'grupos'));
    }

    public function show(Request $request, Asignacion $asignacion): View
    {
        $docente = Docente::delUsuario($request->user());

        abort_unless($docente && $asignacion->docente_id === $docente->id, 403, 'Esta asignación no es tuya.');

        $asignacion->load(['espacio.plan', 'curso.nivel', 'curso.preceptor', 'ciclo']);

        $inscripciones = $asignacion->curso_id
            ? Inscripcion::with('estudiante')
                ->where('curso_id', $asignacion->curso_id)
                ->where('estado', Inscripcion::ACTIVA)
                ->get()
                ->sortBy(fn ($i) => mb_strtolower(Str::ascii($i->estudiante->apellido . ' ' . $i->estudiante->nombre)))
                ->values()
            : collect();

        // Otros espacios del mismo docente en ese curso.
        $otros = Asignacion::with('espacio')->activas()
            ->where('docente_id', $docente->id)
            ->where('ciclo_lectivo_id', $asignacion->ciclo_lectivo_id)
            ->when($asignacion->curso_id, fn ($q) => $q->where('curso_id', $asignacion->curso_id), fn ($q) => $q->whereNull('curso_id')->where('comision', $asignacion->comision))
            ->get();

        // Asistencia de la materia (enviada desde GestiónAula), por estudiante.
        $registros = AsistenciaEspacio::where('espacio_curricular_id', $asignacion->espacio_curricular_id)
            ->where('ciclo_lectivo_id', $asignacion->ciclo_lectivo_id)
            ->when($asignacion->curso_id, fn ($q) => $q->where('curso_id', $asignacion->curso_id))
            ->get();

        $clases = $registros->unique(fn ($r) => $r->fecha->toDateString() . '|' . $r->referencia)->count();

        $asistencia = $registros->groupBy('estudiante_id')->map(function ($lista) {
            $faltas = $lista->sum(fn ($a) => Asistencia::VALORES[$a->estado] ?? 0);

            return (object) [
                'clases'     => $lista->count(),
                'faltas'     => $faltas,
                'porcentaje' => round(($lista->count() - $faltas) * 100 / $lista->count(), 1),
            ];
        });

        // Notas de la materia por período (enviadas desde GestiónAula).
        $nivelId = $asignacion->curso?->institucion_nivel_id ?? $asignacion->espacio->plan->institucion_nivel_id;
        $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $asignacion->ciclo_lectivo_id)
            ->where('institucion_nivel_id', $nivelId)->orderBy('orden')->get();
        $notas = Calificacion::where('espacio_curricular_id', $asignacion->espacio_curricular_id)
            ->whereIn('periodo_evaluacion_id', $periodos->pluck('id'))
            ->get()
            ->keyBy(fn ($c) => "{$c->estudiante_id}-{$c->periodo_evaluacion_id}");
        $nivel = \App\Models\InstitucionNivel::find($nivelId);

        return view('mis-cursos.show', compact('asignacion', 'inscripciones', 'otros', 'asistencia', 'clases', 'periodos', 'notas', 'nivel'));
    }
}
