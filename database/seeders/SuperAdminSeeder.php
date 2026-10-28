<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder de PRODUCCIÓN: crea (o actualiza) solo el superadmin de MiGestión.
 * No carga instituciones ni datos de demostración.
 *
 * Uso:
 *   php artisan db:seed --class=SuperAdminSeeder --force
 *
 * La contraseña se pide por consola (no se muestra al escribirla) y no queda
 * guardada en el repo ni en el .env. También se puede pasar como variable de
 * entorno: SUPERADMIN_PASSWORD='...' php artisan db:seed --class=SuperAdminSeeder --force
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Auditoria::$activa = false;

        $email = 'admin@migestion.com.ar';

        $password = getenv('SUPERADMIN_PASSWORD') ?: null;

        if (! $password) {
            $password  = $this->command->secret("Contraseña para {$email}");
            $confirmar = $this->command->secret('Repetir contraseña');

            if ($password !== $confirmar) {
                $this->command->error('Las contraseñas no coinciden. No se hizo ningún cambio.');
                return;
            }
        }

        if (! $password || mb_strlen($password) < 8 || $password === 'password') {
            $this->command->error('La contraseña debe tener al menos 8 caracteres y no puede ser "password". No se hizo ningún cambio.');
            return;
        }

        $existia = User::where('email', $email)->exists();

        User::updateOrCreate(
            ['email' => $email],
            [
                'nombre'   => 'Administrador',
                'apellido' => 'MiGestión',
                'password' => $password,
                'rol'      => Rol::SuperAdmin,
                'activo'   => true,
            ]
        );

        $this->command->info($existia
            ? "Superadmin {$email} actualizado (nueva contraseña)."
            : "Superadmin {$email} creado.");
    }
}
