<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\MesaExamen;
use App\Models\MesaInscripcion;
use App\Models\PlanEstudio;
use App\Services\TrayectoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mesas de examen final: llamados, inscripción (con control de condición y
 * correlativas), acta de notas, cierre e impresión.
 */
class MesaExamenController extends Controller
{
    public function __construct(protected TrayectoriaService $trayectoria)
    {
    }

    public function index(Request $request): View
    {
        $planes = PlanEstudio::where('tipo', 'carrera')->orderBy('nombre')->get();
        $estado = $request->input('estado', 'proximas');

        $mesas = MesaExamen::with(['espacio.plan', 'presidente'])
            ->withCount('inscripciones')
            ->when($request->filled('plan'), fn ($q) => $q->whereHas('espacio', fn ($e) => $e->where('plan_estudio_id', $request->plan)))
            ->when($estado === 'proximas', fn ($q) => $q->where('estado', 'abierta'))
            ->when($estado === 'cerradas', fn ($q) => $q->where('estado', 'cerrada'))
            ->orderBy($estado === 'cerradas' ? 'fecha' : 'fecha', $estado === 'cerradas' ? 'desc' : 'asc')
            ->paginate(25)->withQueryString();

        return view('mesas.index', compact('mesas', 'planes', 'estado'));
    }

    public function create(Request $request): View
    {
        return view('mesas.form', $this->datosFormulario(new MesaExamen([
            'espacio_curricular_id' => $request->espacio,
            'fecha'                 => today()->addWeeks(3)->setTime(18, 0),
            'inscripcion_desde'     => today(),
            'inscripcion_hasta'     => today()->addWeeks(3)->subDays(2),
            'admite_libres'         => true,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $mesa = MesaExamen::create($this->validar($request) + ['estado' => 'abierta']);

        return redirect()->route('mesas.show', $mesa)->with('ok', 'Se creó la mesa. Ya se pueden inscribir estudiantes.');
    }

    public function show(MesaExamen $mesa): View
    {
        $mesa->load(['espacio.plan.nivel', 'presidente', 'inscripciones.estudiante']);
        $inscriptos = $mesa->inscripciones->sortBy(fn ($i) => $i->estudiante->apellido . ' ' . $i->estudiante->nombre)->values();

        return view('mesas.show', [
            'mesa'       => $mesa,
            'inscriptos' => $inscriptos,
            'candidatos' => $mesa->estaCerrada() ? collect() : $this->trayectoria->candidatos($mesa),
            'aprobacion' => $mesa->espacio->plan->nivel->notaAprobacion(),
        ]);
    }

    public function edit(MesaExamen $mesa): View|RedirectResponse
    {
        if ($mesa->estaCerrada()) {
            return redirect()->route('mesas.show', $mesa)->with('error', 'El acta está cerrada: la mesa no se puede modificar.');
        }

        return view('mesas.form', $this->datosFormulario($mesa));
    }

    public function update(Request $request, MesaExamen $mesa): RedirectResponse
    {
        if ($mesa->estaCerrada()) {
            return redirect()->route('mesas.show', $mesa)->with('error', 'El acta está cerrada.');
        }

        $mesa->update($this->validar($request, $mesa));

        return redirect()->route('mesas.show', $mesa)->with('ok', 'Se guardó la mesa.');
    }

    public function destroy(MesaExamen $mesa): RedirectResponse
    {
        if ($mesa->inscripciones()->exists()) {
            return back()->with('error', 'La mesa tiene inscriptos: quitalos antes de eliminarla.');
        }

        $mesa->delete();

        return redirect()->route('mesas.index')->with('ok', 'Se eliminó la mesa.');
    }

    /** Inscribir por id (candidatos) o por DNI. */
    public function inscribir(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $request->validate(['estudiante_id' => ['nullable', 'integer'], 'dni' => ['nullable', 'string', 'max:15'], 'forzar' => ['nullable', 'boolean']]);

        $estudiante = $request->filled('estudiante_id')
            ? Estudiante::find($request->estudiante_id)
            : Estudiante::where('documento', preg_replace('/\D/', '', (string) $request->dni))->first();

        if (! $estudiante) {
            return back()->with('error', 'No se encontró el estudiante.');
        }

        $insc = $this->trayectoria->inscribirMesa($mesa, $estudiante, $request->boolean('forzar'));

        return back()->with('ok', "Se inscribió a {$estudiante->nombre_completo} como {$insc->condicion}.");
    }

    public function quitar(MesaExamen $mesa, MesaInscripcion $inscripcion): RedirectResponse
    {
        abort_unless($inscripcion->mesa_examen_id === $mesa->id, 404);

        if ($mesa->estaCerrada()) {
            return back()->with('error', 'El acta está cerrada.');
        }

        $inscripcion->delete();

        return back()->with('ok', 'Se quitó la inscripción.');
    }

    /** Guarda las notas del acta. */
    public function acta(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $mesa->load(['inscripciones.estudiante', 'espacio.plan.nivel']);
        $this->trayectoria->guardarActa($mesa, $request->input('acta', []), $mesa->espacio->plan->nivel->notaAprobacion());

        return back()->with('ok', 'Se guardaron las notas del acta.');
    }

    /** Cierra el acta (queda de solo lectura). */
    public function cerrar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $request->validate(['libro' => ['nullable', 'string', 'max:20'], 'folio' => ['nullable', 'string', 'max:20']]);

        $pendientes = $mesa->inscripciones()->whereNull('resultado')->count();
        if ($pendientes) {
            return back()->with('error', "Hay {$pendientes} inscriptos sin nota ni ausente: completá el acta antes de cerrarla.");
        }

        $mesa->update(['estado' => 'cerrada', 'libro' => $request->libro, 'folio' => $request->folio, 'cerrada_at' => now()]);

        return back()->with('ok', 'Se cerró el acta.');
    }

    public function imprimir(MesaExamen $mesa): View
    {
        $mesa->load(['espacio.plan.nivel', 'presidente', 'inscripciones.estudiante']);

        return view('mesas.acta', [
            'mesa'       => $mesa,
            'inscriptos' => $mesa->inscripciones->sortBy(fn ($i) => $i->estudiante->apellido . ' ' . $i->estudiante->nombre)->values(),
        ]);
    }

    protected function datosFormulario(MesaExamen $mesa): array
    {
        return [
            'mesa'     => $mesa,
            'espacios' => EspacioCurricular::with('plan')->whereHas('plan', fn ($q) => $q->where('tipo', 'carrera'))
                ->orderBy('plan_estudio_id')->orderBy('grado')->orderBy('orden')->get()->groupBy('plan_estudio_id'),
            'docentes' => Docente::activos()->orderBy('apellido')->get(),
        ];
    }

    protected function validar(Request $request, ?MesaExamen $mesa = null): array
    {
        $datos = $request->validate([
            'espacio_curricular_id' => ['required', 'integer', Rule::exists('espacios_curriculares', 'id')->where('institucion_id', \App\Support\InstitucionActual::id())],
            'llamado'               => ['required', 'string', 'max:80'],
            'fecha'                 => ['required', 'date'],
            'aula'                  => ['nullable', 'string', 'max:40'],
            'presidente_id'         => ['nullable', 'integer', Rule::exists('docentes', 'id')->where('institucion_id', \App\Support\InstitucionActual::id())],
            'vocales'               => ['nullable', 'string', 'max:200'],
            'inscripcion_desde'     => ['required', 'date'],
            'inscripcion_hasta'     => ['required', 'date', 'after_or_equal:inscripcion_desde', 'before_or_equal:fecha'],
            'admite_libres'         => ['nullable', 'boolean'],
        ], [
            'inscripcion_hasta.before_or_equal' => 'La inscripción tiene que cerrar antes de la fecha del examen.',
        ], [
            'espacio_curricular_id' => 'unidad curricular', 'presidente_id' => 'presidente',
            'inscripcion_desde' => 'inicio de inscripción', 'inscripcion_hasta' => 'cierre de inscripción',
        ]);

        return $datos + ['admite_libres' => $request->boolean('admite_libres')];
    }
}
