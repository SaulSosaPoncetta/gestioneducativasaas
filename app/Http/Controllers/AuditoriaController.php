<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use App\Support\Auditoria\Formato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Consulta del registro de auditoría de la institución (solo lectura). */
class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $registros = $this->consulta($request)->with('usuario')->latest('id')->paginate(50)->withQueryString();

        $usuarios = User::whereIn('id', Auditoria::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('apellido')->orderBy('nombre')->get();

        $modelos = collect(Auditoria::MODELOS)->mapWithKeys(fn ($v, $clase) => [class_basename($clase) => $v[0]])->sort();

        return view('auditoria.index', compact('registros', 'usuarios', 'modelos'));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $consulta = $this->consulta($request)->latest('id');

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel lea bien los acentos
            fputcsv($salida, ['Fecha', 'Usuario', 'Evento', 'Registro', 'Cambios', 'Origen', 'IP'], ';');

            $consulta->chunk(500, function ($lote) use ($salida) {
                foreach ($lote as $r) {
                    $cambios = collect($r->cambios())
                        ->map(fn ($c, $campo) => Formato::campo($campo) . ': ' . Formato::valor($c['antes']) . ' → ' . Formato::valor($c['despues']))
                        ->join(' | ');

                    fputcsv($salida, [
                        $r->created_at->format('d/m/Y H:i:s'), $r->usuario_nombre, $r->evento_label,
                        $r->descripcion, $cambios, $r->origen, $r->ip,
                    ], ';');
                }
            });

            fclose($salida);
        }, 'auditoria-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function consulta(Request $request): Builder
    {
        $clase = $request->filled('modelo')
            ? collect(array_keys(Auditoria::MODELOS))->first(fn ($c) => class_basename($c) === $request->modelo)
            : null;

        return Auditoria::query()
            ->when($request->filled('desde'), fn ($q) => $q->where('created_at', '>=', Carbon::parse($request->desde)->startOfDay()))
            ->when($request->filled('hasta'), fn ($q) => $q->where('created_at', '<=', Carbon::parse($request->hasta)->endOfDay()))
            ->when($request->filled('usuario'), fn ($q) => $q->where('user_id', (int) $request->usuario))
            ->when($request->filled('evento'), fn ($q) => $q->where('evento', $request->evento))
            ->when($request->filled('modelo'), fn ($q) => $q->where('modelo_tipo', $clase ?? '-'))
            ->when($request->filled('id'), fn ($q) => $q->where('modelo_id', (int) $request->id))
            ->when($request->filled('q'), fn ($q) => $q->where('descripcion', 'like', '%' . $request->q . '%'));
    }
}
