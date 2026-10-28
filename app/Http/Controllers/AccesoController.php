<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Tutor;
use App\Models\User;
use App\Support\InstitucionActual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Cuentas de acceso a los portales de estudiantes y familias.
 * Usuario: DNI (o email). Contraseña inicial: DNI.
 */
class AccesoController extends Controller
{
    public function estudiante(Estudiante $estudiante): RedirectResponse
    {
        return back()->with('ok', $this->vincular($estudiante, Rol::Estudiante));
    }

    public function tutor(Tutor $tutor): RedirectResponse
    {
        return back()->with('ok', $this->vincular($tutor, Rol::Tutor));
    }

    /** Vuelve la contraseña al DNI (para quien la olvidó). */
    public function restablecer(User $usuario): RedirectResponse
    {
        abort_unless($usuario->institucion_id === InstitucionActual::id()
            && in_array($usuario->rol, [Rol::Estudiante, Rol::Tutor], true) && $usuario->dni, 404);

        $usuario->update(['password' => $usuario->dni, 'activo' => true]);

        return back()->with('ok', "La contraseña de {$usuario->nombre} {$usuario->apellido} volvió a ser su DNI.");
    }

    /** Genera las cuentas que faltan: familias de estudiantes activos o estudiantes de nivel superior. */
    public function masivo(Request $request): RedirectResponse
    {
        $request->validate(['tipo' => ['required', Rule::in(['familias', 'superior'])]]);

        $creados = 0;

        if ($request->tipo === 'familias') {
            Tutor::whereNull('user_id')
                ->whereHas('estudiantes', fn ($q) => $q->where('estado', 'activo'))
                ->get()
                ->each(function (Tutor $t) use (&$creados) {
                    $this->vincular($t, Rol::Tutor);
                    $creados++;
                });
        } else {
            Estudiante::whereNull('user_id')->where('estado', 'activo')
                ->whereHas('inscripciones', fn ($q) => $q->where('estado', Inscripcion::ACTIVA)->whereNull('curso_id'))
                ->get()
                ->each(function (Estudiante $e) use (&$creados) {
                    $this->vincular($e, Rol::Estudiante);
                    $creados++;
                });
        }

        return back()->with('ok', $creados
            ? "Se generaron {$creados} accesos. Ingresan con su DNI y la contraseña inicial es el DNI."
            : 'No había accesos pendientes.');
    }

    /** Vincula un usuario existente con el mismo DNI o crea uno nuevo. */
    protected function vincular(Estudiante|Tutor $persona, Rol $rol): string
    {
        if ($persona->user_id) {
            return "{$persona->nombre_completo} ya tiene acceso.";
        }

        $dni = preg_replace('/\D/', '', (string) $persona->documento);
        $usuario = $dni ? User::deInstitucion()->where('dni', $dni)->first() : null;

        if ($usuario) {
            $persona->update(['user_id' => $usuario->id]);

            return "Se vinculó a {$persona->nombre_completo} con su usuario existente ({$usuario->rol->label()}).";
        }

        $email = $persona->email && ! User::where('email', $persona->email)->exists() ? $persona->email : null;

        $usuario = User::create([
            'institucion_id' => InstitucionActual::id(),
            'nombre'         => $persona->nombre,
            'apellido'       => $persona->apellido,
            'dni'            => $dni ?: null,
            'email'          => $email,
            'password'       => $dni ?: str()->random(12),
            'rol'            => $rol,
            'activo'         => true,
        ]);

        $persona->update(['user_id' => $usuario->id]);

        return "Se creó el acceso de {$persona->nombre_completo}: ingresa con su DNI y la contraseña inicial es el DNI.";
    }
}
