<?php

namespace App\Http\Controllers;

use App\Services\AdministradorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdministradorController extends Controller
{
    public function __construct(
        protected AdministradorService $administradorService
    ) {}

    // -------------------------------------------------------------------------
    // GET /administradores
    // -------------------------------------------------------------------------
    public function index(): JsonResponse
    {
        $administradores = $this->administradorService->getAll();

        return response()->json([
            'success' => true,
            'data'    => $administradores,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /administradores/{id}
    // -------------------------------------------------------------------------
    public function show(int $id): JsonResponse
    {
        $administrador = $this->administradorService->getById($id);

        return response()->json([
            'success' => true,
            'data'    => $administrador,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /administradores
    // -------------------------------------------------------------------------
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'         => 'required|string|max:100',
            'apellido'       => 'required|string|max:100',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'email'          => 'required|email|max:150|unique:usuarios,email',
            'password'       => 'required|string|min:8',
            'genero'         => 'required|in:M,F,Otro',
            'id_localidad'   => 'required|integer|exists:localidades,id_localidad',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $administrador = $this->administradorService->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Administrador creado correctamente.',
            'data'    => $administrador,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // PUT /administradores/{id}
    // -------------------------------------------------------------------------
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'         => 'sometimes|required|string|max:100',
            'apellido'       => 'sometimes|required|string|max:100',
            'nombre_usuario' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('usuarios', 'nombre_usuario')->ignore($id, 'id_usuario'),
            ],
            'email' => [
                'sometimes', 'required', 'email', 'max:150',
                Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario'),
            ],
            'password'     => 'sometimes|nullable|string|min:8',
            'genero'       => 'sometimes|required|in:M,F,Otro',
            'id_localidad' => 'sometimes|required|integer|exists:localidades,id_localidad',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $administrador = $this->administradorService->update($id, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Administrador actualizado correctamente.',
            'data'    => $administrador,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /administradores/{id}
    // -------------------------------------------------------------------------
    public function destroy(int $id): JsonResponse
    {
        $this->administradorService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Administrador eliminado correctamente.',
        ]);
    }
}