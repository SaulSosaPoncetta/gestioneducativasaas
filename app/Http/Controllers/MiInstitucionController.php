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
}
