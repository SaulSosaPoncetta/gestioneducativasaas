<?php

namespace Database\Factories;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TipoMateria;
use App\Models\Administrador;
use App\Models\Carrera;
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
            'id_tipo_materia' => TipoMateria::inRandomOrder()->first()->id_tipo_materia,

            'nota_promocionable' => 7,

            'nota_aprobada' => 4,

            'nombre' => fake()->unique()->randomElement([
                'Programación I',
                'Programación II',
                'Base de Datos',
                'Sistemas Operativos',
                'Redes',
                'Ingeniería de Software',
                'Análisis de Sistemas',
                'Matemática',
                'Estadística',
                'Arquitectura de Computadoras',
                'Seguridad Informática',
                'Laboratorio de Programación',
                'Diseño Web',
                'Algoritmos y Estructuras de Datos',
                'Investigación Operativa'
            ]),

            'anio' => fake()->numberBetween(1, 3),

            'id_usuario_administrador' =>
                Administrador::inRandomOrder()->first()?->id_usuario,

            'id_carrera' =>
                Carrera::inRandomOrder()->first()->id_carrera,
        ];
    }
}
