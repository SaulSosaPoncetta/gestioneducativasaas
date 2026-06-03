<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\Usuario;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::inRandomOrder()
            ->take(20)
            ->get();

        foreach ($usuarios as $usuario) {
            Estudiante::create([
                'id_usuario' => $usuario->id_usuario,
            ]);
        }
    }
}
