<?php

namespace Database\Seeders;

use App\Models\Llamado;
use Illuminate\Database\Seeder;

class LlamadoSeeder extends Seeder
{
    /**
     * Llamados genéricos (períodos), sin atarlos a un año/ciclo particular.
     * El año de una mesa concreta sale de mesas_examen.id_ciclo.
     */
    public function run(): void
    {
        collect([
            'Febrero/Marzo',
            'Julio/Agosto',
            'Noviembre/Diciembre',
        ])->each(fn ($descripcion) => Llamado::firstOrCreate(['descripcion' => $descripcion]));
    }
}