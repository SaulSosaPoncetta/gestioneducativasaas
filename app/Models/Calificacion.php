<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Calificación de un estudiante en un espacio curricular y un período.
 * Llega desde GestiónAula (origen gestionaula) o la corrige la secretaría (manual).
 */
class Calificacion extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'calificaciones';

    protected $fillable = [
        'institucion_id', 'ciclo_lectivo_id', 'periodo_evaluacion_id', 'espacio_curricular_id', 'curso_id',
        'estudiante_id', 'docente_id', 'valor', 'valor_numerico', 'informe', 'observacion', 'origen', 'modificado_por',
    ];

    protected function casts(): array
    {
        return ['valor_numerico' => 'float'];
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'periodo_evaluacion_id');
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(EspacioCurricular::class, 'espacio_curricular_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function modificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function esManual(): bool
    {
        return $this->origen === 'manual';
    }
}
