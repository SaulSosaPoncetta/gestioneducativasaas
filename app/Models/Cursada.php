<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cursada de una unidad curricular (nivel superior) en un ciclo lectivo.
 *
 * Criterios de cierre sugeridos (ajustables acá según el régimen académico):
 * asistencia mínima, nota para regularizar y nota para promocionar.
 */
class Cursada extends Model
{
    use PerteneceAInstitucion;

    public const ASISTENCIA_MINIMA  = 75;   // %
    public const NOTA_REGULARIDAD   = 4;
    public const NOTA_PROMOCION     = 7;
    public const VIGENCIA_ANIOS     = 2;    // la regularidad vence a los 2 años del cierre del ciclo

    public const CONDICIONES = [
        'cursando'     => 'Cursando',
        'regular'      => 'Regular',
        'promocionado' => 'Promocionado',
        'libre'        => 'Libre',
        'abandono'     => 'Abandonó',
    ];

    protected $fillable = [
        'institucion_id', 'estudiante_id', 'espacio_curricular_id', 'ciclo_lectivo_id', 'comision',
        'fecha_inscripcion', 'condicion', 'nota', 'fecha_condicion', 'vence', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'fecha_condicion'   => 'date',
            'vence'             => 'date',
            'nota'              => 'float',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(EspacioCurricular::class, 'espacio_curricular_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'ciclo_lectivo_id');
    }

    public function getCondicionLabelAttribute(): string
    {
        return self::CONDICIONES[$this->condicion] ?? $this->condicion;
    }

    public static function color(string $condicion): string
    {
        return match ($condicion) {
            'promocionado' => 'success',
            'regular'      => 'primary',
            'cursando'     => 'info',
            default        => 'secondary',
        };
    }

    public function regularVigente(): bool
    {
        return $this->condicion === 'regular' && (! $this->vence || $this->vence->gte(today()));
    }
}
