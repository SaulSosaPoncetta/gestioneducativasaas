<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct()
{

    $this->profileService = new ProfileService();
}

    // -------------------------------------------------------------------------
    // GET /perfil
    // Muestra los datos del usuario autenticado
    // -------------------------------------------------------------------------
    public function show()
    {
        $usuario     = $this->profileService->getPerfil();
        $paises      = \App\Models\Pais::orderBy('nombre_pais')->get();
        $localidades = \App\Models\Localidad::orderBy('nombre_localidad')->get();
        $tiposDni    = \App\Models\TipoDni::orderBy('descripcion')->get();

        return view('Secretaria.misDatos', compact('usuario', 'paises', 'localidades', 'tiposDni'));
    }

    // -------------------------------------------------------------------------
    // PUT /perfil
    // Actualiza los datos del usuario autenticado (llamado por AJAX desde la vista)
    // -------------------------------------------------------------------------
    public function update(Request $request): JsonResponse
    {
        $id = Auth::user()->id_usuario;

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre'         => 'required|string|max:100',
            'apellido'       => 'required|string|max:100',
            'nombre_usuario' => [
                'required', 'string', 'max:50',
                Rule::unique('usuarios', 'nombre_usuario')->ignore($id, 'id_usuario'),
            ],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario'),
            ],
            'password'     => 'nullable|string|min:8',
            'genero'       => 'required|in:M,F,Otro',
            'id_localidad' => 'required|integer|exists:localidades,id_localidad',
            'telefonos'    => 'nullable|array',
            'dni'         => 'nullable|string|max:20',
            'id_tipo_dni' => 'nullable|integer|exists:tipos_dni,id_tipo_dni',
            'id_pais'          => 'nullable|integer|exists:paises,id_pais',
            'domicilio'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'cuil'             => 'nullable|string|max:13',
            'estado'           => 'required|in:Activo,Inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $usuario = $this->profileService->updatePerfil(Auth::user(), $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Datos actualizados correctamente.',
            'data'    => $usuario,
        ]);
    }
        public function showEstudiante()
    {
        $usuario     = $this->profileService->getPerfil();
        $paises      = \App\Models\Pais::orderBy('nombre_pais')->get();
        $localidades = \App\Models\Localidad::orderBy('nombre_localidad')->get();
        $tiposDni    = \App\Models\TipoDni::orderBy('descripcion')->get();

        return view('Estudiante.misDatos', compact('usuario', 'paises', 'localidades', 'tiposDni'));
    }
}
