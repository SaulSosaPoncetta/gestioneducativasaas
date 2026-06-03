<?php

namespace Database\Factories;

use App\Models\MesaExamen;
use App\Models\TipoMesaExamen;
use App\Models\Materia;
use App\Models\Administrador;
use App\Models\CicloLectivo;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'fecha' => fake()->dateTimeBetween('now', '+1 year'),

            'id_tipo_mesa' =>
                TipoMesaExamen::inRandomOrder()->first()->id_tipo_mesa,

            'habilitado' => fake()->boolean(85),

            'id_materia' =>
                Materia::inRandomOrder()->first()->id_materia,

            'aula' => fake()->randomElement([
                'Aula 1',
                'Aula 2',
                'Aula 3',
                'Laboratorio 1',
                'Laboratorio 2',
                'SUM'
            ]),

            'id_usuario_administracion' =>
                Administrador::inRandomOrder()->first()?->id_usuario,

            'id_ciclo' =>
                CicloLectivo::inRandomOrder()->first()->id_ciclo,
        ];
    }
}
