<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Docente;
use App\Models\Usuario;

class DocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $usuarios = Usuario::inRandomOrder()
            ->take(10)
            ->get();

        foreach ($usuarios as $usuario) {
            Docente::create([
                'id_usuario' => $usuario->id_usuario,
                'nro_legajo' => fake()->unique()->numerify('LEG####'),
            ]);
        } 
    }
}
