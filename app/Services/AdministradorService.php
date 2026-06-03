<?php

namespace App\Services;

use App\Models\Administrador;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class AdministradorService
{
    /**
     * Obtener todos los administradores con sus relaciones.
     */
    public function getAll(): Collection
    {
        return Administrador::with(['usuario.rol', 'usuario.localidad'])
            ->get();
    }

    /**
     * Obtener un administrador por su id_usuario.
     */
    public function getById(int $id): Administrador
    {
        return Administrador::with(['usuario.rol', 'usuario.localidad'])
            ->where('id_usuario', $id)
            ->firstOrFail();
    }

    /**
     * Crear un nuevo administrador junto con su usuario.
     *
     * @param array $data {
     *   nombre, apellido, nombre_usuario, email, password,
     *   genero, id_localidad
     * }
     */
    public function create(array $data): Administrador
    {
        return DB::transaction(function () use ($data) {

            // Obtener el id_rol correspondiente a "Administración"
            $rol = Rol::where('descripcion', 'Administracion')->firstOrFail();

            $usuario = Usuario::create([
                'nombre'          => $data['nombre'],
                'apellido'        => $data['apellido'],
                'nombre_usuario'  => $data['nombre_usuario'],
                'email'           => $data['email'],
                'password_hash'   => Hash::make($data['password']),
                'id_rol'          => $rol->id_rol,
                'genero'          => $data['genero'],
                'id_localidad'    => $data['id_localidad'],
            ]);

            $administrador = Administrador::create([
                'id_usuario' => $usuario->id_usuario,
            ]);

            return $administrador->load(['usuario.rol', 'usuario.localidad']);
        });
    }

    /**
     * Actualizar los datos del usuario asociado al administrador.
     *
     * @param array $data Campos a actualizar (todos opcionales)
     */
    public function update(int $id, array $data): Administrador
    {
        return DB::transaction(function () use ($id, $data) {

            $administrador = Administrador::where('id_usuario', $id)->firstOrFail();
            $usuario = $administrador->usuario;

            $camposUsuario = array_filter([
                'nombre'         => $data['nombre']         ?? null,
                'apellido'       => $data['apellido']       ?? null,
                'nombre_usuario' => $data['nombre_usuario'] ?? null,
                'email'          => $data['email']          ?? null,
                'genero'         => $data['genero']         ?? null,
                'id_localidad'   => $data['id_localidad']   ?? null,
            ], fn($v) => !is_null($v));

            if (!empty($data['password'])) {
                $camposUsuario['password_hash'] = Hash::make($data['password']);
            }

            if (!empty($camposUsuario)) {
                $usuario->update($camposUsuario);
            }

            return $administrador->load(['usuario.rol', 'usuario.localidad']);
        });
    }

    /**
     * Eliminar un administrador y su usuario asociado.
     */
    public function delete(int $id): void
{
    DB::transaction(function () use ($id) {
        $administrador = Administrador::where('id_usuario', $id)->firstOrFail();

        $administrador->delete();           // soft delete en administradores
        $administrador->usuario->delete();  // soft delete en usuarios
    });
}
}