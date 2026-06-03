<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Exception;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => 'required|string',   // coincide con name="login" del blade
            'password' => 'required|string',
        ]);

        try {
            $this->authService->login($data, $request->ip());
            return redirect()->intended('/home');  // ajustar según la ruta del home
        } catch (Exception $e) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Usuario, DNI o contraseña incorrectos.']);
        }
    }
}