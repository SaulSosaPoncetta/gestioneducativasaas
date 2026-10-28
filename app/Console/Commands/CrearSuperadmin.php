<?php

namespace App\Console\Commands;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Crea (o actualiza la clave de) un superadministrador de MiGestión.
 * Para producción, en lugar del seeder de demostración:
 *     php artisan sgi:superadmin
 */
class CrearSuperadmin extends Command
{
    protected $signature = 'sgi:superadmin';

    protected $description = 'Crea un superadministrador de MiGestión o le cambia la contraseña';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->ask('Email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('El email no es válido.');

            return self::FAILURE;
        }

        $existente = User::where('email', $email)->first();

        if ($existente && ! $existente->esSuperAdmin()) {
            $this->error('Ese email ya pertenece a un usuario de una institución.');

            return self::FAILURE;
        }

        $nombre   = $existente?->nombre ?? $this->ask('Nombre', 'Administrador');
        $apellido = $existente?->apellido ?? $this->ask('Apellido', 'MiGestión');
        $clave    = $this->secret('Contraseña (mínimo 10 caracteres)');

        if (mb_strlen((string) $clave) < 10) {
            $this->error('La contraseña tiene que tener al menos 10 caracteres.');

            return self::FAILURE;
        }

        if ($clave !== $this->secret('Repetir contraseña')) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], [
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'password'       => $clave,
            'rol'            => Rol::SuperAdmin,
            'institucion_id' => null,
            'activo'         => true,
        ]);

        $this->info($existente ? "Contraseña actualizada para {$email}." : "Superadministrador {$email} creado.");

        return self::SUCCESS;
    }
}
