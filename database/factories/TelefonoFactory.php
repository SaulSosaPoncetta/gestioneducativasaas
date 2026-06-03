<?php

namespace Database\Factories;

use App\Models\Usuario;
use App\Models\Telefono;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Telefono>
 */
class TelefonoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telefono' => $this->faker->unique()->numerify('11########'),
            'id_usuario' => Usuario::inRandomOrder()->first()->id_usuario,
        ];
    }
}
