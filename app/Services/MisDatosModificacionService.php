<?php
namespace App\Services;

use App\Models\Usuario;

class ProfileService {
    public function updateData(Usuario $usuario, array $data): bool {
        // Actualiza el modelo con el array de datos
        return $usuario->update($data);
    }
}
