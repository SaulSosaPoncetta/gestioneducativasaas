<?php

namespace Database\Factories;

use App\Models\Notificacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TipoNotificacion;

/**
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(4),
            'contenido' => $this->faker->paragraph(),
            'fecha_envio' => $this->faker->date(),
            'tipo_notificacion_id' => TipoNotificacion::inRandomOrder()->first()->id,
        ];
    }
}
