<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Secretaria;
use App\Models\Usuario;

class SecretariaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secretarias = Usuario::inRandomOrder()->take(5)->get();

        foreach ($secretarias as $se) {
            Secretaria::create([
                'usuario_id' => $se->id,
            ]);
        }
    }
}
