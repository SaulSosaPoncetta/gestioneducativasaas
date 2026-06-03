<?php

namespace App\Http\Controllers;

use App\Services\EstudianteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    protected EstudianteService $estudianteService;

    public function __construct() {
       $this->estudianteService = new EstudianteService();
    }

    // -------------------------------------------------------------------------
    // GET /buscarAlumno
    // Muestra la vista con todos los alumnos, o filtrados si hay parámetros
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $filtros = $request->only(['nombre_apellido', 'dni', 'anio', 'padron']);

          // Solo ejecutar la búsqueda si se envió algún filtro
        //$estudiantes = null;
            //if (array_filter($filtros)) {
        // Siempre busca - sin filtros trae todos, con filtros achica la lista
        $estudiantes = $this->estudianteService->buscar($filtros);

        return view('Secretaria.alumnosSec', compact('estudiantes', 'filtros'));
    }

    // -------------------------------------------------------------------------
    // GET /alumnoPerfil/{id}
    // Muestra el perfil completo de un alumno
    // -------------------------------------------------------------------------
    public function show(int $id)
    {
        $estudiante  = $this->estudianteService->getById($id);
        $dni         = $estudiante->dnis;
        $telefonos   = $estudiante->telefono;
        $localidades = $this->estudianteService->getLocalidades();

        return view('Secretaria.alumnoPerfil', compact(
            'estudiante',
            'dni',
            'telefonos',
            'localidades'
        ));
    }

    // -------------------------------------------------------------------------
    // PUT /alumnoPerfil/{id}
    // Actualiza los datos del alumno (llamado por AJAX desde la vista)
    // -------------------------------------------------------------------------
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'                 => 'required|string|max:100',
            'apellido'               => 'required|string|max:100',
            'email'                  => [
                'required', 'email', 'max:150',
                Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario'),
            ],
            'nombre_usuario'         => [
                'required', 'string', 'max:50',
                Rule::unique('usuarios', 'nombre_usuario')->ignore($id, 'id_usuario'),
            ],
            'password'               => 'nullable|string|min:8',
            'id_localidad_domicilio' => 'required|integer|exists:localidades,id_localidad',
            'telefono_celular'       => 'nullable|string|max:20',
            'telefono_secundario'    => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $estudiante = $this->estudianteService->update($id, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Datos del alumno actualizados correctamente.',
            'data'    => $estudiante,
        ]);
    }
}
