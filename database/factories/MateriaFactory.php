<?php

namespace Database\Factories;

use App\Models\Materia;
use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'nombre' => $this->faker->randomElement([
                'Matemática I',
                'Matemática II',
                'Programación I',
                'Programación II',
                'Base de Datos',
                'Algoritmos',
                'Sistemas Operativos',
                'Redes',
                'Ingeniería de Software',
                'Arquitectura de Computadoras'
            ]),
            'anio' => $this->faker->numberBetween(1, 5),
            'carrera_id' => Carrera::inRandomOrder()->first()->id,
            'correlativa' => $this->faker->word(),
        ];
    }
}
