<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Inscripción de un estudiante a una mesa y su resultado en el acta. */
class MesaInscripcion extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'mesa_inscripciones';

    public const RESULTADOS = [
        'aprobado'    => 'Aprobado',
        'desaprobado' => 'Desaprobado',
        'ausente'     => 'Ausente',
    ];

    protected $fillable = ['institucion_id', 'mesa_examen_id', 'estudiante_id', 'condicion', 'nota', 'resultado'];

    protected function casts(): array
    {
        return ['nota' => 'float'];
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class, 'mesa_examen_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public static function color(?string $resultado): string
    {
        return match ($resultado) {
            'aprobado'    => 'success',
            'desaprobado' => 'danger',
            'ausente'     => 'secondary',
            default       => 'light',
        };
    }
}
