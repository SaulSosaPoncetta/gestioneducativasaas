<?php

namespace App\Models;

use App\Enums\EscalaCalificacion;
use App\Enums\NivelEducativo;
use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;

/**
 * Un nivel que ofrece una institución (ej. "Primaria" del Colegio X).
 * En Argentina cada nivel suele tener su propio CUE anexo y denominación
 * oficial (ej. "EP N° 12"), por eso se guardan acá y no en la institución.
 */
class InstitucionNivel extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'institucion_niveles';

    protected $fillable = [
        'institucion_id', 'nivel', 'denominacion', 'cue_anexo', 'duracion', 'orden', 'activo',
        'escala', 'nota_aprobacion', 'aprueba_desde',
    ];

    protected function casts(): array
    {
        return [
            'nivel'           => NivelEducativo::class,
            'activo'          => 'boolean',
            'nota_aprobacion' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $n) {
            $n->orden    = $n->nivel->orden();
            $n->duracion = $n->duracion ?: $n->nivel->duracionSugerida();
        });
    }

    public function getNombreAttribute(): string
    {
        return $this->denominacion ?: $this->nivel->label();
    }

    // -------------------------------------------------------------------------
    // Calificaciones
    // -------------------------------------------------------------------------

    public function escalaCalificacion(): EscalaCalificacion
    {
        return EscalaCalificacion::tryFrom((string) $this->escala) ?? EscalaCalificacion::sugerida($this->nivel);
    }

    public function notaAprobacion(): float
    {
        return $this->nota_aprobacion ?: EscalaCalificacion::aprobacionSugerida($this->nivel);
    }

    public function apruebaDesde(): string
    {
        return $this->aprueba_desde ?: 'B';
    }

    /** ¿La calificación aprueba? null si la escala no aprueba/desaprueba (informe). */
    public function aprueba(?string $valor, ?float $numerico): ?bool
    {
        return match ($this->escalaCalificacion()) {
            EscalaCalificacion::Numerica => $numerico === null ? null : $numerico >= $this->notaAprobacion(),
            EscalaCalificacion::Conceptual => $valor === null ? null : array_search($valor, array_keys(EscalaCalificacion::CONCEPTOS), true)
                <= array_search($this->apruebaDesde(), array_keys(EscalaCalificacion::CONCEPTOS), true),
            EscalaCalificacion::Informe => null,
        };
    }
}
