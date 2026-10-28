<?php

namespace App\Http\Controllers\Portal;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Constancia;
use App\Models\Estudiante;
use App\Models\MesaInscripcion;
use App\Models\Tutor;
use App\Services\ConstanciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Autogestión de constancias: el estudiante (o la familia, para sus hijos)
 * descarga su constancia de alumno regular o de examen, con código verificable.
 */
class ConstanciaPortalController extends Controller
{
    public function __construct(private ConstanciaService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $estudiantes = $this->estudiantes($request)->map(fn (Estudiante $e) => (object) [
            'e'           => $e,
            'inscripcion' => $this->servicio->inscripcionVigente($e),
            'examenes'    => $this->servicio->examenesRendidos($e),
        ]);

        $emitidas = Constancia::with('estudiante')
            ->whereIn('estudiante_id', $estudiantes->pluck('e.id'))
            ->whereNull('anulada_at')
            ->latest('id')
            ->limit(30)
            ->get();

        return view('portal.constancias.index', [
            'estudiantes' => $estudiantes,
            'emitidas'    => $emitidas,
            'esFamilia'   => $request->user()->rol === Rol::Tutor,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'estudiante_id'       => ['required', 'integer'],
            'tipo'                => ['required', Rule::in(array_keys(Constancia::TIPOS))],
            'mesa_inscripcion_id' => ['nullable', 'required_if:tipo,examen', 'integer'],
            'presentar_ante'      => ['nullable', 'string', 'max:150'],
        ], [
            'mesa_inscripcion_id.required_if' => 'Elegí el examen.',
        ]);

        $estudiante = $this->estudiantes($request)->firstWhere('id', (int) $datos['estudiante_id']);
        abort_unless($estudiante, 403);

        $presentarAnte = filled($datos['presentar_ante'] ?? null) ? trim($datos['presentar_ante']) : null;

        // Si ya pidió hoy la misma, se reutiliza (no se gastan números).
        $existente = Constancia::where('estudiante_id', $estudiante->id)
            ->where('tipo', $datos['tipo'])
            ->where('mesa_inscripcion_id', $datos['tipo'] === 'examen' ? $datos['mesa_inscripcion_id'] : null)
            ->where('presentar_ante', $presentarAnte)
            ->whereNull('anulada_at')
            ->whereDate('created_at', today())
            ->first();

        if ($existente) {
            return redirect()->route('portal.constancias.show', $existente);
        }

        $constancia = $datos['tipo'] === 'examen'
            ? $this->servicio->emitirExamen(
                MesaInscripcion::where('estudiante_id', $estudiante->id)->findOrFail($datos['mesa_inscripcion_id']),
                $presentarAnte, true, $request->user()->id, 'portal'
            )
            : $this->servicio->emitirAlumnoRegular($estudiante, $presentarAnte, $request->user()->id, 'portal');

        if ($request->user()->rol === Rol::Tutor) {
            $constancia->update(['datos' => $constancia->datos + ['solicitante' => 'familia']]);
        }

        return redirect()->route('portal.constancias.show', $constancia);
    }

    public function show(Request $request, Constancia $constancia): View
    {
        abort_unless($this->estudiantes($request)->contains('id', $constancia->estudiante_id), 403);

        return view('constancias.imprimir', [
            'constancia'  => $constancia,
            'institucion' => $constancia->institucion,
            'volver'      => route('portal.constancias.index'),
        ]);
    }

    /** El propio estudiante, o los hijos activos del tutor. */
    private function estudiantes(Request $request): Collection
    {
        $usuario = $request->user();

        if ($usuario->rol === Rol::Tutor) {
            $tutor = Tutor::where('user_id', $usuario->id)->first();
            abort_unless($tutor, 403, 'Tu usuario no está vinculado como tutor. Consultá en secretaría.');

            return $tutor->estudiantes()->where('estado', 'activo')->orderBy('nombre')->get();
        }

        $estudiante = Estudiante::where('user_id', $usuario->id)->first();
        abort_unless($estudiante, 403, 'Tu usuario no está vinculado a un legajo. Consultá en secretaría.');

        return collect([$estudiante]);
    }
}
