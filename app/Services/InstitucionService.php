<?php

namespace App\Services;

use App\Enums\NivelEducativo;
use App\Enums\Rol;
use App\Models\Institucion;
use App\Models\InstitucionNivel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Alta y modificación de instituciones con sus niveles y logo.
 * Lo usan el superadmin (InstitucionController) y el administrador
 * de la institución (MiInstitucionController).
 */
class InstitucionService
{
    /** Alta de institución + niveles + su primer usuario administrador. */
    public function crear(array $datos, array $niveles, array $admin, ?UploadedFile $logo = null): Institucion
    {
        return DB::transaction(function () use ($datos, $niveles, $admin, $logo) {
            $institucion = Institucion::create($datos);

            $this->sincronizarNiveles($institucion, $niveles);
            $this->guardarLogo($institucion, $logo);

            User::create([
                'institucion_id' => $institucion->id,
                'nombre'         => $admin['nombre'],
                'apellido'       => $admin['apellido'],
                'email'          => $admin['email'],
                'dni'            => $admin['dni'] ?? null,
                'password'       => $admin['password'],
                'rol'            => Rol::Admin,
                'activo'         => true,
            ]);

            return $institucion;
        });
    }

    public function actualizar(
        Institucion $institucion,
        array $datos,
        array $niveles,
        ?UploadedFile $logo = null,
        bool $quitarLogo = false
    ): Institucion {
        return DB::transaction(function () use ($institucion, $datos, $niveles, $logo, $quitarLogo) {
            $institucion->update($datos);

            $this->sincronizarNiveles($institucion, $niveles);

            if ($quitarLogo && ! $logo) {
                $this->borrarLogo($institucion);
            }

            $this->guardarLogo($institucion, $logo);

            return $institucion->refresh();
        });
    }

    /**
     * Crea o actualiza cada nivel. Un nivel destildado queda inactivo,
     * no se borra: más adelante va a tener cursos, alumnos e historial.
     *
     * @param array<string, array{activo?:bool, denominacion?:?string, cue_anexo?:?string, duracion?:?int}> $niveles
     */
    public function sincronizarNiveles(Institucion $institucion, array $niveles): void
    {
        foreach (NivelEducativo::cases() as $nivel) {
            $datos  = $niveles[$nivel->value] ?? [];
            $activo = (bool) ($datos['activo'] ?? false);

            $existente = InstitucionNivel::sinInstitucion()
                ->where('institucion_id', $institucion->id)
                ->where('nivel', $nivel->value)
                ->first();

            if (! $existente && ! $activo) {
                continue;
            }

            $registro = $existente ?? new InstitucionNivel([
                'institucion_id' => $institucion->id,
                'nivel'          => $nivel,
            ]);

            // Solo se pisan los campos que vinieron en el formulario
            // (un campo vaciado llega como null y se guarda vacío).
            foreach (['denominacion', 'cue_anexo', 'duracion'] as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $registro->{$campo} = $datos[$campo];
                }
            }

            $registro->activo = $activo;
            $registro->save();
        }
    }

    protected function guardarLogo(Institucion $institucion, ?UploadedFile $logo): void
    {
        if (! $logo) {
            return;
        }

        $this->borrarLogo($institucion);

        $institucion->update([
            'logo_path' => $logo->store("instituciones/{$institucion->id}", 'public'),
        ]);
    }

    protected function borrarLogo(Institucion $institucion): void
    {
        if ($institucion->logo_path) {
            Storage::disk('public')->delete($institucion->logo_path);
            $institucion->update(['logo_path' => null]);
        }
    }
}
