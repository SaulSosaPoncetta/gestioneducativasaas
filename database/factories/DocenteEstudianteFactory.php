<?php

namespace Database\Factories;

use App\Models\DocenteEstudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Docente;
use App\Models\Estudiante;

/**
 * @extends Factory<DocenteEstudiante>
 */
class DocenteEstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'docente_id' => Docente::inRandomOrder()->first()->id,
            'estudiante_id' => Estudiante::inRandomOrder()->first()->id,
        ];
    }
}
