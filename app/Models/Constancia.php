<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Constancia extends Model
{
    use PerteneceAInstitucion;

    public const TIPOS = [
        'alumno_regular' => 'Alumno regular',
        'examen'         => 'Examen rendido',
    ];

    protected $fillable = [
        'institucion_id', 'estudiante_id', 'tipo', 'anio', 'numero', 'codigo', 'inscripcion_id',
        'mesa_inscripcion_id', 'presentar_ante', 'datos', 'emitida_por', 'origen', 'anulada_at', 'motivo_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'datos'      => 'array',
            'anio'       => 'integer',
            'numero'     => 'integer',
            'anulada_at' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitida_por');
    }

    public function estaAnulada(): bool
    {
        return $this->anulada_at !== null;
    }

    /** "N° 15/2026". */
    public function getNumeroLabelAttribute(): string
    {
        return 'N° ' . $this->numero . '/' . $this->anio;
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /** Código con guiones para leerlo fácil: ABCD-EFGH-JK. */
    public function getCodigoLabelAttribute(): string
    {
        return implode('-', str_split($this->codigo, 4));
    }

    public function getUrlVerificacionAttribute(): string
    {
        return route('constancias.verificar', $this->codigo);
    }
}
