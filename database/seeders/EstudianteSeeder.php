<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Carrera;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiantes = Usuario::inRandomOrder()->take(10)->get();

        foreach ($estudiantes as $es) {
            Estudiante::create([
                'usuario_id' => $es->id,
                'carrera_id' => Carrera::inRandomOrder()->first()->id,
            ]);
        }
    }
}