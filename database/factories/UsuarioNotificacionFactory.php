<?php

namespace Database\Factories;

use App\Models\UsuarioNotificacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;
use App\Models\Notificacion;

/**
 * @extends Factory<UsuarioNotificacion>
 */
class UsuarioNotificacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::inRandomOrder()->first()->id,
            'notificacion_id' => Notificacion::inRandomOrder()->first()->id,
            'leido' => $this->faker->boolean(50), 
        ];
    }
}
