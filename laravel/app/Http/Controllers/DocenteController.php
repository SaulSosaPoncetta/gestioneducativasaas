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
        $request->validate([
            'apellido' => 'required|max:255',
            'nombre' => 'required|max:255',
            'dni' => 'required',
        ]);

        DB::transaction(function () use ($request) {

            $usuario = Usuario::create([
                'nombre'            => $request->nombre,
                'apellido'          => $request->apellido,
                'nombre_usuario'    => $request->nombre_usuario,
                'email'             => $request->email,
                'password_hash'     => Hash::make($request->password),
                'id_rol'            => 1,
                'genero'            => $request->genero,
                'estado'            => 'Activo',
                'id_localidad'      => $request->id_localidad,
                'fecha_nacimiento'  => $request->fecha_nacimiento,
                'domicilio'         => $request->domicilio,
            ]);

            Docente::create([
                'id_usuario' => $usuario->id_usuario,
            ]);

            Dni::create([
                'dni'         => $request->dni,
                'cuil'        => $request->cuil,
                'id_usuario'  => $usuario->id_usuario,
                'id_tipo_dni' => $request->id_tipo_dni,
            ]);

            Telefono::create([
                'telefono'   => $request->telefono,
                'id_usuario' => $usuario->id_usuario,
            ]);
        });

        return redirect()
            ->route('profesoresSec')
            ->with('success', 'Profesor agregado correctamente.');
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
        // dd($request->all());
        DB::transaction(function () use ($request, $docente) {
            $docente->usuario->update([
                'apellido' => $request->apellido,
                'nombre' => $request->nombre,
                'genero' => $request->genero,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'domicilio' => $request->domicilio,
                'email' => $request->email,
                'nombre_usuario' => $request->nombre_usuario,
                'id_localidad' => $request->id_localidad,
                'id_pais' => $request->id_pais,
                'estado'           => $request->estado,
            ]);
        });

        $telefono = $docente->usuario->telefonos->first();

        if ($telefono) {
            Telefono::where('telefono', $telefono->telefono)
                ->where('id_usuario', $telefono->id_usuario)
                ->update([
                    'telefono' => $request->telefono
                ]);
        }

        $dni = $docente->usuario->dnis->first();
        if ($dni) {

            Dni::where('dni', $dni->dni)
                ->where('id_usuario', $dni->id_usuario)
                ->delete();
        }
        Dni::create([
            'dni'=> $request->dni,
            'cuil'=> $request->cuil,
            'id_usuario'=> $docente->usuario->id_usuario,
            'id_tipo_dni'=> $request->id_tipo_dni,
        ]);
        return response()->json(['success' => true, 'message' => 'Profesor actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function toggleEstado(int $id): JsonResponse
    {
        $docente = $this->DocenteService->toggleEstado($id);
        $estado = $docente->usuario->estado;

        return response()->json([
            'success'    => true,
            'message'    => "Docente {$estado} correctamente.",
            'estado'  => $estado,
            'data'       => $docente,
        ]);
    }
}
