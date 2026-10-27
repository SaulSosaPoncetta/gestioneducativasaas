<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsistenciaService;
use App\Support\InstitucionActual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * API para GestiónAula.
 *
 * GET  /api/v1/ping         Verifica el token.
 * POST /api/v1/asistencias  Registra la asistencia de una clase.
 *
 * Cuerpo (JSON):
 * {
 *   "docente_dni": "27111333",
 *   "fecha": "2026-10-26",
 *   "espacio": "Matemática",          // nombre o código (opcional si el docente tiene un solo espacio en ese curso)
 *   "referencia": "clase-1234",       // id de la clase en GestiónAula (reenviar con el mismo id actualiza)
 *   "asistencias": [
 *     {"dni": "54001370", "estado": "presente"},
 *     {"dni": "54001507", "estado": "ausente", "observacion": "Avisó la familia"}
 *   ]
 * }
 * Estados: presente | ausente | tarde | retiro
 */
class AsistenciaApiController extends Controller
{
    public function ping(): JsonResponse
    {
        $institucion = InstitucionActual::get();

        return response()->json([
            'ok'          => true,
            'institucion' => ['id' => $institucion->id, 'nombre' => $institucion->nombre],
        ]);
    }

    public function store(Request $request, AsistenciaService $servicio): JsonResponse
    {
        $validador = Validator::make($request->all(), [
            'docente_dni'               => ['required', 'string', 'max:15'],
            'fecha'                     => ['required', 'date_format:Y-m-d'],
            'espacio'                   => ['nullable', 'string', 'max:150'],
            'referencia'                => ['nullable', 'string', 'max:64'],
            'asistencias'               => ['required', 'array', 'min:1', 'max:200'],
            'asistencias.*.dni'         => ['required', 'string', 'max:15'],
            'asistencias.*.estado'      => ['required', Rule::in(['presente', 'ausente', 'tarde', 'retiro'])],
            'asistencias.*.observacion' => ['nullable', 'string', 'max:200'],
        ]);

        if ($validador->fails()) {
            return $this->errores($validador->errors()->toArray());
        }

        try {
            return response()->json($servicio->recibirClase($validador->validated()));
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
