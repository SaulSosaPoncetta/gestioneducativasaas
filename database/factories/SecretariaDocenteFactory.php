<?php

namespace Database\Factories;

use App\Models\SecretariaDocente;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Secretaria;
use App\Models\Docente;

/**
 * @extends Factory<SecretariaDocente>
 */
class SecretariaDocenteFactory extends Factory
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
            'docente_id' => Docente::inRandomOrder()->first()->id,
        ];
    }
}
