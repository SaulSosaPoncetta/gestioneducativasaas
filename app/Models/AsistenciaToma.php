<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Asistencia diaria de un curso (una por curso y fecha). */
class AsistenciaToma extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'asistencia_tomas';

    protected $fillable = ['institucion_id', 'ciclo_lectivo_id', 'curso_id', 'fecha', 'user_id'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }
}
