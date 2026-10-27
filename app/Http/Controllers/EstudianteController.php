<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstudianteRequest;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\InstitucionNivel;
use App\Services\AsistenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Legajos de estudiantes. El listado muestra dónde está inscripto
 * cada uno en el ciclo lectivo elegido (por defecto, el actual).
 */
class EstudianteController extends Controller
{
    public function index(Request $request): View
    {
        $ciclos = CicloLectivo::orderByDesc('anio')->get();
        $ciclo  = $ciclos->firstWhere('id', (int) $request->ciclo) ?? CicloLectivo::actual();
        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get();

        $cursos = $ciclo
            ? Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)
                ->orderBy('institucion_nivel_id')->orderBy('grado')->orderBy('division')->get()
            : collect();

        $cicloId = $ciclo?->id;

        $estudiantes = Estudiante::query()
            ->buscar($request->q)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('curso'), fn ($q) => $q->whereHas('inscripciones', fn ($i) => $i
                ->where('curso_id', $request->curso)->where('estado', 'activa')))
            ->when($request->filled('nivel'), fn ($q) => $q->whereHas('inscripciones', fn ($i) => $i
                ->where('institucion_nivel_id', $request->nivel)->where('ciclo_lectivo_id', $cicloId)->where('estado', 'activa')))
            ->when($request->sin_inscripcion, fn ($q) => $q->where('estado', 'activo')->whereDoesntHave('inscripciones', fn ($i) => $i
                ->where('ciclo_lectivo_id', $cicloId)->where('estado', 'activa')))
            ->with(['inscripciones' => fn ($q) => $q->where('ciclo_lectivo_id', $cicloId)->where('estado', 'activa')
                ->with(['curso.nivel', 'plan'])])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('estudiantes.index', compact('estudiantes', 'ciclos', 'ciclo', 'niveles', 'cursos'));
    }

    public function create(): View
    {
        return view('estudiantes.form', [
            'estudiante' => new Estudiante([
                'tipo_documento' => 'dni',
                'nacionalidad'   => 'Argentina',
                'estado'         => 'activo',
                'fecha_ingreso'  => today(),
            ]),
        ]);
    }

    public function store(EstudianteRequest $request): RedirectResponse
    {
        $estudiante = Estudiante::create($request->validated());

        if ($request->input('despues') === 'inscribir') {
            return redirect()->route('estudiantes.inscripciones.create', $estudiante)
                ->with('ok', "Se creó el legajo N° {$estudiante->legajo}. Ahora elegí dónde inscribirlo.");
        }

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('ok', "Se creó el legajo N° {$estudiante->legajo} de {$estudiante->nombre_completo}.");
    }

    public function show(Estudiante $estudiante): View
    {
        $estudiante->load([
            'tutores',
            'inscripciones' => fn ($q) => $q->with(['ciclo', 'nivel', 'curso.nivel', 'plan']),
            'usuario',
        ]);

        $servicio = app(AsistenciaService::class);
        $ciclo    = CicloLectivo::actual();

        return view('estudiantes.show', [
            'estudiante'  => $estudiante,
            'ciclo'       => $ciclo,
            'asistencia'  => $servicio->resumenEstudiante($estudiante, $ciclo),
            'porMateria'  => $servicio->porMateria($estudiante, $ciclo),
        ]);
    }

    public function edit(Estudiante $estudiante): View
    {
        return view('estudiantes.form', compact('estudiante'));
    }

    public function update(EstudianteRequest $request, Estudiante $estudiante): RedirectResponse
    {
        $estudiante->update($request->validated());

        return redirect()->route('estudiantes.show', $estudiante)->with('ok', 'Se guardaron los datos del estudiante.');
    }
}
