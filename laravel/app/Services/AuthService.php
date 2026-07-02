<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Dni;
use App\Models\CategoriaAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Autenticar al usuario por email, nombre_usuario o DNI + password.
     * Lanza excepción si las credenciales son incorrectas o el usuario está deshabilitado.
     */
    public function login(array $data, string $ip): bool
    {
        $loginValue = $data['login'];

        $usuario = $this->resolverUsuario($loginValue);

        if (!$usuario || !Hash::check($data['password'], $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'login' => 'Usuario, DNI o contraseña incorrectos.',
            ]);
        }

        // Verificar que el usuario esté habilitado (cuando exista la columna)
        if (isset($usuario->habilitado) && !$usuario->habilitado) {
            throw ValidationException::withMessages([
                'login' => 'Tu cuenta se encuentra deshabilitada. Contactá al administrador.',
            ]);
        }

        Auth::login($usuario);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->registrarAuditoria($usuario, $ip);

        return true;
    }

    /**
     * Cerrar la sesión del usuario autenticado.
     */
    public function logout(): void
    {
        Auth::logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }

    /**
     * Enviar el link de reseteo de contraseña al email indicado.
     */
    public function sendResetLink(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Resuelve el usuario a partir del valor ingresado:
     *   - Si es email      → busca por email en usuarios
     *   - Si es numérico   → busca por DNI en la tabla dnis
     *   - Cualquier otro   → busca por nombre_usuario en usuarios
     */
    private function resolverUsuario(string $loginValue): ?Usuario
    {
        if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
            return Usuario::where('email', $loginValue)->first();
        }

        if (ctype_digit($loginValue)) {
            $dni = Dni::where('dni', $loginValue)->first();
            return $dni?->usuario;
        }

        return Usuario::where('nombre_usuario', $loginValue)->first();
    }

    /**
     * Registrar el inicio de sesión en la tabla de auditorías.
     * Crea la categoría 'Login' si no existe.
     */
    private function registrarAuditoria(Usuario $usuario, string $ip): void
    {
        $categoria = CategoriaAuditoria::firstOrCreate(
            ['descripcion_categoria' => 'Login']
        );

        $usuario->auditorias()->create([
            'id_categoria_auditoria' => $categoria->id_categoria_auditoria,
            'nombre_tabla'           => 'usuarios',
            'id_registro'            => $usuario->id_usuario,
            'accion'                 => 'login',
            'descripcion'            => 'Inicio de sesión desde ' . $ip,
            'fecha_hora'             => now(),
        ]);
    }
}