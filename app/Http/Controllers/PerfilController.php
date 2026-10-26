<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * "Mis datos": cada usuario edita su nombre, email y contraseña.
 */
class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('perfil.edit', ['usuario' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $request->merge(['email' => $request->filled('email') ? mb_strtolower(trim($request->email)) : null]);

        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email'    => [
                $usuario->dni ? 'nullable' : 'required',
                'email', 'max:150',
                Rule::unique('users', 'email')->ignore($usuario->id),
            ],
        ]);

        $usuario->update($datos);

        return back()->with('ok', 'Tus datos se guardaron.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'confirmed', Password::min(8), 'different:password_actual'],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.different'                => 'La nueva contraseña tiene que ser distinta de la actual.',
        ], [
            'password_actual' => 'contraseña actual',
            'password'        => 'nueva contraseña',
        ]);

        $request->user()->update(['password' => $request->password]);

        return back()->with('ok', 'Tu contraseña se cambió.');
    }
}
