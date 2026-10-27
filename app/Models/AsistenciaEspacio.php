<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asistencia por materia que envía GestiónAula (solo lectura acá).
 */
class AsistenciaEspacio extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'asistencias_espacio';

    protected $fillable = [
        'institucion_id', 'ciclo_lectivo_id', 'asignacion_id', 'espacio_curricular_id', 'curso_id',
        'docente_id', 'estudiante_id', 'fecha', 'estado', 'observacion', 'origen', 'referencia',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
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
}
