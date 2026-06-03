<?php
namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\View\View;

class UsuarioController extends Controller {
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService) {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Muestra la pantalla de mis datos personales.
     */
    public function show(): View {
        $usuario = $this->usuarioService->getPerfilActual();
        
        return view('usuarios.perfil', compact('usuario'));
    }
}
