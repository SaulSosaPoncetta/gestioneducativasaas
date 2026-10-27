<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asistencia diaria de un estudiante (la toma preceptoría).
 *
 * Cómputo de inasistencias: ausente = 1, llegada tarde = 1/2,
 * retiro anticipado = 1/2. Ajustable en VALORES según la normativa.
 */
class Asistencia extends Model
{
    use PerteneceAInstitucion;

    public const ESTADOS = [
        'presente' => 'Presente',
        'ausente'  => 'Ausente',
        'tarde'    => 'Llegada tarde',
        'retiro'   => 'Retiro anticipado',
    ];

    public const SIGLAS = ['presente' => 'P', 'ausente' => 'A', 'tarde' => 'T', 'retiro' => 'R'];

    public const VALORES = ['presente' => 0.0, 'ausente' => 1.0, 'tarde' => 0.5, 'retiro' => 0.5];

    protected $fillable = ['institucion_id', 'asistencia_toma_id', 'estudiante_id', 'fecha', 'estado', 'justificada', 'motivo'];

    protected function casts(): array
    {
        return [
            'fecha'       => 'date',
            'justificada' => 'boolean',
        ];
    }

    public function toma(): BelongsTo
    {
        return $this->belongsTo(AsistenciaToma::class, 'asistencia_toma_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function getValorAttribute(): float
    {
        return self::VALORES[$this->estado] ?? 0.0;
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public static function color(string $estado): string
    {
        return match ($estado) {
            'presente' => 'success',
            'ausente'  => 'danger',
            default    => 'warning',
        };
    }
}
