<?php

namespace App\Models;

use App\Enums\Rol;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario con acceso al sistema.
 *
 * No usa el trait PerteneceAInstitucion a propósito: el login busca usuarios
 * antes de saber la institución, y el superadmin no pertenece a ninguna.
 * Para listar usuarios de la institución actual usar el scope deInstitucion().
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'institucion_id', 'nombre', 'apellido', 'dni', 'email',
        'password', 'rol', 'activo', 'ultimo_acceso_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'rol'              => Rol::class,
            'activo'           => 'boolean',
            'ultimo_acceso_at' => 'datetime',
            'password'         => 'hashed',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function scopeDeInstitucion(Builder $query, ?int $institucionId = null): Builder
    {
        return $query->where('institucion_id', $institucionId ?? \App\Support\InstitucionActual::id());
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === Rol::SuperAdmin;
    }

    /** @param Rol|string ...$roles */
    public function tieneRol(...$roles): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        foreach ($roles as $rol) {
            $valor = $rol instanceof Rol ? $rol->value : $rol;
            if ($this->rol->value === $valor) {
                return true;
            }
        }

        return false;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido . ', ' . $this->nombre, ', ');
    }

    public function getInicialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->nombre, 0, 1) . mb_substr($this->apellido, 0, 1));
    }
}
