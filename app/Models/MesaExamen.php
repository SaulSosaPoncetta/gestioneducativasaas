<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Mesa de examen final de una unidad curricular. */
class MesaExamen extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'mesas_examen';

    protected $fillable = [
        'institucion_id', 'espacio_curricular_id', 'llamado', 'fecha', 'aula', 'presidente_id', 'vocales',
        'inscripcion_desde', 'inscripcion_hasta', 'admite_libres', 'estado', 'libro', 'folio', 'cerrada_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha'             => 'datetime',
            'inscripcion_desde' => 'date',
            'inscripcion_hasta' => 'date',
            'admite_libres'     => 'boolean',
            'cerrada_at'        => 'datetime',
        ];
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(EspacioCurricular::class, 'espacio_curricular_id');
    }

    public function presidente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'presidente_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(MesaInscripcion::class);
    }

    public function estaCerrada(): bool
    {
        return $this->estado === 'cerrada';
    }

    public function inscripcionAbierta(): bool
    {
        return ! $this->estaCerrada() && today()->between($this->inscripcion_desde, $this->inscripcion_hasta);
    }
}
