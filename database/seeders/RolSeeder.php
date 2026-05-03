<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rol::create(['descripcion' => 'Administrador']);
        Rol::create(['descripcion' => 'Secretaria']);
        Rol::create(['descripcion' => 'Docente']);
        Rol::create(['descripcion' => 'Estudiante']);
    }
}
