<?php

namespace Database\Factories;

use App\Models\TramiteDocente;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;
use App\Models\Tramite;

/**
 * @extends Factory<TramiteDocente>
 */
class TramiteDocenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::inRandomOrder()->first()->id,
            'tramite_id' => Tramite::inRandomOrder()->first()->id,
        ];
    }
}
