<?php

namespace Database\Factories;

use App\Models\Tramite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tramite>
 */
class TramiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo' => $this->faker->randomElement([
                'Inscripción',
                'Equivalencia',
                'Certificado',
                'Baja',
            ]),
            'estado' => $this->faker->randomElement([
                'Pendiente',
                'Aprobado',
                'Rechazado',
            ]),
            'fecha_solicitud' => $this->faker->date(),
        ];
    }
}
