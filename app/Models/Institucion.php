<?php

namespace App\Models;

use App\Enums\NivelEducativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre', 'nombre_corto', 'cue', 'cuit', 'tipo_gestion',
        'provincia', 'localidad', 'domicilio', 'telefono', 'email',
        'logo_path', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function niveles(): HasMany
    {
        return $this->hasMany(InstitucionNivel::class)->orderBy('orden');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Niveles activos como enums, ordenados de inicial a universitario. */
    public function nivelesActivos(): Collection
    {
        return $this->niveles->where('activo', true)->map->nivel->values();
    }

    public function ofreceNivel(NivelEducativo $nivel): bool
    {
        return $this->nivelesActivos()->contains($nivel);
    }

    public function getNombreVisibleAttribute(): string
    {
        return $this->nombre_corto ?: $this->nombre;
    }
}
