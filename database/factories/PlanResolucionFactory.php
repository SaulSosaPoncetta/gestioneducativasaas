<?php

namespace Database\Factories;

use App\Models\PlanResolucion;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlanEstudio;
use App\Models\ResolucionCarrera;
/**
 * @extends Factory<PlanResolucion>
 */
class PlanResolucionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_estudio_id' => PlanEstudio::inRandomOrder()->first()->id,
            'resolucion_carrera_id' => ResolucionCarrera::inRandomOrder()->first()->id,
        ];
    }
}
