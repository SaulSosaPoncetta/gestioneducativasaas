<?php

namespace App\Http\Middleware;

use App\Models\Institucion;
use App\Support\InstitucionActual;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API de integración: autentica con "Authorization: Bearer <token>".
 * El token identifica a la institución (se genera en "Datos de la institución").
 */
class AutenticarApiInstitucion
{
    public function handle(Request $request, Closure $next): Response
    {
        $institucion = Institucion::porApiToken($request->bearerToken());

        if (! $institucion) {
            return response()->json(['ok' => false, 'mensaje' => 'Token inválido o institución deshabilitada.'], 401);
        }

        InstitucionActual::establecer($institucion);

        return $next($request);
    }
}
