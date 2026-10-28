<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\CicloLectivo;
use App\Models\Cursada;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\MesaExamen;
use App\Models\PeriodoEvaluacion;
use App\Services\AsistenciaService;
use App\Services\CalificacionService;
use App\Services\TrayectoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal del estudiante: su cursada (boletín o cursadas del superior),
 * asistencia, inscripción a cursadas y a mesas, y analítico.
 */
class EstudiantePortalController extends Controller
{
    public function __construct(protected TrayectoriaService $trayectoria)
    {
    }

    public function inicio(Request $request, CalificacionService $calificaciones, AsistenciaService $asistencia): View
    {
        $estudiante = $this->estudiante($request);
        $ciclo = CicloLectivo::actual();
        $carreras = $this->trayectoria->carrerasActivas($estudiante);

        $datos = [
            'estudiante' => $estudiante,
            'ciclo'      => $ciclo,
            'carreras'   => $carreras,
            'boletin'    => null,
            'cursadas'   => collect(),
            'asistencia' => $asistencia->resumenEstudiante($estudiante, $ciclo),
            'porMateria' => $asistencia->porMateria($estudiante, $ciclo),
        ];

        if ($carreras->isEmpty()) {
            $datos['boletin'] = $ciclo ? $calificaciones->boletin($estudiante, $ciclo) : null;
        } else {
            // Superior: cursadas del ciclo con sus notas por período.
            $cursadas = Cursada::with(['espacio.plan'])->where('estudiante_id', $estudiante->id)
                ->where('ciclo_lectivo_id', $ciclo?->id)->get()->sortBy(fn ($c) => [$c->espacio->grado, $c->espacio->orden]);
            $notas = Calificacion::with('periodo')->where('estudiante_id', $estudiante->id)->where('ciclo_lectivo_id', $ciclo?->id)
                ->get()->groupBy('espacio_curricular_id');
            $datos['cursadas'] = $cursadas->map(fn ($c) => (object) [
                'cursada'    => $c,
                'notas'      => $notas->get($c->espacio_curricular_id, collect())->sortBy('periodo.orden'),
                'asistencia' => $datos['porMateria']->first(fn ($m) => $m->espacio->id === $c->espacio_curricular_id),
            ]);
        }

        return view('portal.estudiante.inicio', $datos);
    }

    public function analitico(Request $request): View
    {
        $estudiante = $this->estudiante($request);
        $carreras = $this->trayectoria->carrerasActivas($estudiante);
        $plan = $carreras->firstWhere('id', (int) $request->plan) ?? $carreras->first();

        return view('portal.estudiante.analitico', ['estudiante' => $estudiante, 'carreras' => $carreras, 'plan' => $plan]
            + ($plan ? \App\Http\Controllers\AnaliticoController::datos($estudiante, $plan, $this->trayectoria) : ['porAnio' => collect(), 'totales' => []]));
    }

    // ---------------- Inscripción a cursadas ----------------

    public function cursadas(Request $request): View
    {
        $estudiante = $this->estudiante($request);
        $ciclo = CicloLectivo::where('estado', '!=', CicloLectivo::CERRADO)->orderByDesc('anio')->first();
        $carreras = $this->trayectoria->carrerasActivas($estudiante);
        $plan = $carreras->firstWhere('id', (int) $request->plan) ?? $carreras->first();

        $espacios = collect();
        if ($plan) {
            $estados = $this->trayectoria->estados($estudiante, $plan);
            $espacios = EspacioCurricular::with('correlativas')->where('plan_estudio_id', $plan->id)
                ->orderBy('grado')->orderBy('orden')->get()
                ->map(fn ($e) => (object) [
                    'espacio' => $e,
                    'estado'  => $estados->get($e->id),
                    'faltan'  => $this->trayectoria->faltantes($e, $estados, 'cursar'),
                ]);
        }

        return view('portal.estudiante.cursadas', compact('estudiante', 'ciclo', 'carreras', 'plan', 'espacios'));
    }

    public function inscribirCursadas(Request $request): RedirectResponse
    {
        $estudiante = $this->estudiante($request);
        $request->validate(['plan' => ['required', 'integer'], 'espacios' => ['required', 'array', 'min:1']],
            ['espacios.required' => 'Elegí al menos una unidad curricular.']);

        $ciclo = CicloLectivo::where('estado', '!=', CicloLectivo::CERRADO)->orderByDesc('anio')->firstOrFail();
        $plan  = $this->trayectoria->carrerasActivas($estudiante)->firstWhere('id', (int) $request->plan);

        abort_unless($plan, 403);

        if (! $ciclo->inscripcionCursadasAbierta()) {
            return back()->with('error', 'La inscripción a cursadas no está abierta.');
        }

        $elegidos = collect($request->espacios)->mapWithKeys(fn ($id) => [(int) $id => 'Única'])->all();
        [$ok, $rechazadas] = $this->trayectoria->inscribirCursadas($estudiante, $plan, $ciclo, $elegidos);

        $r = back();
        if ($ok) {
            $r->with('ok', 'Te inscribiste en: ' . implode(', ', $ok) . '.');
        }
        if ($rechazadas) {
            $r->with('error', 'No se pudo: ' . collect($rechazadas)->map(fn ($m, $n) => "{$n} ({$m})")->join('; ') . '.');
        }

        return $r;
    }

    // ---------------- Mesas de examen ----------------

    public function finales(Request $request): View
    {
        $estudiante = $this->estudiante($request);

        $historial = \App\Models\MesaInscripcion::with('mesa.espacio')->where('estudiante_id', $estudiante->id)
            ->whereNotNull('resultado')->get()->sortByDesc(fn ($i) => $i->mesa->fecha);

        return view('portal.estudiante.finales', [
            'estudiante' => $estudiante,
            'mesas'      => $this->trayectoria->mesasParaEstudiante($estudiante),
            'historial'  => $historial,
        ]);
    }

    public function inscribirMesa(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $estudiante = $this->estudiante($request);
        $insc = $this->trayectoria->inscribirMesa($mesa, $estudiante);

        return back()->with('ok', "Te inscribiste a {$mesa->espacio->nombre} ({$mesa->fecha->format('d/m H:i')}) como {$insc->condicion}.");
    }

    public function bajaMesa(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->trayectoria->bajaMesa($mesa, $this->estudiante($request));

        return back()->with('ok', "Te diste de baja de la mesa de {$mesa->espacio->nombre}.");
    }

    protected function estudiante(Request $request): Estudiante
    {
        $estudiante = Estudiante::where('user_id', $request->user()->id)->first();

        abort_unless($estudiante, 403, 'Tu usuario no está vinculado a un legajo de estudiante. Consultá en secretaría.');

        return $estudiante;
    }
}
