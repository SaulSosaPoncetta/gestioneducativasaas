<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Http\Requests\ComunicadoRequest;
use App\Models\CicloLectivo;
use App\Models\Comunicado;
use App\Models\Curso;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use App\Services\ComunicadoService;
use App\Support\InstitucionActual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComunicadoController extends Controller
{
    public function __construct(private ComunicadoService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $comunicados = Comunicado::with('autor')
            ->withCount([
                'destinatarios',
                'destinatarios as leidos_count'      => fn ($q) => $q->whereNotNull('leido_at'),
                'destinatarios as confirmados_count' => fn ($q) => $q->whereNotNull('confirmado_at'),
            ])
            ->when($this->esPreceptor($request), fn ($q) => $q->where('autor_id', $request->user()->id))
            ->when($request->estado === 'borradores', fn ($q) => $q->whereNull('publicado_at'))
            ->when($request->estado === 'publicados', fn ($q) => $q->whereNotNull('publicado_at'))
            ->when($request->filled('q'), fn ($q) => $q->where('titulo', 'like', '%' . $request->q . '%'))
            ->orderByRaw('publicado_at is not null')
            ->latest('publicado_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('comunicados.index', compact('comunicados'));
    }

    public function create(Request $request): View
    {
        $comunicado = new Comunicado([
            'alcance'      => $this->esPreceptor($request) ? 'cursos' : 'institucion',
            'para'         => ['familias'],
            'enviar_email' => true,
        ]);

        if ($request->filled('curso')) {
            $comunicado->alcance = 'cursos';
            $comunicado->alcance_ids = [(int) $request->curso];
        }

        return view('comunicados.form', $this->datosFormulario($request, $comunicado));
    }

    public function store(ComunicadoRequest $request): RedirectResponse
    {
        $datos = $this->validarDestino($request);

        $comunicado = Comunicado::create($datos + ['autor_id' => $request->user()->id]);
        $this->guardarAdjunto($request, $comunicado);

        return redirect()->route('comunicados.show', $comunicado)
            ->with('ok', 'Borrador guardado. Revisá los destinatarios y publicalo.');
    }

    public function show(Request $request, Comunicado $comunicado): View
    {
        $this->autorizar($request, $comunicado);

        $vista = ['comunicado' => $comunicado];

        if ($comunicado->estaPublicado()) {
            $filtro = $request->input('filtro');

            $vista['destinatarios'] = $comunicado->destinatarios()->with('usuario')
                ->when($filtro === 'sin_leer', fn ($q) => $q->whereNull('leido_at'))
                ->when($filtro === 'leidos', fn ($q) => $q->whereNotNull('leido_at'))
                ->when($filtro === 'sin_confirmar', fn ($q) => $q->whereNull('confirmado_at'))
                ->orderBy('tipo')->orderBy('referencia')
                ->paginate(50)->withQueryString();

            $vista['totales'] = [
                'total'       => $comunicado->destinatarios()->count(),
                'leidos'      => $comunicado->destinatarios()->whereNotNull('leido_at')->count(),
                'confirmados' => $comunicado->destinatarios()->whereNotNull('confirmado_at')->count(),
                'emails'      => $comunicado->destinatarios()->whereNotNull('email_enviado_at')->count(),
            ];
        } else {
            $vista['previa'] = $this->servicio->destinatarios($comunicado);
        }

        return view('comunicados.show', $vista);
    }

    public function edit(Request $request, Comunicado $comunicado): View|RedirectResponse
    {
        $this->autorizar($request, $comunicado);

        if ($comunicado->estaPublicado()) {
            return redirect()->route('comunicados.show', $comunicado)->with('error', 'Un comunicado publicado no se puede editar.');
        }

        return view('comunicados.form', $this->datosFormulario($request, $comunicado));
    }

    public function update(ComunicadoRequest $request, Comunicado $comunicado): RedirectResponse
    {
        $this->autorizar($request, $comunicado);

        if ($comunicado->estaPublicado()) {
            return redirect()->route('comunicados.show', $comunicado)->with('error', 'Un comunicado publicado no se puede editar.');
        }

        $comunicado->update($this->validarDestino($request));

        if ($request->boolean('quitar_adjunto') && $comunicado->adjunto_path) {
            Storage::disk('local')->delete($comunicado->adjunto_path);
            $comunicado->update(['adjunto_path' => null, 'adjunto_nombre' => null]);
        }

        $this->guardarAdjunto($request, $comunicado);

        return redirect()->route('comunicados.show', $comunicado)->with('ok', 'Borrador actualizado.');
    }

    public function publicar(Request $request, Comunicado $comunicado): RedirectResponse
    {
        $this->autorizar($request, $comunicado);

        if ($comunicado->estaPublicado()) {
            return back()->with('error', 'El comunicado ya estaba publicado.');
        }

        $cantidad = $this->servicio->publicar($comunicado);

        if ($cantidad === 0) {
            return back()->with('error', 'No hay destinatarios con acceso al portal para ese destino. Generá los accesos o cambiá el destino.');
        }

        $mensaje = 'Comunicado publicado para ' . $cantidad . ($cantidad === 1 ? ' persona' : ' personas') . '.';

        if ($comunicado->enviar_email) {
            $mensaje .= ' Los emails se están enviando.';
        }

        return redirect()->route('comunicados.show', $comunicado)->with('ok', $mensaje);
    }

    public function destroy(Request $request, Comunicado $comunicado): RedirectResponse
    {
        $this->autorizar($request, $comunicado);

        if ($comunicado->estaPublicado()) {
            return back()->with('error', 'Un comunicado publicado no se puede eliminar: queda como registro.');
        }

        if ($comunicado->adjunto_path) {
            Storage::disk('local')->delete($comunicado->adjunto_path);
        }

        $comunicado->delete();

        return redirect()->route('comunicados.index')->with('ok', 'Borrador eliminado.');
    }

    public function adjunto(Request $request, Comunicado $comunicado): StreamedResponse
    {
        $this->autorizar($request, $comunicado);

        abort_unless($comunicado->adjunto_path && Storage::disk('local')->exists($comunicado->adjunto_path), 404);

        return Storage::disk('local')->download($comunicado->adjunto_path, $comunicado->adjunto_nombre);
    }

    // -----------------------------------------------------------------

    private function esPreceptor(Request $request): bool
    {
        return $request->user()->rol === Rol::Preceptor;
    }

    /** El preceptor solo ve y maneja sus propios comunicados. */
    private function autorizar(Request $request, Comunicado $comunicado): void
    {
        abort_if($this->esPreceptor($request) && $comunicado->autor_id !== $request->user()->id, 403);
    }

    /** Cursos que puede elegir: todos, o los propios / sin preceptor si es preceptor. */
    private function cursosDisponibles(Request $request)
    {
        $ciclo = CicloLectivo::actual();

        if (! $ciclo) {
            return collect();
        }

        return Curso::with('nivel')
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->when($this->esPreceptor($request), fn ($q) => $q->where(fn ($w) => $w
                ->where('preceptor_id', $request->user()->id)->orWhereNull('preceptor_id')))
            ->get()
            ->sortBy(fn ($c) => sprintf('%03d-%03d-%s', $c->nivel->orden, $c->grado, $c->division));
    }

    private function datosFormulario(Request $request, Comunicado $comunicado): array
    {
        $preceptor = $this->esPreceptor($request);

        return [
            'comunicado' => $comunicado,
            'preceptor'  => $preceptor,
            'alcances'   => $preceptor ? ['cursos' => Comunicado::ALCANCES['cursos']] : Comunicado::ALCANCES,
            'niveles'    => $preceptor ? collect() : InstitucionNivel::where('activo', true)->orderBy('orden')->get(),
            'cursos'     => $this->cursosDisponibles($request)->groupBy(fn ($c) => $c->nivel->nombre),
            'carreras'   => $preceptor ? collect() : PlanEstudio::with('nivel')->where('activo', true)
                ->whereHas('nivel', fn ($q) => $q->whereIn('nivel', ['terciario', 'universitario']))
                ->orderBy('nombre')->get(),
            'ciclo'      => CicloLectivo::actual(),
        ];
    }

    /** Deja solo ids de la institución (y permitidos para el usuario). */
    private function validarDestino(ComunicadoRequest $request): array
    {
        $datos = $request->safe()->except(['adjunto', 'quitar_adjunto']);
        $ids   = $datos['alcance_ids'] ?? [];

        if ($this->esPreceptor($request) && $datos['alcance'] !== 'cursos') {
            abort(403);
        }

        $validos = match ($datos['alcance']) {
            'niveles'  => InstitucionNivel::whereIn('id', $ids)->pluck('id'),
            'cursos'   => $this->cursosDisponibles($request)->whereIn('id', $ids)->pluck('id'),
            'carreras' => PlanEstudio::whereIn('id', $ids)->pluck('id'),
            default    => collect(),
        };

        if ($datos['alcance'] !== 'institucion' && $validos->isEmpty()) {
            back()->withInput()->withErrors(['alcance_ids' => 'Elegí al menos un destino válido.'])->throwResponse();
        }

        $datos['alcance_ids'] = $validos->values()->all();
        $datos['para'] = array_values(array_intersect(array_keys(Comunicado::PARA), $datos['para']));

        return $datos;
    }

    private function guardarAdjunto(ComunicadoRequest $request, Comunicado $comunicado): void
    {
        if (! $request->hasFile('adjunto')) {
            return;
        }

        if ($comunicado->adjunto_path) {
            Storage::disk('local')->delete($comunicado->adjunto_path);
        }

        $archivo = $request->file('adjunto');
        $ruta = $archivo->store('comunicados/' . InstitucionActual::id(), 'local');

        $comunicado->update([
            'adjunto_path'   => $ruta,
            'adjunto_nombre' => mb_substr($archivo->getClientOriginalName(), 0, 150),
        ]);
    }
}
