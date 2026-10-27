<?php

namespace App\Http\Controllers;

use App\Models\CicloLectivo;
use App\Models\Cursada;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\PlanEstudio;
use App\Services\TrayectoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Cursadas del nivel superior: inscripción del estudiante a unidades
 * curriculares (con control de correlativas) y cierre de cada comisión.
 */
class CursadaController extends Controller
{
    public function __construct(protected TrayectoriaService $trayectoria)
    {
    }

    /** Resumen por carrera: unidades del año con inscriptos por comisión. */
    public function index(Request $request): View
    {
        $ciclos = CicloLectivo::orderByDesc('anio')->get();
        $ciclo  = $ciclos->firstWhere('id', (int) $request->ciclo) ?? CicloLectivo::actual();
        $planes = PlanEstudio::with('nivel')->where('tipo', 'carrera')->orderBy('nombre')->get();
        $plan   = $planes->firstWhere('id', (int) $request->plan) ?? $planes->first();

        $espacios = $plan ? $plan->espacios()->get() : collect();
        $resumen = $ciclo && $plan
            ? Cursada::where('ciclo_lectivo_id', $ciclo->id)->whereIn('espacio_curricular_id', $espacios->pluck('id'))
                ->selectRaw('espacio_curricular_id, comision, condicion, count(*) as total')
                ->groupBy('espacio_curricular_id', 'comision', 'condicion')->get()
                ->groupBy('espacio_curricular_id')
            : collect();

        return view('cursadas.index', compact('ciclos', 'ciclo', 'planes', 'plan', 'espacios', 'resumen'));
    }

    /** Lista de una comisión con datos para el cierre de cursada. */
    public function comision(Request $request, EspacioCurricular $espacio): View
    {
        $ciclo    = CicloLectivo::find($request->ciclo) ?? CicloLectivo::actual();
        $comision = $request->input('comision', 'Única');
        $filas    = $this->trayectoria->datosCierre($espacio->load('plan'), $ciclo, $comision);

        $comisiones = Cursada::where('espacio_curricular_id', $espacio->id)->where('ciclo_lectivo_id', $ciclo->id)
            ->distinct()->orderBy('comision')->pluck('comision');

        return view('cursadas.comision', compact('espacio', 'ciclo', 'comision', 'filas', 'comisiones'));
    }

    public function cerrar(Request $request, EspacioCurricular $espacio): RedirectResponse
    {
        $ciclo = CicloLectivo::findOrFail($request->ciclo);

        $request->validate([
            'cursadas'             => ['required', 'array'],
            'cursadas.*.condicion' => ['required', Rule::in(array_keys(Cursada::CONDICIONES))],
            'cursadas.*.nota'      => ['nullable', 'regex:/^(10|[1-9])([.,]\d{1,2})?$/'],
        ], ['cursadas.*.nota.regex' => 'Las notas tienen que ser de 1 a 10.']);

        if ($ciclo->estaCerrado()) {
            return back()->with('error', "El {$ciclo->nombre} está cerrado.");
        }

        $n = $this->trayectoria->cerrarCursadas($ciclo, $request->cursadas);

        return back()->with('ok', "Se guardó la condición de {$n} estudiantes en {$espacio->nombre}.");
    }

    /** Inscripción de un estudiante a cursadas. */
    public function inscribir(Request $request, Estudiante $estudiante): View|RedirectResponse
    {
        $ciclo = CicloLectivo::where('estado', '!=', CicloLectivo::CERRADO)->orderByDesc('anio')->first();

        $carreras = Inscripcion::with('plan')->where('estudiante_id', $estudiante->id)
            ->where('estado', Inscripcion::ACTIVA)->where('ciclo_lectivo_id', $ciclo?->id)
            ->whereNull('curso_id')->whereNotNull('plan_estudio_id')->get()->pluck('plan');

        if (! $ciclo || $carreras->isEmpty()) {
            return redirect()->route('estudiantes.show', $estudiante)
                ->with('error', 'Para inscribir a cursadas, el estudiante tiene que estar inscripto en una carrera en el ciclo abierto.');
        }

        $plan    = $carreras->firstWhere('id', (int) $request->plan) ?? $carreras->first();
        $estados = $this->trayectoria->estados($estudiante, $plan);
        $espacios = EspacioCurricular::with('correlativas')->where('plan_estudio_id', $plan->id)
            ->orderBy('grado')->orderBy('orden')->get()
            ->map(fn ($e) => (object) [
                'espacio' => $e,
                'estado'  => $estados->get($e->id),
                'faltan'  => $this->trayectoria->faltantes($e, $estados, 'cursar'),
            ]);

        return view('cursadas.inscribir', compact('estudiante', 'ciclo', 'carreras', 'plan', 'espacios'));
    }

    public function guardarInscripcion(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $request->validate([
            'plan'        => ['required', 'integer'],
            'ciclo'       => ['required', 'integer'],
            'espacios'    => ['required', 'array', 'min:1'],
            'comision'    => ['nullable', 'array'],
            'comision.*'  => ['nullable', 'string', 'max:30'],
            'forzar'      => ['nullable', 'boolean'],
        ], ['espacios.required' => 'Elegí al menos una unidad curricular.']);

        $plan  = PlanEstudio::findOrFail($request->plan);
        $ciclo = CicloLectivo::findOrFail($request->ciclo);
        $elegidos = collect($request->espacios)->mapWithKeys(fn ($id) => [(int) $id => $request->input("comision.$id", 'Única')])->all();

        [$ok, $rechazadas] = $this->trayectoria->inscribirCursadas($estudiante, $plan, $ciclo, $elegidos, $request->boolean('forzar'));

        $redirect = redirect()->route('cursadas.inscribir', ['estudiante' => $estudiante, 'plan' => $plan->id]);

        if ($ok) {
            $redirect->with('ok', 'Inscripto en: ' . implode(', ', $ok) . '.');
        }

        if ($rechazadas) {
            $redirect->with('error', 'No se pudo: ' . collect($rechazadas)->map(fn ($m, $n) => "{$n} ({$m})")->join('; ') . '.');
        }

        return $redirect;
    }

    /** Quita una cursada cargada por error. */
    public function destroy(Cursada $cursada): RedirectResponse
    {
        $cursada->delete();

        return back()->with('ok', 'Se quitó la cursada.');
    }
}
