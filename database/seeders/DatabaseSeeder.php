<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


// Este archivo centraliza la ejecución de todos los seeders.
// Al correr "php artisan db:seed", Laravel ejecuta este método
// y llama a cada seeder definido en el array.

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([ 
            RolSeeder::class,
            TipoEstudianteSeeder::class,
            TipoNotificacionSeeder::class,
            CicloLectivoSeeder::class,
            CarreraSeeder::class,
            UsuarioSeeder::class,
            AdminSeeder::class,
            SecretariaSeeder::class,
            DocenteSeeder::class,
            EstudianteSeeder::class,
            MateriaSeeder::class,
            InscripcionSeeder::class,
            NotaCursadaSeeder::class,
            MesaExamenSeeder::class,
            ResolucionCarreraSeeder::class,
            PlanEstudioSeeder::class,
            PlanResolucionSeeder::class,
            SecretariaDocenteSeeder::class,
            NotificacionSeeder::class,
            UsuarioNotificacionSeeder::class,
            ComentarioSeeder::class,
            NotificacionComentarioSeeder::class,
            TramiteSeeder::class,
            TramiteSecretariaSeeder::class,
            TramiteDocenteSeeder::class,
            TramiteEstudianteSeeder::class,
            LogSeeder::class,
        ]);
    }
}
