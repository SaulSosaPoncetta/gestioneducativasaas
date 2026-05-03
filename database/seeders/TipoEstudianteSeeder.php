<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoEstudiante;

class TipoEstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoEstudiante::create(['descripcion' => 'Regular']);
        TipoEstudiante::create(['descripcion' => 'Libre']);
    }
}
