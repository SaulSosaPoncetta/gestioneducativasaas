<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Services\TrayectoriaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Analítico (historial académico) del nivel superior, imprimible.
 */
class AnaliticoController extends Controller
{
    public function show(Request $request, Estudiante $estudiante, TrayectoriaService $trayectoria): View
    {
        $carreras = Inscripcion::with('plan.nivel')->where('estudiante_id', $estudiante->id)
            ->whereNull('curso_id')->whereNotNull('plan_estudio_id')->get()
            ->pluck('plan')->unique('id')->values();

        $plan = $carreras->firstWhere('id', (int) $request->plan) ?? $carreras->first();
        $estados = $plan ? $trayectoria->estados($estudiante, $plan) : collect();

        $aprobadas = $estados->where('estado', 'aprobada');
        $notasFinales = $aprobadas->pluck('nota')->filter(fn ($n) => $n !== null);
        $aplazos = $estados->flatMap(fn ($e) => $e->examenes->where('resultado', 'desaprobado')->pluck('nota'))->filter();

        return view('analitico.show', [
            'estudiante' => $estudiante,
            'carreras'   => $carreras,
            'plan'       => $plan,
            'porAnio'    => $estados->groupBy(fn ($e) => $e->espacio->grado),
            'totales'    => [
                'aprobadas'     => $aprobadas->count(),
                'total'         => $estados->count(),
                'avance'        => $estados->count() ? round($aprobadas->count() * 100 / $estados->count()) : 0,
                'promedio'      => $notasFinales->isNotEmpty() ? round($notasFinales->avg(), 2) : null,
                'promedio_aplazos' => $notasFinales->isNotEmpty() ? round($notasFinales->merge($aplazos)->avg(), 2) : null,
                'aplazos'       => $aplazos->count(),
            ],
        ]);
    }
}
