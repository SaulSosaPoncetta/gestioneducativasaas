<?php

namespace App\Models;

use App\Enums\NivelEducativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Institucion extends Model
{
    protected $table = 'instituciones';

    public const PROVINCIAS = [
        'Buenos Aires', 'Ciudad Autónoma de Buenos Aires', 'Catamarca', 'Chaco', 'Chubut',
        'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja',
        'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan', 'San Luis',
        'Santa Cruz', 'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán',
    ];

    protected $fillable = [
        'nombre', 'nombre_corto', 'cue', 'cuit', 'tipo_gestion',
        'provincia', 'localidad', 'domicilio', 'telefono', 'email',
        'logo_path', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Niveles de ESTA institución. Se quita el filtro por institución actual
     * porque la relación ya filtra por institucion_id (si no, el superadmin
     * parado en otra institución no vería los niveles de la que edita).
     */
    public function niveles(): HasMany
    {
        return $this->hasMany(InstitucionNivel::class)
            ->withoutGlobalScope('institucion')
            ->orderBy('orden');
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

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
