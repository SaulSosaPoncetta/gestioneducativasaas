<?php

namespace Database\Factories;

use App\Models\NotificacionComentario;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notificacion;
use App\Models\Comentario;

/**
 * @extends Factory<NotificacionComentario>
 */
class NotificacionComentarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notificacion_id' => Notificacion::inRandomOrder()->first()->id,
            'comentario_id' => Comentario::inRandomOrder()->first()->id,
        ];
    }
}
