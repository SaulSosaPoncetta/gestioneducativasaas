<?php

namespace App\Http\Controllers;

use App\Services\AdministradorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdministradorController extends Controller
{
    protected AdministradorService $administradorService;

    public function __construct(AdministradorService $administradorService)
    {
        $this->administradorService = $administradorService;
    }
    public function create()
    {
        $tiposDni    = $this->administradorService->getTiposDni();
        $localidades = $this->administradorService->getLocalidades();
        $paises      = $this->administradorService->getPaises();

        return view('Secretaria.administracionAgregar', compact('tiposDni', 'localidades', 'paises'));
    }
    // -------------------------------------------------------------------------
    // GET /buscarAdministrador
    // Muestra la vista con administradores filtrados
    // Filtros: nombre_apellido, dni, habilitado
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $filtros         = $request->only(['nombre_apellido', 'dni', 'habilitado']);
        $administradores = $this->administradorService->buscar($filtros);

        return view('Secretaria.administracionSec', compact('administradores', 'filtros'));
    }

    // -------------------------------------------------------------------------
    // GET /administradores/{id}
    // Muestra el perfil completo de un administrador
    // -------------------------------------------------------------------------
    public function show(int $id)
{
    $administrador = $this->administradorService->getById($id);
    $telefonos     = $administrador->usuario->telefonos;
    $dni           = $administrador->usuario->dnis;
    $localidades   = $this->administradorService->getLocalidades();
    $paises        = $this->administradorService->getPaises();
    $tiposDni      = $this->administradorService->getTiposDni(); 

    $tipoDocumento = $dni->first() && $dni->first()->tipoDni->descripcion !== 'CUIL/CUIT'
        ? $dni->first()->tipoDni->descripcion: '—';

    return view('Secretaria.administracionPerfil', compact(
        'administrador',
        'telefonos',
        'dni',
        'localidades',
        'paises',
        'tiposDni',
        'tipoDocumento'
    ));
}

    // -------------------------------------------------------------------------
    // POST /administradores
    // Crea un nuevo administrador (llamado por AJAX desde la vista)
    // Body: { nombre, apellido, nombre_usuario, email, password,
    //         genero, id_localidad, dni, tipo_dni, telefonos: [] }
    // -------------------------------------------------------------------------
    public function store(Request $request)
{
    $validated = $request->validate([
    'nombre'           => 'required|string|max:100',
    'apellido'         => 'required|string|max:100',
    'nombre_usuario'   => 'required|string|max:50|unique:usuarios,nombre_usuario',
    'email'            => 'required|email|max:150|unique:usuarios,email',
    'password'         => 'required|string|min:8',
    'genero'           => 'required|in:M,F,Otro',
    'id_localidad'     => 'required|integer|exists:localidades,id_localidad',
    'id_pais'          => 'nullable|integer|exists:paises,id_pais',
    'domicilio'        => 'nullable|string|max:255',
    'fecha_nacimiento' => 'nullable|date',
    'dni'              => 'required|string|max:20|unique:dnis,dni',
    'cuil'             => 'nullable|string|max:13',
    'id_tipo_dni'      => 'required|integer|exists:tipos_dni,id_tipo_dni',
    'telefonos'        => 'required|array|min:1',
    'telefonos.*'      => 'required|string|max:20',
]);



    $administrador = $this->administradorService->create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Administrador creado correctamente.',
        'data'    => $administrador,
    ], 201);
}

    // -------------------------------------------------------------------------
    // PUT /administradores/{id}
    // Actualiza los datos del administrador (llamado por AJAX desde la vista)
    // -------------------------------------------------------------------------
   public function update(Request $request, int $id): JsonResponse
{
  
    $validator = Validator::make($request->all(), [
        'nombre'         => 'required|string|max:100',
        'apellido'       => 'required|string|max:100',
        'email'          => [
            'required', 'email', 'max:150',
            Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario'),
        ],
        'nombre_usuario' => [
            'required', 'string', 'max:50',
            Rule::unique('usuarios', 'nombre_usuario')->ignore($id, 'id_usuario'),
        ],
        'password'         => 'nullable|string|min:8',
        'genero'           => 'required|in:M,F,Otro',
        'id_localidad'     => 'required|integer|exists:localidades,id_localidad',
        'dni' => [
            'required', 'string', 'max:20',
            Rule::unique('dnis', 'dni')->where(function ($query) use ($id) {
                return $query->where('id_usuario', '!=', $id);
            }),
        ],
        'id_tipo_dni'      => 'required|integer|exists:tipos_dni,id_tipo_dni',
        'telefonos'        => 'required|array|min:1',
        'telefonos.*'      => 'required|string|max:20',
        'id_pais'          => 'nullable|integer|exists:paises,id_pais',
        'domicilio'        => 'nullable|string|max:255',
        'fecha_nacimiento' => 'nullable|date',
        'cuil'             => 'nullable|string|max:13',
        'estado'           => 'sometimes|in:Activo,Inactivo',
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
    // PATCH /administradores/{id}/estado
    // Habilita o deshabilita un administrador (toggle)
    // -------------------------------------------------------------------------
    public function toggleEstado(int $id): JsonResponse
    {
        $administrador = $this->administradorService->toggleEstado($id);
        $estado = $administrador->usuario->estado;

        return response()->json([
            'success'    => true,
            'message'    => "Administrador {$estado} correctamente.",
            'estado'  => $estado,
            'data'       => $administrador,
        ]);
    }
}