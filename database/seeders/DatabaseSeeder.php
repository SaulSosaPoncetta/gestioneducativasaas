<?php

namespace Database\Seeders;

use App\Enums\NivelEducativo;
use App\Enums\Rol;
use App\Models\Institucion;
use App\Models\InstitucionNivel;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Datos iniciales:
 * - Superadmin de MiGestión.
 * - Dos instituciones de demo: un colegio (inicial, primaria, secundaria y
 *   técnica) y un instituto superior (terciario y universitario).
 *
 * Contraseña de todos los usuarios de demo: password
 * Cambiala en producción.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@migestion.com.ar'],
            [
                'nombre'   => 'Administrador',
                'apellido' => 'MiGestión',
                'password' => 'password',
                'rol'      => Rol::SuperAdmin,
                'activo'   => true,
            ]
        );

        $this->institucion(
            datos: [
                'nombre'       => 'Colegio San Martín',
                'nombre_corto' => 'San Martín',
                'cue'          => '060000100',
                'tipo_gestion' => 'privada',
                'localidad'    => 'La Plata',
                'domicilio'    => 'Calle 7 N° 1234',
                'email'        => 'contacto@colegiosanmartin.edu.ar',
            ],
            niveles: [
                NivelEducativo::Inicial,
                NivelEducativo::Primario,
                NivelEducativo::Secundario,
                NivelEducativo::SecundarioTecnico,
            ],
            admin: ['email' => 'admin@colegiosanmartin.edu.ar', 'dni' => '20111222']
        );

        $this->institucion(
            datos: [
                'nombre'       => 'Instituto Superior Belgrano',
                'nombre_corto' => 'ISB',
                'cue'          => '060000200',
                'tipo_gestion' => 'privada',
                'localidad'    => 'Mar del Plata',
                'domicilio'    => 'Av. Colón 2500',
                'email'        => 'secretaria@isbelgrano.edu.ar',
            ],
            niveles: [
                NivelEducativo::Terciario,
                NivelEducativo::Universitario,
            ],
            admin: ['email' => 'admin@isbelgrano.edu.ar', 'dni' => '20333444']
        );
    }

    protected function institucion(array $datos, array $niveles, array $admin): void
    {
        $institucion = Institucion::updateOrCreate(['nombre' => $datos['nombre']], $datos + ['activo' => true]);

        foreach ($niveles as $nivel) {
            InstitucionNivel::sinInstitucion()->updateOrCreate(
                ['institucion_id' => $institucion->id, 'nivel' => $nivel->value],
                ['activo' => true]
            );
        }

        User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'institucion_id' => $institucion->id,
                'nombre'         => 'Administración',
                'apellido'       => $institucion->nombre_corto,
                'dni'            => $admin['dni'],
                'password'       => 'password',
                'rol'            => Rol::Admin,
                'activo'         => true,
            ]
        );
    }
}
