<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    protected $profileService;

    public function __construct(ProfileService $profileService) {
        $this->profileService = $profileService;
    }

    public function update(Request $request) {
        // 1. Validar directamente en el controlador
        $validatedData = $request->validate([
            'cuil'                 => 'nullable|string|max:13',
            'fecha_nacimiento'     => 'required|date',
            'nacionalidad'         => 'required|string',
            'localidad_nacimiento' => 'required|string',
            'domicilio'            => 'required|string',
            'localidad_domicilio'  => 'required|string',
            'telefono_celular'     => 'nullable|string',
            'telefono_secundario'  => 'nullable|string',
            'contacto_alternativo' => 'nullable|string',
            'email'                => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        // 2. Pasar datos al servicio
        $this->profileService->updateData(Auth::user(), $validatedData);

        // 3. Redirigir con mensaje
        return back()->with('status', 'Datos actualizados con éxito.');
    }
}
