<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\User;
use App\Support\InstitucionActual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();

        // Superadmin sin institución elegida: listado de instituciones.
        if ($usuario->esSuperAdmin() && ! InstitucionActual::existe()) {
            $instituciones = Institucion::with('niveles')
                ->withCount('usuarios')
                ->orderBy('nombre')
                ->get();

            return view('dashboard', [
                'modo'          => 'superadmin',
                'instituciones' => $instituciones,
            ]);
        }

        $institucion = InstitucionActual::get();

        return view('dashboard', [
            'modo'        => 'institucion',
            'institucion' => $institucion,
            'niveles'     => $institucion->niveles->where('activo', true),
            'totales'     => [
                'usuarios' => User::deInstitucion()->where('activo', true)->count(),
            ],
        ]);
    }

    /** El superadmin entra a operar una institución. */
    public function seleccionarInstitucion(Request $request, Institucion $institucion): RedirectResponse
    {
        $request->session()->put('institucion_id', $institucion->id);

        return redirect()->route('dashboard')
            ->with('ok', "Estás trabajando en {$institucion->nombre}.");
    }

    /** El superadmin vuelve al listado general. */
    public function salirInstitucion(Request $request): RedirectResponse
    {
        $request->session()->forget('institucion_id');

        return redirect()->route('dashboard');
    }
}
