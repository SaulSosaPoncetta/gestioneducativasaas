<?php

namespace Database\Factories;

use App\Models\CicloLectivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicloLectivo>
 */
class CicloLectivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $anio = $this->faker->unique()->numberBetween(2000, 2050);
        return [
            'anio' => $anio,
            'descripcion' => 'ciclo lectivo '.$anio, 
            'fecha_inicio'=> $anio.'-03-01',
            'fecha_fin' => $anio.'-12-15',           
        ];
    }
}

