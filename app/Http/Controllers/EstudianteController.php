<?php

namespace App\Http\Controllers;

use App\Services\EstudianteService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    protected EstudianteService $estudianteService;

    public function __construct(EstudianteService $estudianteService)
    {
        $this->estudianteService = $estudianteService;
    }

    // -------------------------------------------------------------------------
    // GET /buscarAlumno
    // Lista de alumnos con filtros opcionales
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $filtros = $request->only(['nombre_apellido', 'dni', 'anio', 'padron']);

        $estudiantes = $this->estudianteService->buscar($filtros);

        return view('Secretaria.alumnosSec', compact('estudiantes', 'filtros'));
    }

    // -------------------------------------------------------------------------
    // GET /alumnoAgregar
    // Formulario para crear un nuevo alumno
    // -------------------------------------------------------------------------
    public function create()
    {
        $tiposDni    = $this->estudianteService->getTiposDni();
        $paises      = $this->estudianteService->getPaises();
        $localidades = $this->estudianteService->getLocalidades();

        return view('Secretaria.alumnoAgregar', compact('tiposDni', 'paises', 'localidades'));
    }

    // -------------------------------------------------------------------------
    // POST /alumnoAgregar
    // Guarda un nuevo alumno (AJAX JSON)
    // -------------------------------------------------------------------------
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'required|string|max:100',
            'nombre_usuario'     => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'email'              => 'required|email|max:150|unique:usuarios,email',
            'password'           => 'required|string|min:8',
            'genero'             => 'nullable|in:M,F,Otro',
            'id_localidad'       => 'required|integer|exists:localidades,id_localidad',
            'id_pais'            => 'nullable|integer|exists:paises,id_pais',
            'domicilio'          => 'nullable|string|max:255',
            'fecha_nacimiento'   => 'nullable|date',
            'dni'                => 'required|string|max:20|unique:dnis,dni',
            'cuil'               => 'nullable|string|max:13',
            'id_tipo_dni'        => 'required|integer|exists:tipos_dni,id_tipo_dni',
            'telefono_celular'   => 'nullable|string|max:20',
            'telefono_secundario'=> 'nullable|string|max:20',
        ]);

        $estudiante = $this->estudianteService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alumno creado correctamente.',
            'data'    => $estudiante,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /alumnoPerfil/{id}
    // Muestra el perfil completo de un alumno
    // -------------------------------------------------------------------------
    public function show(int $id)
    {
        $estudiante = $this->estudianteService->getById($id);

        // DNI principal (primer registro)
        $dniRegistro     = $estudiante->usuario->dnis->first();
        $dniNumero       = $dniRegistro?->dni   ?? '—';
        $dniCuil         = $dniRegistro?->cuil  ?? '';
        $tipoDni         = $dniRegistro?->tipoDni?->descripcion ?? '—';
        $idTipoDni       = $dniRegistro?->id_tipo_dni ?? null;

        // Teléfonos
        $tels            = $estudiante->usuario->telefonos->values();
        $telefonos = [
            'celular'    => $tels->get(0)?->telefono ?? '',
            'secundario' => $tels->get(1)?->telefono ?? '',
        ];

        $localidades = $this->estudianteService->getLocalidades();
        $tiposDni    = $this->estudianteService->getTiposDni();

        return view('Secretaria.alumnoPerfil', compact(
            'estudiante',
            'dniNumero',
            'dniCuil',
            'tipoDni',
            'idTipoDni',
            'telefonos',
            'localidades',
            'tiposDni'
        ));
    }

    // -------------------------------------------------------------------------
    // PUT /alumnoPerfil/{id}
    // Actualiza los datos del alumno (AJAX)
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
            'estado'                 => 'nullable|in:Activo,Inactivo',
            'telefono_celular'       => 'nullable|string|max:20',
            'telefono_secundario'    => 'nullable|string|max:20',
            'dni'                    => [
                'nullable', 'string', 'max:20',
                Rule::unique('dnis', 'dni')->where(function ($query) use ($id) {
                    return $query->where('id_usuario', '!=', $id);
                }),
            ],
            'cuil'                   => 'nullable|string|max:13',
            'id_tipo_dni'            => 'nullable|integer|exists:tipos_dni,id_tipo_dni',
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

    // -------------------------------------------------------------------------
    // PATCH /alumnos/{id}/estado
    // Habilita o deshabilita un alumno (toggle)
    // -------------------------------------------------------------------------
    public function toggleEstado(int $id): JsonResponse
    {
        $estudiante = $this->estudianteService->toggleEstado($id);
        $estado     = $estudiante->usuario->estado;

        return response()->json([
            'success' => true,
            'message' => "Alumno {$estado} correctamente.",
            'estado'  => $estado,
            'data'    => $estudiante,
        ]);
    }
}
