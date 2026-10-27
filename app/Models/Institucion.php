<?php

namespace App\Models;

use App\Enums\NivelEducativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    protected $hidden = ['api_token'];

    protected function casts(): array
    {
        return [
            'activo'              => 'boolean',
            'api_token_creado_at' => 'datetime',
        ];
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

    // -------------------------------------------------------------------------
    // Token de integración (GestiónAula). Se guarda solo el hash SHA-256:
    // el token en claro se muestra una única vez al generarlo.
    // -------------------------------------------------------------------------

    public function generarApiToken(): string
    {
        $token = 'ge_' . Str::random(48);

        $this->forceFill([
            'api_token'           => hash('sha256', $token),
            'api_token_creado_at' => now(),
        ])->save();

        return $token;
    }

    public function revocarApiToken(): void
    {
        $this->forceFill(['api_token' => null, 'api_token_creado_at' => null])->save();
    }

    public function tieneApiToken(): bool
    {
        return $this->api_token !== null;
    }

    public static function porApiToken(?string $token): ?self
    {
        if (! $token) {
            return null;
        }

        return static::where('api_token', hash('sha256', $token))->where('activo', true)->first();
    }
}
