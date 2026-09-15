<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudio;
use App\Models\ResolucionCarrera;
use App\Services\CarreraService;
use App\Services\MateriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TipoArchivo;

class CarreraController extends Controller
{
    protected CarreraService $carreraService;
    protected MateriaService $materiaService;

    public function __construct(CarreraService $carreraService, MateriaService $materiaService)
    {
        $this->carreraService = $carreraService;
        $this->materiaService = $materiaService;
    }

    public function index(Request $request)
    {
        $filtros   = $request->only(['nombre']);
        $carreras  = $this->carreraService->buscar($filtros);

        return view('Secretaria.carreraSec', compact('carreras', 'filtros'));
    }

    public function create()
    {
        // Catálogos existentes para los selects de "Plan de estudio"
        $planesEstudio = PlanEstudio::orderBy('titulo')->get();
        $resoluciones  = ResolucionCarrera::orderBy('descripcion')->get();

        return view('Secretaria.carreraAgregar', compact('planesEstudio', 'resoluciones'));
    }

    public function edit(int $id)
    {
        $carrera       = $this->carreraService->getById($id);
        $planesEstudio = PlanEstudio::orderBy('titulo')->get();
        $resoluciones  = ResolucionCarrera::orderBy('descripcion')->get();

        return view('Secretaria.carreraEditar', compact('carrera', 'planesEstudio', 'resoluciones'));
    }

    public function show(int $id)
    {
        $carrera = $this->carreraService->getById($id);
        $materiasPorAnio = $this->materiaService->materiasPorCarreraAgrupadas($carrera);

        return view('Secretaria.cursadaSec', compact('carrera', 'materiasPorAnio'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
        'nombre'        => 'required|string|max:150|unique:carreras,nombre',
        'titulo'        => 'required|string|max:150',
        'id_plan'       => 'nullable|integer|exists:planes_estudio,id_plan',
        'id_resolucion' => 'nullable|integer|exists:resoluciones_carrera,id_resolucion',
    ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $carrera = $this->carreraService->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Carrera creada correctamente.',
            'data'    => $carrera,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('carreras', 'nombre')->ignore($id, 'id_carrera'),
            ],
            'titulo'        => 'required|string|max:150',
            'id_plan'       => 'nullable|integer|exists:planes_estudio,id_plan',
            'id_resolucion' => 'nullable|integer|exists:resoluciones_carrera,id_resolucion',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $carrera = $this->carreraService->update($id, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Carrera actualizada correctamente.',
            'data'    => $carrera,
        ]);
    }
   public function planEstudioForm(int $id)
    {
        $carrera = $this->carreraService->getById($id);
        // Ya no hace falta pasar $tiposArchivo como catálogo para elegir,
        // porque ahora se crea uno nuevo en esta pantalla.
        // Si igual querés mostrar los ya creados (ej. para reutilizar), dejalo:
        $tiposArchivo = TipoArchivo::orderBy('descripcion')->get();

        return view('Secretaria.carreraPlanEstudio', compact('carrera', 'tiposArchivo'));
    }

    public function updatePlanEstudio(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre'        => 'required|string|max:150|unique:carreras,nombre',
            'titulo'        => 'required|string|max:150',
            'id_plan'       => 'required|integer|exists:planes_estudio,id_plan',
            'id_resolucion' => 'required|integer|exists:resoluciones_carrera,id_resolucion',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $planResolucion = $this->carreraService->completarPlanEstudio(
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
}