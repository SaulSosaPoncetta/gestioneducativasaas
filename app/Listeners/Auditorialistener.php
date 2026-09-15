<?php

namespace App\Listeners;

use App\Models\Auditoria;
use App\Models\CategoriaAuditoria;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class AuditoriaListener
{
    /**
     * Registra un evento de login en la tabla auditorias.
     */
    public function handleLogin(Login $event): void
    {
        $this->registrar(
            usuario: $event->user,
            categoria: 'Login',
            accion: 'login',
            descripcion: 'Inicio de sesión desde IP ' . Request::ip()
        );
    }

    /**
     * Registra un evento de logout en la tabla auditorias.
     */
    public function handleLogout(Logout $event): void
    {
        // En logout, $event->user puede venir null si la sesión ya se invalidó.
        if (!$event->user) {
            return;
        }

        $this->registrar(
            usuario: $event->user,
            categoria: 'Logout',
            accion: 'logout',
            descripcion: 'Cierre de sesión desde IP ' . Request::ip()
        );
    }

    /**
     * Crea el registro de auditoría. Si la categoría no existe, la crea
     * automáticamente para no romper el flujo por falta de datos maestros.
     */
    protected function registrar($usuario, string $categoria, string $accion, string $descripcion): void
    {
        if (!$usuario) {
            return;
        }

        $idCategoria = CategoriaAuditoria::firstOrCreate(
            ['descripcion_categoria' => $categoria]
        )->id_categoria_auditoria;

        Auditoria::create([
            'id_categoria_auditoria' => $idCategoria,
            'id_usuario'             => $usuario->id_usuario ?? $usuario->id ?? null,
            'nombre_tabla'           => 'usuarios',
            'id_registro'            => $usuario->id_usuario ?? $usuario->id ?? null,
            'accion'                 => $accion,
            'descripcion'            => $descripcion,
            'fecha_hora'             => now(),
        ]);
    }
}