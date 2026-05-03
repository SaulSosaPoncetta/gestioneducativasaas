<?php

namespace Database\Factories;

use App\Models\SecretariaEstudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Secretaria;
use App\Models\Estudiante;

/**
 * @extends Factory<SecretariaEstudiante>
 */
class SecretariaEstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'secretaria_id' => Secretaria::inRandomOrder()->first()->id,
            'estudiante_id' => Estudiante::inRandomOrder()->first()->id,
        ];
    }
}
