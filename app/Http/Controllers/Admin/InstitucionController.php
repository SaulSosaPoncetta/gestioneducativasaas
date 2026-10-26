<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitucionRequest;
use App\Models\Institucion;
use App\Services\InstitucionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ABM de instituciones (solo superadmin MiGestión).
 */
class InstitucionController extends Controller
{
    public function __construct(protected InstitucionService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $instituciones = Institucion::query()
            ->with('niveles')
            ->withCount('usuarios')
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)
                    ->orWhere('nombre_corto', 'like', $texto)
                    ->orWhere('cue', 'like', $texto)
                    ->orWhere('localidad', 'like', $texto));
            })
            ->when($request->estado === 'activas', fn ($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivas', fn ($q) => $q->where('activo', false))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('instituciones.index', compact('instituciones'));
    }

    public function create(): View
    {
        return view('instituciones.form', [
            'institucion' => new Institucion(['provincia' => 'Buenos Aires', 'tipo_gestion' => 'privada', 'activo' => true]),
        ]);
    }

    public function store(InstitucionRequest $request): RedirectResponse
    {
        $institucion = $this->servicio->crear(
            $request->datosInstitucion(),
            $request->input('niveles', []),
            $request->input('admin'),
            $request->file('logo'),
        );

        return redirect()->route('instituciones.index')
            ->with('ok', "Se creó la institución {$institucion->nombre}.");
    }

    public function edit(Institucion $institucion): View
    {
        $institucion->load('niveles');

        return view('instituciones.form', compact('institucion'));
    }

    public function update(InstitucionRequest $request, Institucion $institucion): RedirectResponse
    {
        $this->servicio->actualizar(
            $institucion,
            $request->datosInstitucion(),
            $request->input('niveles', []),
            $request->file('logo'),
            $request->boolean('quitar_logo'),
        );

        return redirect()->route('instituciones.index')
            ->with('ok', "Se guardaron los cambios de {$institucion->nombre}.");
    }

    public function toggleEstado(Institucion $institucion): RedirectResponse
    {
        $institucion->update(['activo' => ! $institucion->activo]);

        return back()->with('ok', $institucion->activo
            ? "{$institucion->nombre} quedó habilitada."
            : "{$institucion->nombre} quedó deshabilitada. Sus usuarios ya no pueden ingresar.");
    }
}
