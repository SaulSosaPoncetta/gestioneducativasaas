<?php

namespace App\Http\Controllers;

use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Services\InscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Inscripción de un estudiante en un ciclo lectivo: a un curso (niveles
 * escolares) o a una carrera (nivel superior). Cambio de curso, baja,
 * pase, finalización y reactivación.
 */
class InscripcionController extends Controller
{
    public function __construct(protected InscripcionService $servicio)
    {
    }

    public function create(Request $request, Estudiante $estudiante): View|RedirectResponse
    {
        $ciclos = CicloLectivo::where('estado', '!=', CicloLectivo::CERRADO)->orderByDesc('anio')->get();

        if ($ciclos->isEmpty()) {
            return redirect()->route('estudiantes.show', $estudiante)
                ->with('error', 'No hay ciclos lectivos abiertos. Creá o abrí uno desde Ciclos lectivos.');
        }

        $ciclo = $ciclos->firstWhere('id', (int) $request->ciclo)
            ?? $ciclos->firstWhere('estado', CicloLectivo::EN_CURSO)
            ?? $ciclos->first();

        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get();

        $cursos = Curso::with(['nivel', 'plan'])
            ->withCount('inscripcionesActivas')
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->orderBy('grado')->orderBy('division')
            ->get()
            ->groupBy('institucion_nivel_id');

        $carreras = PlanEstudio::with('nivel')
            ->where('tipo', 'carrera')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('inscripciones.create', compact('estudiante', 'ciclos', 'ciclo', 'niveles', 'cursos', 'carreras'));
    }

    public function store(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $datos = $request->validate([
            'ciclo_lectivo_id' => ['required', 'integer', Rule::exists('ciclos_lectivos', 'id')->where('institucion_id', $estudiante->institucion_id)],
            'tipo'             => ['required', Rule::in(['curso', 'carrera'])],
            'curso_id'         => ['exclude_unless:tipo,curso', 'required', 'integer', Rule::exists('cursos', 'id')->where('institucion_id', $estudiante->institucion_id)],
            'plan_estudio_id'  => ['exclude_unless:tipo,carrera', 'required', 'integer', Rule::exists('planes_estudio', 'id')->where('institucion_id', $estudiante->institucion_id)],
            'fecha'            => ['required', 'date'],
            'condicion'        => ['required', Rule::in(array_keys(Inscripcion::CONDICIONES_ESCOLAR + Inscripcion::CONDICIONES_SUPERIOR))],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
            'forzar_cupo'      => ['nullable', 'boolean'],
        ], [
            'curso_id.required'        => 'Elegí el curso.',
            'plan_estudio_id.required' => 'Elegí la carrera.',
        ], [
            'ciclo_lectivo_id' => 'ciclo lectivo',
            'condicion'        => 'condición',
        ]);

        $inscripcion = $this->servicio->inscribir($estudiante, $datos);
        $inscripcion->load(['curso.nivel', 'plan', 'ciclo']);

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('ok', "Quedó inscripto en {$inscripcion->destino} ({$inscripcion->ciclo->nombre}).");
    }

    public function edit(Inscripcion $inscripcion): View
    {
        $inscripcion->load(['estudiante', 'ciclo', 'nivel', 'curso', 'plan']);

        $cursos = $inscripcion->curso_id
            ? Curso::with(['nivel', 'plan'])->withCount('inscripcionesActivas')
                ->where('ciclo_lectivo_id', $inscripcion->ciclo_lectivo_id)
                ->where('institucion_nivel_id', $inscripcion->institucion_nivel_id)
                ->orderBy('grado')->orderBy('division')->get()
            : collect();

        return view('inscripciones.edit', compact('inscripcion', 'cursos'));
    }

    /** Cambio de curso, condición y observaciones. */
    public function update(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $condiciones = $inscripcion->curso_id ? Inscripcion::CONDICIONES_ESCOLAR : Inscripcion::CONDICIONES_SUPERIOR;

        $datos = $request->validate([
            'curso_id'      => [$inscripcion->curso_id ? 'required' : 'exclude', 'integer'],
            'condicion'     => ['required', Rule::in(array_keys($condiciones))],
            'fecha'         => ['required', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'forzar_cupo'   => ['nullable', 'boolean'],
        ], [], ['condicion' => 'condición', 'curso_id' => 'curso']);

        if ($inscripcion->ciclo->estaCerrado()) {
            return back()->with('error', "El {$inscripcion->ciclo->nombre} está cerrado.");
        }

        if (isset($datos['curso_id'])) {
            $this->servicio->cambiarCurso($inscripcion, (int) $datos['curso_id'], $request->boolean('forzar_cupo'));
        }

        $inscripcion->update([
            'condicion'     => $datos['condicion'],
            'fecha'         => $datos['fecha'],
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        return redirect()->route('estudiantes.show', $inscripcion->estudiante_id)->with('ok', 'Se guardó la inscripción.');
    }

    /** Baja, pase, finalización o reactivación. */
    public function estado(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $request->validate([
            'estado'      => ['required', Rule::in(array_keys(Inscripcion::ESTADOS))],
            'fecha_baja'  => ['exclude_if:estado,activa', 'required', 'date'],
            'motivo_baja' => ['nullable', 'string', 'max:200'],
        ], [], ['fecha_baja' => 'fecha', 'motivo_baja' => 'motivo']);

        if ($request->estado === Inscripcion::ACTIVA) {
            $this->servicio->reactivar($inscripcion);
            $texto = 'Se reactivó la inscripción.';
        } else {
            $this->servicio->cerrar($inscripcion, $request->estado, $request->fecha_baja, $request->motivo_baja);
            $texto = 'La inscripción quedó como "' . Inscripcion::ESTADOS[$request->estado] . '".';
        }

        return redirect()->route('estudiantes.show', $inscripcion->estudiante_id)->with('ok', $texto);
    }

    /** Eliminar una inscripción cargada por error. */
    public function destroy(Inscripcion $inscripcion): RedirectResponse
    {
        $estudiante = $inscripcion->estudiante_id;
        $inscripcion->delete();

        return redirect()->route('estudiantes.show', $estudiante)->with('ok', 'Se eliminó la inscripción.');
    }
}
