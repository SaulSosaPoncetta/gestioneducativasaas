<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected AuthService $authService;

   
    public function __construct()
    {
        $this->authService = app(AuthService::class); 
    }

    // -------------------------------------------------------------------------
    // GET /login
    // Muestra la vista del formulario de login
    // -------------------------------------------------------------------------
    public function showLogin()
    {
        return view('auth.login');
    }

    // -------------------------------------------------------------------------
    // POST /login
    // Autentica al usuario por email, nombre_usuario o DNI
    // -------------------------------------------------------------------------
    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $this->authService->login($data, $request->ip());

            return redirect()->intended('/homeSec');

        } catch (ValidationException $e) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors($e->errors());
        }
    }

    // -------------------------------------------------------------------------
    // POST /logout
    // Cierra la sesión del usuario autenticado
    // -------------------------------------------------------------------------
    public function logout(Request $request)
    {
        $this->authService->logout();

        return redirect('/login');
    }
}