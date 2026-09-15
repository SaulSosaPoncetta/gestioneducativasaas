<?php

namespace App\Http\Controllers;

use App\Services\PlanEstudioService;
use App\Models\Carrera;
use App\Models\PlanResolucion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlanEstudioController extends Controller
{
    protected PlanEstudioService $planEstudioService;

    public function __construct(PlanEstudioService $planEstudioService)
    {
        $this->planEstudioService = $planEstudioService;
    }

    public function index(Request $request)
    {
        $filtro = $request->get('nombre');

        $planResoluciones = $this->planEstudioService->buscarPlanResoluciones($filtro);

        return view('Secretaria.planDeEstudio', compact('planResoluciones'));
    }
    public function espacios(Request $request, Carrera $carrera)
    {
        $filtro = $request->get('nombre');
        $materias = $this->planEstudioService->buscarEspaciosPorCarrera($carrera, $filtro);
        $planResolucion = PlanResolucion::where('id_carrera', $carrera->id_carrera)->first();
        return view('Secretaria.planEspacios', compact('carrera', 'materias','planResolucion'));
    }

    public function create()
    {
        $carreraSinAsignar = Carrera::sinAsignar();
        $carreras = Carrera::where('id_carrera', '!=', $carreraSinAsignar->id_carrera)
            ->orderBy('nombre')
            ->get();

        return view('Secretaria.planEstudioAgregar', compact('carreras', 'carreraSinAsignar'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo'                  => 'required|string|max:150',
            'plan_descripcion'        => 'nullable|string|max:255',
            'resolucion_descripcion'  => 'required|string|max:150',
            'id_carrera'              => 'nullable|integer|exists:carreras,id_carrera',
            'total_horas'             => 'required|integer|min:1',
            'fecha'                   => 'required|date',
            'archivo_descripcion'     => 'required|string|max:150',
            'archivo'                 => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $planResolucion = $this->planEstudioService->crearPlanCompleto(
            $validator->safe()->except('archivo'),
            $request->file('archivo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Plan de estudio creado y asignado correctamente.',
            'data'    => $planResolucion,
        ], 201);
    }

    public function edit(int $id)
{
    $planResolucion = $this->planEstudioService->buscarPorId($id);
    $carreras       = Carrera::orderBy('nombre')->get();

    return view('Secretaria.planEstudioModificar', compact('planResolucion', 'carreras'));
}

public function update(Request $request, int $id): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'titulo'                  => 'required|string|max:150',
        'plan_descripcion'        => 'nullable|string|max:255',
        'resolucion_descripcion'  => 'required|string|max:150',
        'id_carrera'              => 'nullable|integer|exists:carreras,id_carrera',
        'total_horas'             => 'required|integer|min:1',
        'fecha'                   => 'required|date',
        'archivo_descripcion'     => 'required|string|max:150',
        'archivo'                 => 'nullable|file|mimes:pdf|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $planResolucion = $this->planEstudioService->actualizarPlanCompleto(
        $id,
        $validator->safe()->except('archivo'),
        $request->file('archivo')
    );

    return response()->json([
        'success' => true,
        'message' => 'Plan de estudio actualizado correctamente.',
        'data'    => $planResolucion,
    ]);
}

public function alumnos(Request $request, Carrera $carrera)
{
    $filtroMateria = $request->get('materia');
    $filtroAnio    = $request->get('anio');

    $planResolucion = PlanResolucion::where('id_carrera', $carrera->id_carrera)->first();
    $materias = $this->planEstudioService->buscarEspaciosPorCarrera($carrera);
    $alumnos  = $this->planEstudioService->buscarAlumnosPorCarrera($carrera, $filtroMateria, $filtroAnio);

    return view('Secretaria.planAlumnos', compact('carrera', 'planResolucion', 'materias', 'alumnos'));
}
public function show(int $id)
{
    $planResolucion = $this->planEstudioService->buscarPorId($id);

    return view('Estudiante.planEstudioPerfil', compact('planResolucion'));
}
}
