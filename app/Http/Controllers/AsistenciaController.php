<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Models\Asistencia;
use App\Models\AsistenciaToma;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Services\AsistenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Asistencia diaria por curso (preceptoría).
 * El preceptor/a ve sus cursos y los que no tienen preceptor asignado;
 * la dirección y la secretaría ven todos.
 */
class AsistenciaController extends Controller
{
    public function __construct(protected AsistenciaService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $fecha = $this->fecha($request->fecha);
        $ciclo = CicloLectivo::actual();

        $cursos = $ciclo
            ? $this->cursosVisibles($request)
                ->with(['nivel', 'preceptor'])
                ->withCount('inscripcionesActivas')
                ->where('ciclo_lectivo_id', $ciclo->id)
                ->orderBy('institucion_nivel_id')->orderBy('grado')->orderBy('division')
                ->get()
            : collect();

        $tomas = AsistenciaToma::withCount([
                'asistencias as ausentes' => fn ($q) => $q->where('estado', 'ausente'),
                'asistencias as tardes'   => fn ($q) => $q->whereIn('estado', ['tarde', 'retiro']),
            ])
            ->whereDate('fecha', $fecha)
            ->whereIn('curso_id', $cursos->pluck('id'))
            ->get()
            ->keyBy('curso_id');

        return view('asistencia.index', [
            'fecha'   => $fecha,
            'ciclo'   => $ciclo,
            'cursos'  => $cursos->groupBy('institucion_nivel_id'),
            'tomas'   => $tomas,
            'todos'   => $request->boolean('todos'),
            'esPreceptor' => $request->user()->rol === Rol::Preceptor,
        ]);
    }

    /** Planilla del día para un curso. */
    public function tomar(Request $request, Curso $curso): View|RedirectResponse
    {
        $this->autorizarCurso($request, $curso);
        $fecha = $this->fecha($request->fecha);

        try {
            $this->servicio->validarFecha($curso->load('ciclo'), $fecha);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('asistencia.index', ['fecha' => $fecha->toDateString()])
                ->with('error', $curso->nombre . ': ' . collect($e->errors())->flatten()->first());
        }

        $estudiantes = $this->servicio->inscriptos($curso, $fecha);
        $toma = AsistenciaToma::with(['asistencias', 'usuario'])->where('curso_id', $curso->id)->whereDate('fecha', $fecha)->first();
        $actual = $toma?->asistencias->keyBy('estudiante_id') ?? collect();

        // Días con asistencia tomada en la semana, para navegar.
        $semana = AsistenciaToma::where('curso_id', $curso->id)
            ->whereBetween('fecha', [$fecha->copy()->startOfWeek()->toDateString(), $fecha->copy()->endOfWeek()->toDateString()])
            ->pluck('fecha')->map(fn ($f) => $f->toDateString())->all();

        return view('asistencia.tomar', compact('curso', 'fecha', 'estudiantes', 'toma', 'actual', 'semana'));
    }

    public function guardar(Request $request, Curso $curso): RedirectResponse
    {
        $this->autorizarCurso($request, $curso);

        $request->validate([
            'fecha'                => ['required', 'date'],
            'asistencia'           => ['nullable', 'array'],
            'asistencia.*.estado'  => ['required', Rule::in(array_keys(Asistencia::ESTADOS))],
            'asistencia.*.justificada' => ['nullable', 'boolean'],
            'asistencia.*.motivo'  => ['nullable', 'string', 'max:200'],
        ]);

        $fecha = Carbon::parse($request->fecha)->startOfDay();
        $this->servicio->guardarToma($curso->load('ciclo'), $fecha, $request->input('asistencia', []), $request->user()->id);

        return redirect()->route('asistencia.index', ['fecha' => $fecha->toDateString()])
            ->with('ok', "Se guardó la asistencia de {$curso->nombre} del " . $fecha->format('d/m/Y') . '.');
    }

    /** Planilla mensual del curso: días x estudiantes con totales. */
    public function mensual(Request $request, Curso $curso): View
    {
        $this->autorizarCurso($request, $curso);

        $mes = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : today()->startOfMonth();
        $hasta = $mes->copy()->endOfMonth();

        $tomas = AsistenciaToma::with('asistencias')->where('curso_id', $curso->id)
            ->whereBetween('fecha', [$mes->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')->get();

        $estudiantes = $this->servicio->inscriptos($curso, $hasta->isFuture() ? today() : $hasta);

        // Totales del ciclo completo para cada estudiante.
        $ciclo = Asistencia::whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->whereHas('toma', fn ($q) => $q->where('curso_id', $curso->id))
            ->get()->groupBy('estudiante_id');

        $totales = $estudiantes->mapWithKeys(fn ($e) => [$e->id => $this->servicio->totales($ciclo->get($e->id, collect()))]);

        return view('asistencia.mensual', compact('curso', 'mes', 'tomas', 'estudiantes', 'totales'));
    }

    /** Justificar (o quitar la justificación de) una inasistencia, desde el legajo. */
    public function justificar(Request $request, Asistencia $asistencia): RedirectResponse
    {
        $this->autorizarCurso($request, $asistencia->toma->curso);

        $datos = $request->validate([
            'justificada' => ['required', 'boolean'],
            'motivo'      => ['nullable', 'string', 'max:200'],
        ]);

        if ($asistencia->estado === 'presente') {
            return back()->with('error', 'Solo se justifican ausencias, llegadas tarde o retiros.');
        }

        $asistencia->update($datos);

        return back()->with('ok', $asistencia->justificada ? 'Inasistencia justificada.' : 'Se quitó la justificación.');
    }

    protected function cursosVisibles(Request $request)
    {
        $query = Curso::query();

        if ($request->user()->rol === Rol::Preceptor && ! $request->boolean('todos')) {
            $query->where(fn ($q) => $q->where('preceptor_id', $request->user()->id)->orWhereNull('preceptor_id'));
        }

        return $query;
    }

    /** Un preceptor/a solo opera en sus cursos o en los que no tienen preceptor. */
    protected function autorizarCurso(Request $request, Curso $curso): void
    {
        $usuario = $request->user();

        if ($usuario->rol === Rol::Preceptor && $curso->preceptor_id && $curso->preceptor_id !== $usuario->id) {
            abort(403, 'Este curso tiene otro preceptor/a asignado.');
        }
    }

    protected function fecha(?string $valor): Carbon
    {
        try {
            $fecha = $valor ? Carbon::parse($valor)->startOfDay() : today();
        } catch (\Throwable) {
            $fecha = today();
        }

        return $fecha->isFuture() ? today() : $fecha;
    }
}
