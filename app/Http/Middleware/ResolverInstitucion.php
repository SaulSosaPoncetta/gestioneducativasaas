<?php

namespace App\Http\Middleware;

use App\Models\Institucion;
use App\Support\InstitucionActual;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define la institución del request y verifica que el acceso siga vigente.
 *
 * - Usuario común: su institución (debe estar activa).
 * - Superadmin: la que eligió en el panel (guardada en sesión), o ninguna.
 * - Usuario o institución dados de baja: se cierra la sesión.
 */
class ResolverInstitucion
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return $next($request);
        }

        if (! $usuario->activo) {
            return $this->expulsar($request, 'Tu usuario está deshabilitado.');
        }

        if ($usuario->esSuperAdmin()) {
            $id = $request->session()->get('institucion_id');
            $institucion = $id ? Institucion::with('niveles')->find($id) : null;

            if ($id && ! $institucion) {
                $request->session()->forget('institucion_id');
            }
        } else {
            $institucion = $usuario->institucion()->with('niveles')->first();

            if (! $institucion || ! $institucion->activo) {
                return $this->expulsar($request, 'La institución no está habilitada. Comunicate con MiGestión.');
            }
        }

        InstitucionActual::establecer($institucion);
        View::share('institucionActual', $institucion);

        return $next($request);
    }

    protected function expulsar(Request $request, string $mensaje): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['usuario' => $mensaje]);
    }
}
