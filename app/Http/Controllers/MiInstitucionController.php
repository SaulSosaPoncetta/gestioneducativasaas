<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstitucionRequest;
use App\Services\InstitucionService;
use App\Support\InstitucionActual;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * El administrador edita los datos y niveles de su propia institución.
 */
class MiInstitucionController extends Controller
{
    public function edit(): View
    {
        $institucion = InstitucionActual::get()->load('niveles');

        return view('institucion.edit', compact('institucion'));
    }

    public function update(InstitucionRequest $request, InstitucionService $servicio): RedirectResponse
    {
        $servicio->actualizar(
            InstitucionActual::get(),
            $request->datosInstitucion(),
            $request->input('niveles', []),
            $request->file('logo'),
            $request->boolean('quitar_logo'),
        );

        return redirect()->route('institucion.edit')->with('ok', 'Se guardaron los datos de la institución.');
    }

    /** Genera (o regenera) el token de integración con GestiónAula. */
    public function generarToken(): RedirectResponse
    {
        $token = InstitucionActual::get()->generarApiToken();

        return redirect()->route('institucion.edit')
            ->with('ok', 'Se generó el token de integración. Copialo ahora: no se vuelve a mostrar.')
            ->with('token_generado', $token);
    }

    public function revocarToken(): RedirectResponse
    {
        InstitucionActual::get()->revocarApiToken();

        return redirect()->route('institucion.edit')->with('ok', 'Se revocó el token: GestiónAula ya no puede enviar datos.');
    }
}
