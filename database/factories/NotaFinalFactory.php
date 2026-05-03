<?php

namespace Database\Factories;

use App\Models\NotaFinal;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Estudiante;
use App\Models\Materia;

/**
 * @extends Factory<NotaFinal>
 */
class NotaFinalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nota' => $this->faker->numberBetween(1, 10),
            'fecha' => $this->faker->date(),
            'estudiante_id' => Estudiante::inRandomOrder()->first()->id,
            'materia_id' => Materia::inRandomOrder()->first()->id,
        ];
    }
}
