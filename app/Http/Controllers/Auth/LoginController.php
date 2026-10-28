<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login con email o DNI.
 *
 * El DNI se repite entre instituciones (un docente puede trabajar en dos),
 * así que se prueba la contraseña contra cada usuario con ese DNI.
 */
class LoginController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'usuario'  => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ], [], ['usuario' => 'email o DNI', 'password' => 'contraseña']);

        $clave = 'login:' . Str::lower($datos['usuario']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);
            throw ValidationException::withMessages([
                'usuario' => "Demasiados intentos. Probá de nuevo en {$segundos} segundos.",
            ]);
        }

        $usuario = $this->buscarUsuario($datos['usuario'], $datos['password']);

        if (! $usuario) {
            RateLimiter::hit($clave, 60);
            $this->registrarFallido($datos['usuario'], 'contraseña o usuario incorrecto');
            throw ValidationException::withMessages([
                'usuario' => 'Los datos ingresados no son correctos.',
            ]);
        }

        if (! $usuario->activo) {
            $this->registrarFallido($datos['usuario'], 'usuario deshabilitado', $usuario);
            throw ValidationException::withMessages([
                'usuario' => 'Tu usuario está deshabilitado.',
            ]);
        }

        if (! $usuario->esSuperAdmin() && ! $usuario->institucion?->activo) {
            throw ValidationException::withMessages([
                'usuario' => 'La institución no está habilitada. Comunicate con MiGestión.',
            ]);
        }

        RateLimiter::clear($clave);
        Auth::login($usuario, $request->boolean('recordar'));
        $request->session()->regenerate();
        $usuario->forceFill(['ultimo_acceso_at' => now()])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Intento fallido: se registra en la institución del usuario buscado
     * (si existe), para que su administración lo vea. Nunca se guarda la clave.
     */
    protected function registrarFallido(string $ingresado, string $motivo, ?User $usuario = null): void
    {
        $ingresado = trim($ingresado);
        $usuario ??= str_contains($ingresado, '@')
            ? User::where('email', Str::lower($ingresado))->first()
            : User::where('dni', preg_replace('/\D/', '', $ingresado))->first();

        Auditoria::registrar('ingreso_fallido', 'Ingreso fallido de "' . Str::limit($ingresado, 60) . '": ' . $motivo, [
            'usuario'        => null,
            'institucion_id' => $usuario?->institucion_id,
            'modelo_tipo'    => $usuario ? User::class : null,
            'modelo_id'      => $usuario?->id,
        ]);
    }

    protected function buscarUsuario(string $usuario, string $password): ?User
    {
        $usuario = trim($usuario);

        $candidatos = str_contains($usuario, '@')
            ? User::where('email', Str::lower($usuario))->get()
            : User::where('dni', preg_replace('/\D/', '', $usuario))->get();

        return $candidatos->first(fn (User $u) => Hash::check($password, $u->password));
    }
}
