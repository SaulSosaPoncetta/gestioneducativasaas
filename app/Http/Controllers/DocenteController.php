<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Http\Requests\DocenteRequest;
use App\Models\Asignacion;
use App\Models\CicloLectivo;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Legajos docentes. Opcionalmente crea (o vincula) la cuenta de acceso
 * con rol docente para que use el portal "Mis cursos".
 */
class DocenteController extends Controller
{
    public function index(Request $request): View
    {
        $ciclo = CicloLectivo::actual();

        $docentes = Docente::query()
            ->buscar($request->q)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->sin_asignacion && $ciclo, fn ($q) => $q->where('estado', 'activo')
                ->whereDoesntHave('asignaciones', fn ($a) => $a->where('ciclo_lectivo_id', $ciclo->id)->activas()))
            ->withCount(['asignaciones as asignaciones_ciclo' => fn ($q) => $q->where('ciclo_lectivo_id', $ciclo?->id)->activas()])
            ->withSum(['asignaciones as horas_ciclo' => fn ($q) => $q->where('ciclo_lectivo_id', $ciclo?->id)->activas()], 'horas')
            ->with('usuario')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('docentes.index', compact('docentes', 'ciclo'));
    }

    public function create(): View
    {
        return view('docentes.form', [
            'docente' => new Docente(['estado' => 'activo', 'fecha_ingreso' => today()]),
        ]);
    }

    public function store(DocenteRequest $request): RedirectResponse
    {
        [$docente, $aviso] = DB::transaction(function () use ($request) {
            $docente = Docente::create($request->datosDocente());
            $aviso = $request->boolean('crear_usuario') ? $this->vincularUsuario($docente) : null;

            return [$docente, $aviso];
        });

        return redirect()->route('docentes.show', $docente)
            ->with('ok', "Se creó el legajo de {$docente->nombre_completo}." . ($aviso ? " {$aviso}" : ''));
    }

    public function show(Docente $docente): View
    {
        $docente->load('usuario');

        $asignaciones = Asignacion::with(['ciclo', 'espacio.plan.nivel', 'curso.nivel'])
            ->where('docente_id', $docente->id)
            ->orderByDesc('ciclo_lectivo_id')
            ->orderBy('estado')
            ->get()
            ->groupBy('ciclo_lectivo_id');

        return view('docentes.show', compact('docente', 'asignaciones'));
    }

    public function edit(Docente $docente): View
    {
        return view('docentes.form', ['docente' => $docente->load('usuario')]);
    }

    public function update(DocenteRequest $request, Docente $docente): RedirectResponse
    {
        $aviso = DB::transaction(function () use ($request, $docente) {
            $docente->update($request->datosDocente());

            // Mantener los datos de la cuenta en sincronía con el legajo.
            if ($docente->usuario) {
                $docente->usuario->update([
                    'nombre'   => $docente->nombre,
                    'apellido' => $docente->apellido,
                    'dni'      => $docente->documento,
                    'email'    => $docente->email ?? $docente->usuario->email,
                    'activo'   => $docente->estado !== 'baja' ? $docente->usuario->activo : false,
                ]);

                return null;
            }

            return $request->boolean('crear_usuario') ? $this->vincularUsuario($docente) : null;
        });

        return redirect()->route('docentes.show', $docente)
            ->with('ok', 'Se guardaron los datos del docente.' . ($aviso ? " {$aviso}" : ''));
    }

    /**
     * Vincula un usuario existente de la institución con el mismo DNI,
     * o crea uno nuevo con rol docente (contraseña inicial: DNI).
     */
    protected function vincularUsuario(Docente $docente): string
    {
        $usuario = User::deInstitucion()->where('dni', $docente->documento)->first();

        if ($usuario) {
            $docente->update(['user_id' => $usuario->id]);

            return "Se vinculó con el usuario existente ({$usuario->rol->label()}).";
        }

        $usuario = User::create([
            'institucion_id' => $docente->institucion_id,
            'nombre'         => $docente->nombre,
            'apellido'       => $docente->apellido,
            'dni'            => $docente->documento,
            'email'          => $docente->email,
            'password'       => $docente->documento,
            'rol'            => Rol::Docente,
            'activo'         => true,
        ]);

        $docente->update(['user_id' => $usuario->id]);

        return 'Se creó su usuario: ingresa con su DNI y la contraseña inicial es el DNI.';
    }
}
