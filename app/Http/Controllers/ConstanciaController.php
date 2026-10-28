<?php

namespace App\Http\Controllers;

use App\Models\Constancia;
use App\Models\Estudiante;
use App\Models\MesaInscripcion;
use App\Services\ConstanciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Emisión y registro de constancias (secretaría, dirección y preceptoría). */
class ConstanciaController extends Controller
{
    public function __construct(private ConstanciaService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $constancias = Constancia::with(['estudiante', 'emisor'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);
                // "15/2026" o "15": por número; si no, por estudiante o código.
                if (preg_match('~^(\d+)(?:/(\d{4}))?$~', $texto, $m)) {
                    return $q->where('numero', (int) $m[1])->when(isset($m[2]), fn ($w) => $w->where('anio', (int) $m[2]));
                }

                return $q->where(fn ($w) => $w
                    ->where('codigo', strtoupper(str_replace('-', '', $texto)))
                    ->orWhereHas('estudiante', fn ($e) => $e->buscar($texto)));
            })
            ->when($request->estado === 'anuladas', fn ($q) => $q->whereNotNull('anulada_at'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('constancias.index', compact('constancias'));
    }

    /** Sin estudiante: buscador. Con estudiante: opciones para emitir. */
    public function create(Request $request): View
    {
        $estudiante = $request->filled('estudiante') ? Estudiante::findOrFail($request->estudiante) : null;

        if (! $estudiante) {
            $resultados = $request->filled('q')
                ? Estudiante::buscar($request->q)->orderBy('apellido')->orderBy('nombre')->limit(20)->get()
                : collect();

            return view('constancias.buscar', compact('resultados'));
        }

        return view('constancias.create', [
            'estudiante'  => $estudiante,
            'inscripcion' => $this->servicio->inscripcionVigente($estudiante),
            'examenes'    => $this->servicio->examenesRendidos($estudiante),
            'anteriores'  => Constancia::where('estudiante_id', $estudiante->id)->latest('id')->limit(10)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'estudiante_id'       => ['required', 'integer'],
            'tipo'                => ['required', Rule::in(array_keys(Constancia::TIPOS))],
            'mesa_inscripcion_id' => ['nullable', 'required_if:tipo,examen', 'integer'],
            'presentar_ante'      => ['nullable', 'string', 'max:150'],
            'incluir_nota'        => ['nullable', 'boolean'],
        ], [
            'mesa_inscripcion_id.required_if' => 'Elegí el examen.',
        ]);

        $estudiante = Estudiante::findOrFail($datos['estudiante_id']);

        $constancia = $datos['tipo'] === 'examen'
            ? $this->servicio->emitirExamen(
                MesaInscripcion::where('estudiante_id', $estudiante->id)->findOrFail($datos['mesa_inscripcion_id']),
                $datos['presentar_ante'] ?? null,
                $request->boolean('incluir_nota'),
                $request->user()->id
            )
            : $this->servicio->emitirAlumnoRegular($estudiante, $datos['presentar_ante'] ?? null, $request->user()->id);

        return redirect()->route('constancias.imprimir', $constancia)
            ->with('ok', 'Constancia ' . $constancia->numero_label . ' emitida.');
    }

    public function imprimir(Request $request, Constancia $constancia): View
    {
        $volver = $request->query('volver');

        return view('constancias.imprimir', [
            'constancia'  => $constancia,
            'institucion' => $constancia->institucion,
            'volver'      => $volver && str_starts_with($volver, url('/'))
                ? $volver
                : route('constancias.create', ['estudiante' => $constancia->estudiante_id]),
        ]);
    }

    public function anular(Request $request, Constancia $constancia): RedirectResponse
    {
        abort_unless($request->user()->rol->esGestion(), 403);

        $datos = $request->validate(['motivo' => ['required', 'string', 'max:200']], [
            'motivo.required' => 'Indicá el motivo de la anulación.',
        ]);

        if ($constancia->estaAnulada()) {
            return back()->with('error', 'La constancia ya estaba anulada.');
        }

        $this->servicio->anular($constancia, $datos['motivo']);

        return back()->with('ok', 'Constancia ' . $constancia->numero_label . ' anulada. Si alguien la verifica, va a figurar como anulada.');
    }
}
