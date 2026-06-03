<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;

class AuthService
{
    public function login(array $data, string $ip): bool
    {
        $loginValue = $data['identificador'];
        $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'dni';

        if (!Auth::attempt([$fieldType => $loginValue, 'password' => $data['password']])) {
            throw ValidationException::withMessages([
                'identificador' => 'El email/dni o la contraseña son incorrectos.',
            ]);
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->logActivity(Auth::user(), $ip);
        return true;
    }

    private function logActivity(Usuario $usuario, string $ip): void
    {
        $usuario->logs()->create([
            'descripcion' => 'Inicio de sesión desde ' . $ip,
            'fecha_hora'  => now(),
        ]);
    }

    public function getAllUsuarios()
    {
        return Usuario::all();
    }

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
}