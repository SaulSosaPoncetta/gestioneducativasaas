<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Usuario;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        #toma 5 usuarios randoms de la tabla usuarios y los asigna a la administrador
        $admins = Usuario::inRandomOrder()->take(5)->get();

        foreach ($admins as $ad) {
            Admin::create([
                'usuario_id' => $ad->id,
            ]);
        }
    }
}
