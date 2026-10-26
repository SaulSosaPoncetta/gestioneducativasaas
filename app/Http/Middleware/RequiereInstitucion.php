<?php

namespace App\Http\Middleware;

use App\Support\InstitucionActual;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Para módulos que trabajan sobre una institución.
 * Si el superadmin todavía no entró a ninguna, lo manda a elegir.
 */
class RequiereInstitucion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstitucionActual::existe()) {
            return redirect()->route('dashboard')
                ->with('error', 'Primero elegí una institución para trabajar.');
        }

        return $next($request);
    }
}
