<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;

class LoginPasswordReset extends Controller
{
  
    protected $authService;

    // El constructor recibe el servicio y lo ASIGNA a la propiedad
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function sendPasswordResetToken(Request $request)
    {
        // 1. Validar que el email exista en la tabla usuarios
        $data = $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ]);

        // 2. Ejecutar servicio
        $mensaje = $this->authService->sendResetLink($data);

        // 3. Responder
        return back()->with('status', $mensaje);
    }
}
