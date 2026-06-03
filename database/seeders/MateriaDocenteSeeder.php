<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MateriaDocente;
use App\Models\Materia;
use App\Models\Docente;

class MateriaDocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $materias = Materia::all();
        $docentes = Docente::all();

        foreach ($materias as $materia) {

            $docente = $docentes->random();

            MateriaDocente::create([
                'id_materia' => $materia->id_materia,

                'id_usuario_docente' => $docente->id_usuario,

                'id_usuario' => $docente->id_usuario,

                'situacion_revista' => fake()->randomElement([
                    'Titular',
                    'Adjunto',
                    'JTP',
                    'Auxiliar'
                ]),
            ]);
        }
    }
}
