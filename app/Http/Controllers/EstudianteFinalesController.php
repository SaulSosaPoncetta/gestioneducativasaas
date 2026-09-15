<?php

namespace App\Http\Controllers;

use App\Services\EstudianteFinalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstudianteFinalesController extends Controller
{
    public function __construct(protected EstudianteFinalesService $service)
    {
    }

    /**
     * GET /finales  (name: finales)
     * Pantalla "Finales" del alumno: una tabla por carrera (con sus 4
     * pestañas Inscripto/Aprobado/No aprobado/Bajas), igual que el Blade
     * original que ya arma un <table data-carrera="X" data-tabla="Y"> por
     * combinación carrera+pestaña.
     */
    public function index(Request $request): View
    {
        $idUsuario = auth()->user()->id_usuario;

        $carreras = $this->service->carrerasDelEstudiante($idUsuario);

        $finalesPorCarrera = $carreras->mapWithKeys(function ($carrera) use ($idUsuario) {
            return [$carrera->id_carrera => $this->service->finalesDelEstudiante($idUsuario, $carrera->id_carrera)];
        });

        return view('Estudiante.finales', compact('carreras', 'finalesPorCarrera'));
    }

    /**
     * GET /finalesInscripcion  (name: finalesInscripcion)
     * Pantalla "Inscripción a finales": materias de la carrera ACTIVA del
     * alumno, agrupadas por año, con las fechas disponibles (o bloqueada
     * con el motivo).
     */
    public function inscripcionView(): View
    {
        $idUsuario = auth()->user()->id_usuario;

        $carreraActiva = $this->service->carreraActiva($idUsuario);

        abort_if(!$carreraActiva, 404, 'No se encontró una carrera activa para este estudiante.');

        $aniosCarrera = $this->service->materiasParaInscripcion($idUsuario, $carreraActiva->id_carrera);

        return view('Estudiante.finalesInscripcion', compact('aniosCarrera'));
    }

    /**
     * POST /finalesEst/baja  (name: finalesEst.baja)
     * El alumno se da de baja de una mesa a la que está inscripto y todavía
     * no rindió. Revalida todo server-side.
     */
    public function baja(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_mesa' => ['required', 'integer', 'exists:mesas_examen,id_mesa'],
        ]);

        $idUsuario = auth()->user()->id_usuario;

        $ok = $this->service->darDeBaja($idUsuario, $data['id_mesa']);

        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una inscripción activa para esa mesa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Te diste de baja de la mesa correctamente.',
        ]);
    }
}