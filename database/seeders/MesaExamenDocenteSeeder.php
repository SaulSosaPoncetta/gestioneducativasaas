<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MesaExamen;
use App\Models\Docente;
use App\Models\MesaExamenDocente;

class MesaExamenDocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mesas = MesaExamen::all();
        $docentes = Docente::all();

        foreach ($mesas as $mesa) {

            $docente = $docentes->random();

            MesaExamenDocente::create([
                'id_mesa' => $mesa->id_mesa,
                'id_docente' => $docente->id_usuario,
            ]);
        }
    }
}
