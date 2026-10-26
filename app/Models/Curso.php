<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Curso de un ciclo lectivo en un nivel escolar: "Sala de 4 Roja", "3° Grado A".
 */
class Curso extends Model
{
    use PerteneceAInstitucion;

    public const TURNOS = [
        'manana'     => 'Mañana',
        'tarde'      => 'Tarde',
        'vespertino' => 'Vespertino',
        'noche'      => 'Noche',
        'completa'   => 'Jornada completa',
    ];

    protected $fillable = [
        'institucion_id', 'ciclo_lectivo_id', 'institucion_nivel_id',
        'grado', 'division', 'turno', 'preceptor_id', 'aula', 'cupo',
    ];

    protected function casts(): array
    {
        return [
            'grado' => 'integer',
            'cupo'  => 'integer',
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

    public function preceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preceptor_id');
    }

    /** "3° Grado A", "Sala de 4 Roja". */
    public function getNombreAttribute(): string
    {
        return $this->nivel->nivel->etiquetaGrado($this->grado) . ' ' . $this->division;
    }

    /** "3° A", "Sala 4 Roja". */
    public function getNombreCortoAttribute(): string
    {
        return $this->nivel->nivel->etiquetaGradoCorta($this->grado) . ' ' . $this->division;
    }

    public function getTurnoLabelAttribute(): string
    {
        return self::TURNOS[$this->turno] ?? $this->turno;
    }
}
