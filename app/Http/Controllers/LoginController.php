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

            // Se compara por rol->descripcion (no por id_rol numérico) por el
            // mismo motivo que en el sidebar: el número puede no ser estable
            // según cómo se hayan seedeado los roles.
            switch (auth()->user()->rol->descripcion) {

                case 'Administrador':
                    return redirect()->route('home');

                case 'Estudiante':
                    // TODO: reemplazar por route('homeEst') cuando esa
                    // pantalla y su ruta existan. Por ahora manda a
                    // "Mis datos" del estudiante para no romper el login.
                    return redirect()->route('homeEst');

                case 'Docente':
                    // TODO: reemplazar por route('homeDoc') cuando esa
                    // pantalla y su ruta existan. Por ahora manda a
                    // "Mis datos" (misma ruta que usa Secretaría/Docente
                    // en el sidebar).
                    return redirect()->route('misdatos');

                default:
                    return redirect('/login');
            }

        }
        catch (ValidationException $e) {
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