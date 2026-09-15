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

    public function index(Request $request)
    {
        $filtros         = $request->only(['nombre_apellido', 'dni', 'estado']);
        $administradores = $this->administradorService->buscar($filtros);

        return view('Secretaria.administracionSec', compact('administradores', 'filtros'));
    }

    public function show(int $id)
    {
        $administrador = $this->administradorService->getById($id);
        $telefonos     = $administrador->usuario->telefonos;
        $dni           = $administrador->usuario->dnis;
        $localidades   = $this->administradorService->getLocalidades();
        $paises        = $this->administradorService->getPaises();
        $tiposDni      = $this->administradorService->getTiposDni();

        $tipoDocumento = $dni->first() ? $dni->first()->tipoDni->descripcion : '—';

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'nombre_usuario'   => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'email'            => 'required|email|max:150|unique:usuarios,email',
            'password'         => 'required|string|min:8',
            'genero'           => 'nullable|in:M,F,Otro',
            'id_localidad'     => 'required|integer|exists:localidades,id_localidad',
            'id_pais'          => 'required|integer|exists:paises,id_pais',
            'domicilio'        => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'dni'              => 'required|digits_between:7,8|unique:dnis,dni',
            'cuil'             => ['nullable', 'regex:/^\d{2}-\d{8}-\d$/', 'unique:dnis,cuil'],
            'id_tipo_dni'      => 'required|integer|exists:tipos_dni,id_tipo_dni',
            'telefono'         => 'nullable|numeric|digits_between:10,13',
        ], [
            'dni.required'       => 'Debe ingresar un DNI.',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos.',
            'dni.unique'         => 'Ese DNI ya está registrado.',
            'cuil.regex'         => 'El CUIL/CUIT debe tener el formato 20-12345678-3.',
            'cuil.unique'        => 'Ese CUIL/CUIT ya está registrado.',
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

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'email'            => [
                'required', 'email', 'max:150',
                Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario'),
            ],
            'nombre_usuario'   => [
                'required', 'string', 'max:50',
                Rule::unique('usuarios', 'nombre_usuario')->ignore($id, 'id_usuario'),
            ],
            'password'         => 'nullable|string|min:8',
            'genero'           => 'nullable|in:M,F,Otro',
            'id_localidad'     => 'nullable|integer|exists:localidades,id_localidad',
            'dni'              => [
                'required', 'digits_between:7,8',
                Rule::unique('dnis', 'dni')->where(fn($q) => $q->where('id_usuario', '!=', $id)),
            ],
            'cuil'             => [
                'nullable',
                'regex:/^\d{2}-\d{8}-\d$/',
                Rule::unique('dnis', 'cuil')->where(fn($q) => $q->where('id_usuario', '!=', $id)),
            ],
            'id_tipo_dni'      => 'required|integer|exists:tipos_dni,id_tipo_dni',
            'telefono'         => 'nullable|numeric|digits_between:10,13',
            'id_pais'          => 'nullable|integer|exists:paises,id_pais',
            'domicilio'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'estado'           => 'sometimes|in:Activo,Inactivo',
        ], [
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos.',
            'dni.unique'         => 'Ese DNI ya está registrado.',
            'cuil.regex'         => 'El CUIL/CUIT debe tener el formato 20-12345678-3.',
            'cuil.unique'        => 'Ese CUIL/CUIT ya está registrado.',
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

    public function toggleEstado(int $id): JsonResponse
    {
        $administrador = $this->administradorService->toggleEstado($id);
        $estado = $administrador->usuario->estado;

        return response()->json([
            'success' => true,
            'message' => "Administrador {$estado} correctamente.",
            'estado'  => $estado,
            'data'    => $administrador,
        ]);
    }
}
