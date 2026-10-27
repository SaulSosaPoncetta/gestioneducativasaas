<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asignación (designación) de un docente a un espacio curricular en un
 * ciclo lectivo: en un curso (escolar) o en una comisión (superior).
 */
class Asignacion extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'asignaciones';

    public const SITUACIONES = [
        'titular'     => 'Titular',
        'provisional' => 'Provisional',
        'interino'    => 'Interino/a',
        'suplente'    => 'Suplente',
    ];

    public const ACTIVA     = 'activa';
    public const FINALIZADA = 'finalizada';

    protected $fillable = [
        'institucion_id', 'docente_id', 'ciclo_lectivo_id', 'espacio_curricular_id', 'curso_id',
        'comision', 'situacion', 'horas', 'fecha_desde', 'fecha_hasta', 'estado', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'horas'       => 'integer',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'ciclo_lectivo_id');
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(EspacioCurricular::class, 'espacio_curricular_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', self::ACTIVA);
    }

    public function estaActiva(): bool
    {
        return $this->estado === self::ACTIVA;
    }

    /** "3° Grado A" o "Comisión A". */
    public function getGrupoAttribute(): string
    {
        return $this->curso?->nombre ?? ('Comisión ' . ($this->comision ?: 'Única'));
    }

    public function getSituacionLabelAttribute(): string
    {
        return self::SITUACIONES[$this->situacion] ?? $this->situacion;
    }

    public function getSituacionColorAttribute(): string
    {
        return match ($this->situacion) {
            'titular'  => 'primary',
            'suplente' => 'warning',
            default    => 'info',
        };
    }
}
