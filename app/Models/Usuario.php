<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'nombre_usuario',
        'email',
        'password_hash',
        'id_rol',
        'genero',
        'id_localidad',
    ];

    protected $hidden = [
        'password_hash',
    ];
    // -------------------------------------------------------------------------
    // Relaciones existentes
    // -------------------------------------------------------------------------

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'id_localidad', 'id_localidad');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }

    // -------------------------------------------------------------------------
    // Relaciones nuevas (necesarias para alumnosSec y alumnoPerfil)
    // -------------------------------------------------------------------------

    /**
     * DNIs del usuario (tabla dnis, clave foránea id_usuario)
     */
    public function dnis()
    {
        return $this->hasMany(Dni::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Teléfonos del usuario (tabla telefonos, clave foránea id_usuario)
     */
    public function telefonos()
    {
        return $this->hasMany(Telefono::class, 'id_usuario', 'id_usuario');
    }
}
