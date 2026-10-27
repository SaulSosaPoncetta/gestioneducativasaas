<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legajo docente. Puede tener una cuenta de acceso (rol docente)
 * para entrar al portal "Mis cursos".
 */
class Docente extends Model
{
    use PerteneceAInstitucion;

    public const ESTADOS = [
        'activo'   => 'Activo',
        'licencia' => 'De licencia',
        'baja'     => 'Baja',
    ];

    protected $fillable = [
        'institucion_id', 'user_id', 'apellido', 'nombre', 'documento', 'cuil', 'fecha_nacimiento',
        'telefono', 'email', 'domicilio', 'localidad', 'titulo', 'legajo', 'fecha_ingreso', 'estado', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso'    => 'date',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    /** Docente vinculado al usuario logueado (para el portal). */
    public static function delUsuario(?User $usuario): ?self
    {
        return $usuario ? static::where('user_id', $usuario->id)->first() : null;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 'baja');
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (! filled($texto)) {
            return $query;
        }

        $texto = trim($texto);

        if (preg_match('/^[\d.\s]+$/', $texto)) {
            return $query->where('documento', 'like', '%' . preg_replace('/\D/', '', $texto) . '%');
        }

        foreach (preg_split('/\s+/', $texto) as $palabra) {
            $query->where(fn ($q) => $q->where('apellido', 'like', "%{$palabra}%")->orWhere('nombre', 'like', "%{$palabra}%"));
        }

        return $query;
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function getDocumentoLabelAttribute(): string
    {
        return 'DNI ' . (ctype_digit((string) $this->documento) ? number_format((int) $this->documento, 0, ',', '.') : $this->documento);
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'activo'   => 'success',
            'licencia' => 'warning',
            default    => 'secondary',
        };
    }
}
