<?php

namespace Database\Factories;

use App\Models\CicloLectivo;
use App\Models\Administrador;
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
        $anio = fake()->numberBetween(2022, 2027);
        return [
            'anio' => $anio,
            'descripcion' => "Ciclo Lectivo {$anio}",
            'fecha_inicio' => "{$anio}-03-01",
            'fecha_fin' => "{$anio}-12-15",
            'inicio_matriculacion' => "{$anio}-02-01",
            'fin_matriculacion' => "{$anio}-02-28",
            'id_usuario_administracion' =>
                Administrador::inRandomOrder()->first()?->id_usuario,
        ];
    }
}
