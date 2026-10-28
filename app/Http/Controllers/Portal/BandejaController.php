<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Comunicados recibidos: familias, estudiantes y docentes. */
class BandejaController extends Controller
{
    public function index(Request $request): View
    {
        $recibidos = ComunicadoDestinatario::with('comunicado.autor')
            ->where('user_id', $request->user()->id)
            ->whereHas('comunicado', fn ($q) => $q->whereNotNull('publicado_at'))
            ->when($request->filtro === 'sin_leer', fn ($q) => $q->whereNull('leido_at'))
            ->orderByRaw('leido_at is not null')
            ->orderByDesc(
                Comunicado::select('publicado_at')->whereColumn('comunicados.id', 'comunicado_destinatarios.comunicado_id')
            )
            ->paginate(20)
            ->withQueryString();

        $pendientes = ComunicadoDestinatario::where('user_id', $request->user()->id)
            ->whereNull('confirmado_at')
            ->whereHas('comunicado', fn ($q) => $q->where('requiere_confirmacion', true)->whereNotNull('publicado_at'))
            ->count();

        return view('portal.comunicados.index', compact('recibidos', 'pendientes'));
    }

    public function show(Request $request, Comunicado $comunicado): View
    {
        $recibido = $this->recibido($request, $comunicado);

        if (! $recibido->leido_at) {
            $recibido->update(['leido_at' => now()]);
        }

        return view('portal.comunicados.show', compact('comunicado', 'recibido'));
    }

    public function confirmar(Request $request, Comunicado $comunicado): RedirectResponse
    {
        $recibido = $this->recibido($request, $comunicado);

        if ($comunicado->requiere_confirmacion && ! $recibido->confirmado_at) {
            $recibido->update(['confirmado_at' => now(), 'leido_at' => $recibido->leido_at ?? now()]);
        }

        return redirect()->route('bandeja.show', $comunicado)->with('ok', 'Gracias, confirmaste la lectura.');
    }

    public function adjunto(Request $request, Comunicado $comunicado): StreamedResponse
    {
        $this->recibido($request, $comunicado);

        abort_unless($comunicado->adjunto_path && Storage::disk('local')->exists($comunicado->adjunto_path), 404);

        return Storage::disk('local')->download($comunicado->adjunto_path, $comunicado->adjunto_nombre);
    }

    /** Solo puede verlo quien figura como destinatario. */
    private function recibido(Request $request, Comunicado $comunicado): ComunicadoDestinatario
    {
        abort_unless($comunicado->estaPublicado(), 404);

        return ComunicadoDestinatario::where('comunicado_id', $comunicado->id)
            ->where('user_id', $request->user()->id)
            ->firstOr(fn () => abort(404));
    }
}
