<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legajo del estudiante.
 */
class Estudiante extends Model
{
    use PerteneceAInstitucion;

    public const TIPOS_DOCUMENTO = [
        'dni'       => 'DNI',
        'pasaporte' => 'Pasaporte',
        'cedula'    => 'Cédula extranjera',
        'otro'      => 'Otro',
    ];

    public const GENEROS = [
        'femenino'   => 'Femenino',
        'masculino'  => 'Masculino',
        'x'          => 'X / no binario',
        'no_informa' => 'Prefiere no informar',
    ];

    public const ESTADOS = [
        'activo'   => 'Activo',
        'egresado' => 'Egresado',
        'baja'     => 'Baja',
    ];

    public const GRUPOS_SANGUINEOS = ['0+', '0-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    protected $fillable = [
        'institucion_id', 'user_id', 'legajo', 'apellido', 'nombre', 'tipo_documento', 'documento',
        'cuil', 'fecha_nacimiento', 'genero', 'nacionalidad', 'lugar_nacimiento', 'domicilio',
        'localidad', 'telefono', 'email', 'fecha_ingreso', 'estado', 'grupo_sanguineo',
        'obra_social', 'numero_afiliado', 'salud_observaciones', 'emergencia_contacto',
        'emergencia_telefono', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'legajo'           => 'integer',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso'    => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Legajo correlativo por institución.
        static::creating(function (self $e) {
            if (empty($e->legajo)) {
                $e->legajo = (int) static::sinInstitucion()->where('institucion_id', $e->institucion_id)->max('legajo') + 1;
            }
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tutores(): BelongsToMany
    {
        return $this->belongsToMany(Tutor::class, 'estudiante_tutor')
            ->withPivot(['id', 'vinculo', 'es_responsable', 'autorizado_retirar', 'convive', 'recibe_notificaciones'])
            ->withTimestamps()
            ->orderByPivot('es_responsable', 'desc');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class)->latest('fecha');
    }

    /** Inscripciones activas del ciclo indicado. */
    public function inscripcionesActivas(?int $cicloId): HasMany
    {
        return $this->hasMany(Inscripcion::class)
            ->where('estado', Inscripcion::ACTIVA)
            ->where('ciclo_lectivo_id', $cicloId);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (! filled($texto)) {
            return $query;
        }

        $texto = trim($texto);

        // Solo números: DNI o legajo.
        if (preg_match('/^[\d.\s]+$/', $texto)) {
            $digitos = preg_replace('/\D/', '', $texto);

            return $query->where(fn ($q) => $q->where('documento', 'like', "%{$digitos}%")->orWhere('legajo', (int) $digitos));
        }

        // Texto: cada palabra tiene que estar en el apellido o en el nombre ("perez juan").
        foreach (preg_split('/\s+/', $texto) as $palabra) {
            $like = '%' . $palabra . '%';
            $query->where(fn ($q) => $q->where('apellido', 'like', $like)->orWhere('nombre', 'like', $like));
        }

        return $query;
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    public function getDocumentoLabelAttribute(): string
    {
        $tipo = self::TIPOS_DOCUMENTO[$this->tipo_documento] ?? strtoupper($this->tipo_documento);
        $numero = $this->tipo_documento === 'dni' && ctype_digit($this->documento)
            ? number_format((int) $this->documento, 0, ',', '.')
            : $this->documento;

        return "{$tipo} {$numero}";
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'activo'   => 'success',
            'egresado' => 'primary',
            default    => 'secondary',
        };
    }
}
