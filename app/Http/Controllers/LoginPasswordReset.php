<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginPasswordReset extends Controller
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = app(AuthService::class);
    }

    // -------------------------------------------------------------------------
    // GET /forgot-password
    // Muestra el formulario para solicitar el reseteo de contraseña
    // -------------------------------------------------------------------------
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // -------------------------------------------------------------------------
    // POST /forgot-password
    // Envía el link de reseteo al email indicado
    // -------------------------------------------------------------------------
    public function sendPasswordResetToken(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ]);

        try {
            $mensaje = $this->authService->sendResetLink($data);

            return back()->with('status', $mensaje);

        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }
}