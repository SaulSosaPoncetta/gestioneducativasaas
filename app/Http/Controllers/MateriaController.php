<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Services\MateriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MateriaController extends Controller
{
    protected MateriaService $materiaService;

    public function __construct(MateriaService $materiaService)
    {
        $this->materiaService = $materiaService;
    }

    // -------------------------------------------------------------------------
    // GET /carreras/{carrera}/materiaAgregar
    // -------------------------------------------------------------------------
    public function create(Carrera $carrera)
    {
        $planesEstudio = PlanEstudio::whereHas('resoluciones', function ($q) use ($carrera) {
            $q->where('plan_resolucion.id_carrera', $carrera->id_carrera);
        })->orderBy('titulo')->get();

        return view('Secretaria.materiaAgregar', compact('carrera', 'planesEstudio'));
    }

    // -------------------------------------------------------------------------
    // POST /carreras/{carrera}/materiaAgregar
    // -------------------------------------------------------------------------
    public function store(Request $request, Carrera $carrera): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'               => 'required|string|max:150',
            'id_plan_estudio'      => 'required|integer|exists:planes_estudio,id_plan',
            'id_anio'              => 'required|integer|min:1|max:5',
            'orden_anio'           => 'required|integer|min:1|max:20',
            'carga_horaria_anual'  => 'required|integer|min:1',
            'tfo_edi_udi'          => 'nullable|boolean',
            'acreditable_final'    => 'nullable|boolean',
            'promocionable'        => 'nullable|boolean',
            'optativa'             => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();

        $materia = $this->materiaService->crear($carrera, [
            'nombre'              => $datos['nombre'],
            'id_plan'             => $datos['id_plan_estudio'],
            'anio'                => $datos['id_anio'],
            'orden_anio'          => $datos['orden_anio'],
            'carga_horaria_anual' => $datos['carga_horaria_anual'],
            'tfo_edi_udi'         => $request->boolean('tfo_edi_udi'),
            'acreditable_final'   => $request->boolean('acreditable_final'),
            'promocionable'       => $request->boolean('promocionable'),
            'optativa'            => $request->boolean('optativa'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materia agregada correctamente.',
            'data'    => $materia,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /materiaPerfil/{materia}
    // -------------------------------------------------------------------------
    public function show(int $materia)
    {
        $materia = $this->materiaService->getById($materia);
        $profesoresAsignados = $materia->materiaDocentes;

        return view('Secretaria.materiaPerfil', compact('materia', 'profesoresAsignados'));
    }

    // -------------------------------------------------------------------------
    // PUT /materiaPerfil/{materia}
    // -------------------------------------------------------------------------
    public function update(Request $request, int $materia): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'              => 'required|string|max:150',
            'carga_horaria_anual' => 'required|integer|min:1',
            'tfo_edi_udi'         => 'nullable|boolean',
            'acreditable_final'   => 'nullable|boolean',
            'promocionable'       => 'nullable|boolean',
            'optativa'            => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();
        $datos['tfo_edi_udi']       = $request->boolean('tfo_edi_udi');
        $datos['acreditable_final'] = $request->boolean('acreditable_final');
        $datos['promocionable']     = $request->boolean('promocionable');
        $datos['optativa']          = $request->boolean('optativa');

        $materiaActualizada = $this->materiaService->actualizar($materia, $datos);

        return response()->json([
            'success' => true,
            'message' => 'Materia actualizada correctamente.',
            'data'    => $materiaActualizada,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /materias/{materia}/asignarProfesor
    // -------------------------------------------------------------------------
    public function formAsignarDocente(int $materia)
    {
        $materia   = $this->materiaService->getById($materia);
        $docentes  = $this->materiaService->docentesDisponibles($materia);

        return view('Secretaria.docMatUpload', compact('materia', 'docentes'));
    }

    // -------------------------------------------------------------------------
    // POST /materias/{materia}/asignarProfesor
    // -------------------------------------------------------------------------
    public function storeAsignarDocente(Request $request, int $materia): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $validator = Validator::make($request->all(), [
            'id_docente'        => 'required|integer|exists:docentes,id_usuario',
            'division'          => 'nullable|integer|min:1',
            'situacion_revista' => 'nullable|string|max:50',
            'fecha_inicio'      => 'nullable|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'horas_semanales'   => 'nullable|integer|min:1|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $this->materiaService->asignarDocente($materiaModelo, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profesor asignado correctamente.',
        ], 201);
    }

    // -------------------------------------------------------------------------
    // DELETE /materias/{materia}/profesor/{docente}
    // -------------------------------------------------------------------------
    public function destroyDocente(int $materia, int $docente): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $this->materiaService->quitarDocente($materiaModelo, $docente);

        return response()->json([
            'success' => true,
            'message' => 'Profesor quitado de la materia correctamente.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /materias/{materia}/cargarNota
    // -------------------------------------------------------------------------
    public function formCargarNotas(int $materia)
    {
        $materia = $this->materiaService->getById($materia);
        $datos   = $this->materiaService->datosCargarNotas($materia);

        return view('Secretaria.cargarNota', array_merge(['materia' => $materia], $datos));
    }

    // -------------------------------------------------------------------------
    // POST /materias/{materia}/cargarNota
    // -------------------------------------------------------------------------
    public function storeCargarNotas(Request $request, int $materia): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $validator = Validator::make($request->all(), [
            'cierre_automatico'            => 'nullable|boolean',
            'alumnos'                      => 'required|array|min:1',
            'alumnos.*.id_usuario'         => 'required|integer|exists:estudiantes,id_usuario',
            'alumnos.*.1C.valor'           => 'nullable|numeric|min:0|max:10',
            'alumnos.*.1C.situacion'       => 'nullable|in:promocionado,aprobado,desaprobado,promocion_directa',
            'alumnos.*.2C.valor'           => 'nullable|numeric|min:0|max:10',
            'alumnos.*.2C.situacion'       => 'nullable|in:promocionado,aprobado,desaprobado,promocion_directa',
            'alumnos.*.REC.valor'          => 'nullable|numeric|min:0|max:10',
            'alumnos.*.REC.situacion'      => 'nullable|in:aprobado,desaprobado',
            'alumnos.*.CIERRE.valor'       => 'nullable|numeric|min:0|max:10',
            'alumnos.*.CIERRE.situacion'   => 'nullable|in:promocionado,aprobado,desaprobado,promocion_directa',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $this->materiaService->guardarNotas(
            $materiaModelo,
            $validator->validated()['alumnos'],
            $request->boolean('cierre_automatico')
        );

        return response()->json([
            'success' => true,
            'message' => 'Notas guardadas correctamente.',
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /materias/{materia}/alumnos/{alumno}/reprobar
    // -------------------------------------------------------------------------
    public function reprobarAlumno(int $materia, int $alumno): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $this->materiaService->reprobarDirecto($materiaModelo, $alumno);

        return response()->json([
            'success' => true,
            'message' => 'Alumno reprobado directamente.',
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /materias/{materia}/alumnos/{alumno}/restaurar
    // -------------------------------------------------------------------------
    public function restaurarAlumno(int $materia, int $alumno): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $this->materiaService->restaurarCursando($materiaModelo, $alumno);

        return response()->json([
            'success' => true,
            'message' => 'Alumno restaurado a cursando.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /materias/{materia}/agregarAlumno
    // -------------------------------------------------------------------------
    public function formAgregarAlumno(Request $request, int $materia)
    {
        $materia = $this->materiaService->getById($materia);

        $busqueda    = $request->get('nombre');
        $disponibles = $this->materiaService->alumnosDisponibles($materia, $busqueda);
        $agregados   = $this->materiaService->alumnosAsignados($materia);

        return view('Secretaria.estMatUpload', compact('materia', 'disponibles', 'agregados'));
    }

    // -------------------------------------------------------------------------
    // GET /materias/{materia}/alumnosDisponibles (AJAX búsqueda)
    // -------------------------------------------------------------------------
    public function buscarAlumnosDisponibles(Request $request, int $materia): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);
        $disponibles   = $this->materiaService->alumnosDisponibles($materiaModelo, $request->get('nombre'));

        return response()->json([
            'success' => true,
            'data'    => $disponibles->map(fn ($e) => [
                'id_usuario' => $e->id_usuario,
                'nombre'     => $e->usuario->apellido . ', ' . $e->usuario->nombre,
                'dni'        => $e->usuario->dnis->first()->dni ?? '—',
            ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /materias/{materia}/agregarAlumno
    // -------------------------------------------------------------------------
    public function storeAgregarAlumno(Request $request, int $materia): JsonResponse
    {
        $materiaModelo = Materia::findOrFail($materia);

        $validator = Validator::make($request->all(), [
            'alumnos'   => 'present|array',
            'alumnos.*' => 'integer|exists:estudiantes,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $this->materiaService->sincronizarAlumnos($materiaModelo, $validator->validated()['alumnos']);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alumnos agregados a la materia correctamente.',
        ]);
    }
// -------------------------------------------------------------------------
// GET /materiaPerfil/{materia}/estudiante
// -------------------------------------------------------------------------
public function showEstudiante(int $materia)
{
    $materia = $this->materiaService->getById($materia);
    $profesoresAsignados = $materia->materiaDocentes;

    return view('Estudiante.materiaPerfil', compact('materia', 'profesoresAsignados'));
}

}
