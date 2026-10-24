<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'nivel'  => NivelEducativo::class,
            'activo' => 'boolean',
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
}
