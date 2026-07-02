<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\TipoDni;
use App\Models\Dni;


class DniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Usuario::all() as $usuario) {
            Dni::create([
                'dni' => fake()->unique()->numberBetween(10000000, 99999999),
                'cuil' => fake()->numerify('20-########-#'),
                'id_usuario' => $usuario->id_usuario,
                'id_tipo_dni' => TipoDni::inRandomOrder()->first()->id_tipo_dni
            ]);
        }
    }
}
