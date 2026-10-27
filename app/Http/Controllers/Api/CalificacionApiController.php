<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * POST /api/v1/calificaciones (GestiónAula)
 *
 * {
 *   "docente_dni": "27111333",
 *   "espacio": "Matemática",            // nombre o código (opcional si tiene un solo espacio en el curso)
 *   "periodo": 1,                        // número de orden o nombre ("1° Trimestre")
 *   "calificaciones": [
 *     {"dni": "54001370", "nota": "8"},
 *     {"dni": "54001507", "nota": "6,5", "observacion": "Debe reforzar fracciones"},
 *     {"dni": "57000000", "informe": "Texto del informe"}   // nivel inicial
 *   ]
 * }
 * Notas: numéricas 1 a 10 (admite un decimal) o conceptuales E, MB, B, R, I según la escala del nivel.
 */
class CalificacionApiController extends Controller
{
    public function store(Request $request, CalificacionService $servicio): JsonResponse
    {
        $validador = Validator::make($request->all(), [
            'docente_dni'                  => ['required', 'string', 'max:15'],
            'espacio'                      => ['nullable', 'string', 'max:150'],
            'periodo'                      => ['required'],
            'calificaciones'               => ['required', 'array', 'min:1', 'max:200'],
            'calificaciones.*.dni'         => ['required', 'string', 'max:15'],
            'calificaciones.*.nota'        => ['nullable'],
            'calificaciones.*.informe'     => ['nullable', 'string', 'max:5000'],
            'calificaciones.*.observacion' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validador->fails()) {
            return $this->errores($validador->errors()->toArray());
        }

        try {
            return response()->json($servicio->recibir($validador->validated()));
        } catch (ValidationException $e) {
            return $this->errores($e->errors());
        }
    }

    protected function errores(array $errores): JsonResponse
    {
        return response()->json([
            'ok'      => false,
            'mensaje' => collect($errores)->flatten()->first(),
            'errores' => $errores,
        ], 422);
    }
}
