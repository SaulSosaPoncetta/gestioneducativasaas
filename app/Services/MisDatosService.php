<?php
namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class UsuarioService {
    /**
     * Obtiene los datos completos del usuario logueado.
     */
    public function getPerfilActual(): Usuario {
        // Retornamos el usuario autenticado
        return Auth::user();
    }
}
