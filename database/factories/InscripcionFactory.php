<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Estudiante;
use App\Models\Materia;

/**
 * @extends Factory<Inscripcion>
 */
class InscripcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::inRandomOrder()->first()->id,
            'materia_id' => Materia::inRandomOrder()->first()->id,
            'fecha_inscripcion' => $this->faker->date(),
        ];
    }
}
