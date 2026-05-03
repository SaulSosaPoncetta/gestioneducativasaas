<?php

namespace Database\Factories;

use App\Models\ResolucionCarrera;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResolucionCarrera>
 */
class ResolucionCarreraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => 'Resolución ' . $this->faker->unique()->numberBetween(1000, 9999),
        ];
    }
}
