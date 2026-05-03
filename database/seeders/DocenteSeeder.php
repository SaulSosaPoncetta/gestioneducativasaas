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
        $docentes = Usuario::inRandomOrder()->take(5)->get();

        foreach ($docentes as $do) {
            Docente::create([
                'usuario_id' => $do->id,
            ]);
        }
    }
}