<?php

namespace App\Http\Controllers;

use App\Models\Constancia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Verificación pública de constancias (sin iniciar sesión), por QR o código. */
class ConstanciaVerificacionController extends Controller
{
    public function form(Request $request): View|RedirectResponse
    {
        if ($request->filled('codigo')) {
            return redirect()->route('constancias.verificar', $this->normalizar($request->codigo));
        }

        return view('constancias.verificar', ['constancia' => null, 'codigo' => null]);
    }

    public function show(string $codigo): View
    {
        $codigo = $this->normalizar($codigo);

        $constancia = Constancia::sinInstitucion()->with('institucion')->where('codigo', $codigo)->first();

        return view('constancias.verificar', compact('constancia', 'codigo'));
    }

    private function normalizar(string $codigo): string
    {
        return mb_substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo)), 0, 16);
    }
}
