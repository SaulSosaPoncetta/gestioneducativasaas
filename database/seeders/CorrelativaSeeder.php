<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Correlativa;
use App\Models\Materia;

class CorrelativaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materias = Materia::all();

        foreach ($materias as $materia) {

            $correlativa = $materias
                ->where('id_materia', '!=', $materia->id_materia)
                ->random();

            Correlativa::create([
                'id_materia' => $materia->id_materia,
                'id_materia_correlativa' => $correlativa->id_materia,

                'correlativa_cursada' => fake()->boolean(),

                'correlativa_final' => fake()->boolean(),

                'descripcion' => fake()->sentence(),
            ]);
        }
    }
}
