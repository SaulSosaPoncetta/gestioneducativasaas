<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Período de evaluación de un nivel dentro de un ciclo lectivo
 * (trimestre, cuatrimestre, informe, intensificación...).
 * Cerrado = ya no se cargan ni modifican calificaciones de ese período.
 */
class PeriodoEvaluacion extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'periodos_evaluacion';

    public const REGULAR         = 'regular';
    public const INTENSIFICACION = 'intensificacion';

    protected $fillable = [
        'institucion_id', 'ciclo_lectivo_id', 'institucion_nivel_id',
        'nombre', 'tipo', 'orden', 'fecha_inicio', 'fecha_fin', 'cerrado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
            'cerrado'      => 'boolean',
        ];
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'ciclo_lectivo_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(InstitucionNivel::class, 'institucion_nivel_id');
    }

    public function esIntensificacion(): bool
    {
        return $this->tipo === self::INTENSIFICACION;
    }

    /** El período abarca la fecha de hoy. */
    public function estaVigente(): bool
    {
        return today()->between($this->fecha_inicio, $this->fecha_fin);
    }
}
