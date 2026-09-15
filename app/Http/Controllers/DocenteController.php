<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateDocenteRequest;
use App\Models\Usuario;
use App\Models\Docente;
use App\Services\DocenteService;
use App\Models\Pais;
use App\Models\Dni;
use App\Models\Telefono;
use App\Models\Localidad;
use App\Models\TipoDni;
use App\Models\Materia;
use App\Models\MateriaDocente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{

    protected $docenteService;

    public function __construct(
        DocenteService $docenteService
    )
    {
        $this->docenteService = $docenteService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtros = $request->only([
        'nombre_apellido',
        'dni',
        'estado',
        'tipo',
        'plan',
        'desde',
        'hasta'
    ]);

        $docentes = $this->docenteService->buscar($filtros);

        return view('Secretaria.profesoresSec', compact('docentes', 'filtros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiposDni = TipoDni::all();
        $paises = Pais::all();
        $localidades = Localidad::all();

        return view(
            'Secretaria.profesoresAgregar',
            compact(
                'tiposDni',
                'paises',
                'localidades'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'nombre'           => 'required|string|max:100',
        'apellido'         => 'required|string|max:100',
        'nombre_usuario'   => 'required|string|max:50|unique:usuarios,nombre_usuario',
        'email'            => 'required|email|max:150|unique:usuarios,email',
        'password'         => 'required|string|min:8',
        'genero'           => 'nullable|in:Masculino,Femenino,Otro',
        'id_localidad'     => 'required|integer|exists:localidades,id_localidad',
        'id_pais'          => 'required|integer|exists:paises,id_pais',
        'domicilio'        => 'required|string|max:255',
        'fecha_nacimiento' => 'required|date',
        'dni'              => 'required|digits_between:7,8|unique:dnis,dni',
        'cuil'             => ['nullable', 'regex:/^\d{2}-\d{8}-\d$/', 'unique:dnis,cuil'],
        'id_tipo_dni'      => 'required|integer|exists:tipos_dni,id_tipo_dni',
        'telefono'         => 'nullable|numeric|digits_between:10,13',
    ], [
        'dni.required'        => 'Debe ingresar un DNI.',
        'dni.digits_between'  => 'El DNI debe tener entre 7 y 8 dígitos.',
        'dni.unique'          => 'Ese DNI ya está registrado.',
        'cuil.regex'          => 'El CUIL/CUIT debe tener el formato 20-12345678-3.',
        'cuil.unique'         => 'Ese CUIL/CUIT ya está registrado.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación.',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $datos = $validator->validated();

    DB::transaction(function () use ($datos) {

        $usuario = Usuario::create([
            'nombre'           => $datos['nombre'],
            'apellido'         => $datos['apellido'],
            'nombre_usuario'   => $datos['nombre_usuario'],
            'email'            => $datos['email'],
            'password_hash'    => Hash::make($datos['password']),
            'id_rol'           => 1,
            'genero'           => $datos['genero'] ?? null,
            'estado'           => 'Activo',
            'id_localidad'     => $datos['id_localidad'] ?? null,
            'id_pais'          => $datos['id_pais'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'domicilio'        => $datos['domicilio'] ?? null,
        ]);

        Docente::create([
            'id_usuario' => $usuario->id_usuario,
        ]);

        Dni::create([
            'dni'         => $datos['dni'],
            'cuil'        => $datos['cuil'] ?? null,
            'id_usuario'  => $usuario->id_usuario,
            'id_tipo_dni' => $datos['id_tipo_dni'],
        ]);

        if (!empty($datos['telefono'])) {
            Telefono::create([
                'telefono'   => $datos['telefono'],
                'id_usuario' => $usuario->id_usuario,
            ]);
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Profesor agregado correctamente.',
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $docente = Docente::with([
                'usuario',
                // 'usuario.dnis',
                'usuario.dnis.tipoDni',
                'usuario.telefonos',
                'usuario.localidad.pais',
                'materias.carrera',
                'materias.tipoMateria',
        ])->findOrFail($id);

        $tiposDni = TipoDni::all();
        $paises = Pais::all();
        $localidades = Localidad::all();

        return view(
            'Secretaria.profesoresPerfil',
            compact('docente', 'tiposDni', 'paises', 'localidades')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocenteRequest $request, string $id)
{
    $docente = Docente::with('usuario')->findOrFail($id);
    $datos   = $request->validated();

    DB::transaction(function () use ($datos, $docente) {

        $docente->usuario->update([
            'apellido'         => $datos['apellido'],
            'nombre'           => $datos['nombre'],
            'genero'           => $datos['genero'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'domicilio'        => $datos['domicilio'] ?? null,
            'email'            => $datos['email'],
            'nombre_usuario'   => $datos['nombre_usuario'],
            'id_localidad'     => $datos['id_localidad'] ?? null,
            'id_pais'          => $datos['id_pais'] ?? null,
            'estado'           => $datos['estado'] ?? $docente->usuario->estado,
        ]);

        if (!empty($datos['password'])) {
            $docente->usuario->update([
                'password_hash' => \Illuminate\Support\Facades\Hash::make($datos['password']),
            ]);
        }

        $telefono = $docente->usuario->telefonos->first();

        if (!empty($datos['telefono'])) {
            if ($telefono) {
                Telefono::where('telefono', $telefono->telefono)
                    ->where('id_usuario', $telefono->id_usuario)
                    ->update(['telefono' => $datos['telefono']]);
            } else {
                Telefono::create([
                    'telefono'   => $datos['telefono'],
                    'id_usuario' => $docente->usuario->id_usuario,
                ]);
            }
        }

        $dni = $docente->usuario->dnis->first();

        if ($dni) {
            Dni::where('dni', $dni->dni)
                ->where('id_usuario', $dni->id_usuario)
                ->delete();
        }

        Dni::create([
            'dni'         => $datos['dni'],
            'cuil'        => $datos['cuil'] ?? null,
            'id_usuario'  => $docente->usuario->id_usuario,
            'id_tipo_dni' => $datos['id_tipo_dni'],
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Profesor actualizado correctamente.',
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function toggleEstado(int $id): JsonResponse
    {
        $docente = $this->docenteService->toggleEstado($id);
        $estado = $docente->usuario->estado;

        return response()->json([
            'success'    => true,
            'message'    => "Docente {$estado} correctamente.",
            'estado'  => $estado,
            'data'       => $docente,
        ]);
    }
}
