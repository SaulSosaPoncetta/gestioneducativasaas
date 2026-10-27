<?php

use App\Http\Controllers\Api\AsistenciaApiController;
use App\Http\Controllers\Api\CalificacionApiController;
use App\Http\Middleware\AutenticarApiInstitucion;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de integración (prefijo /api)
|--------------------------------------------------------------------------
| Autenticación: Authorization: Bearer <token de la institución>.
*/

Route::prefix('v1')
    ->middleware([AutenticarApiInstitucion::class, 'throttle:120,1'])
    ->group(function () {
        Route::get('/ping', [AsistenciaApiController::class, 'ping']);
        Route::post('/asistencias', [AsistenciaApiController::class, 'store']);
        Route::post('/calificaciones', [CalificacionApiController::class, 'store']);
    });
