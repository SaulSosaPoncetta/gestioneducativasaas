<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inscripción de un estudiante en un ciclo lectivo:
 * - niveles escolares: a un curso (y por él, a su plan);
 * - nivel superior: a una carrera (plan de estudio).
 */
class Inscripcion extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'inscripciones';

    public const ACTIVA     = 'activa';
    public const BAJA       = 'baja';
    public const PASE       = 'pase';
    public const FINALIZADA = 'finalizada';

    public const ESTADOS = [
        self::ACTIVA     => 'Activa',
        self::BAJA       => 'Baja',
        self::PASE       => 'Pase a otra institución',
        self::FINALIZADA => 'Finalizada',
    ];

    public const CONDICIONES_ESCOLAR  = ['regular' => 'Regular', 'repitente' => 'Repitente'];
    public const CONDICIONES_SUPERIOR = ['ingresante' => 'Ingresante', 'regular' => 'Regular', 'oyente' => 'Oyente'];

    protected $fillable = [
        'institucion_id', 'estudiante_id', 'ciclo_lectivo_id', 'institucion_nivel_id', 'curso_id',
        'plan_estudio_id', 'fecha', 'condicion', 'estado', 'fecha_baja', 'motivo_baja', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha'      => 'date',
            'fecha_baja' => 'date',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'ciclo_lectivo_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(InstitucionNivel::class, 'institucion_nivel_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'plan_estudio_id');
    }

    public function estaActiva(): bool
    {
        return $this->estado === self::ACTIVA;
    }

    /** "3° Grado A" o "Profesorado de Educación Primaria". */
    public function getDestinoAttribute(): string
    {
        return $this->curso?->nombre ?? $this->plan?->nombre ?? '—';
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ACTIVA     => 'success',
            self::FINALIZADA => 'primary',
            default          => 'secondary',
        };
    }

    public function getCondicionLabelAttribute(): string
    {
        return (self::CONDICIONES_ESCOLAR + self::CONDICIONES_SUPERIOR)[$this->condicion] ?? $this->condicion;
    }
}
