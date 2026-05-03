<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class TipoNotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoNotificacion::create(['descripcion' => 'Email']);    
        TipoNotificacion::create(['descripcion' => 'Sistema']);
        TipoNotificacion::create(['descripcion' => 'Push']);
    }
}
