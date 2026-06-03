<?php

namespace Database\Factories;

use App\Models\Dni;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dni>
 */
class DniFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => (string) $this->faker->unique()->numberBetween(10000000, 99999999),
            'id_usuario' => Usuario::inRandomOrder()->first()->id_usuario,
            'tipo_dni' => $this->faker->randomElement([
                'DNI',
                'LC',
                'LE',
                'Pasaporte',
             ]),
        ];
    }
}
