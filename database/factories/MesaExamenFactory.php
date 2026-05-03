<?php

namespace Database\Factories;

use App\Models\MesaExamen;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Materia;
use App\Models\CicloLectivo;

/**
 * @extends Factory<MesaExamen>
 */
class MesaExamenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fecha' => $this->faker->date(),
            'turno' => $this->faker->randomElement(['Mañana', 'Tarde', 'Noche']),
            'materia_id' => Materia::inRandomOrder()->first()->id,
            'ciclo_lectivo_id' => CicloLectivo::inRandomOrder()->first()->id,
            'aula' => $this->faker->numberBetween(1, 30),
        ];
    }
}
