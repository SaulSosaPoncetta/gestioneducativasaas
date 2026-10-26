<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use App\Support\InstitucionActual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Usuarios (cuentas de acceso) de la institución actual.
 *
 * Los legajos de estudiantes, docentes y tutores serán módulos propios que
 * se vinculan a una cuenta de acá; este ABM maneja el acceso y el rol.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::deInstitucion()
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)
                    ->orWhere('apellido', 'like', $texto)
                    ->orWhere('email', 'like', $texto)
                    ->orWhere('dni', 'like', $texto));
            })
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->rol))
            ->when($request->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles'    => Rol::opciones(),
        ]);
    }

    public function create(): View
    {
        return view('usuarios.form', [
            'usuario' => new User(['activo' => true, 'rol' => Rol::Docente]),
            'roles'   => Rol::opciones(),
        ]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $usuario = User::create([
            'institucion_id' => InstitucionActual::id(),
            'nombre'         => $request->nombre,
            'apellido'       => $request->apellido,
            'email'          => $request->email,
            'dni'            => $request->dni,
            'rol'            => $request->rol,
            'activo'         => $request->boolean('activo'),
            'password'       => $request->filled('password') ? $request->password : $request->dni,
        ]);

        $aviso = $request->filled('password') ? '' : ' La contraseña inicial es su DNI.';

        return redirect()->route('usuarios.index')
            ->with('ok', "Se creó el usuario {$usuario->nombre_completo}.{$aviso}");
    }

    public function edit(User $usuario): View
    {
        $this->verificarInstitucion($usuario);

        return view('usuarios.form', [
            'usuario' => $usuario,
            'roles'   => Rol::opciones(),
        ]);
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $this->verificarInstitucion($usuario);

        $datos = $request->safe()->only(['nombre', 'apellido', 'email', 'dni']);

        // Nadie se cambia su propio rol ni se deshabilita (evita quedar afuera).
        if (! $this->esUnoMismo($usuario)) {
            $datos['rol']    = $request->rol;
            $datos['activo'] = $request->boolean('activo');
        }

        if ($request->filled('password')) {
            $datos['password'] = $request->password;
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')
            ->with('ok', "Se guardaron los cambios de {$usuario->nombre_completo}.");
    }

    public function toggleEstado(User $usuario): RedirectResponse
    {
        $this->verificarInstitucion($usuario);

        if ($this->esUnoMismo($usuario)) {
            return back()->with('error', 'No podés deshabilitar tu propio usuario.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('ok', $usuario->activo
            ? "{$usuario->nombre_completo} quedó habilitado."
            : "{$usuario->nombre_completo} quedó deshabilitado.");
    }

    /** Solo usuarios de la institución actual (y nunca el superadmin). */
    protected function verificarInstitucion(User $usuario): void
    {
        abort_unless($usuario->institucion_id === InstitucionActual::id(), 404);
    }

    protected function esUnoMismo(User $usuario): bool
    {
        return $usuario->id === auth()->id();
    }
}
