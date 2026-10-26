<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plan de estudio: plan de primaria, ciclo básico, orientación,
 * especialidad técnica o carrera de nivel superior.
 */
class PlanEstudio extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'planes_estudio';

    public const MODALIDADES = [
        'presencial'     => 'Presencial',
        'semipresencial' => 'Semipresencial',
        'distancia'      => 'A distancia',
    ];

    protected $fillable = [
        'institucion_id', 'institucion_nivel_id', 'tipo', 'nombre', 'titulo',
        'resolucion', 'modalidad', 'grado_desde', 'grado_hasta', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'grado_desde' => 'integer',
            'grado_hasta' => 'integer',
            'activo'      => 'boolean',
        ];
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(InstitucionNivel::class, 'institucion_nivel_id');
    }

    public function espacios(): HasMany
    {
        return $this->hasMany(EspacioCurricular::class)->orderBy('grado')->orderBy('orden')->orderBy('id');
    }

    /** @return int[] */
    public function grados(): array
    {
        return range($this->grado_desde, $this->grado_hasta);
    }

    public function esCarrera(): bool
    {
        return $this->tipo === 'carrera';
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->nivel->nivel->tiposPlan()[$this->tipo] ?? ucfirst($this->tipo);
    }

    /** "1° a 3° Año", "Salas de 3 a 5", "4 años". */
    public function getAlcanceAttribute(): string
    {
        $nivel = $this->nivel->nivel;

        if ($this->esCarrera()) {
            $anios = $this->grado_hasta - $this->grado_desde + 1;
            return $anios . ' ' . ($anios === 1 ? 'año' : 'años');
        }

        if ($this->grado_desde === $this->grado_hasta) {
            return $nivel->etiquetaGrado($this->grado_desde);
        }

        return $nivel->etiquetaGrado($this->grado_desde) . ' a ' . $nivel->etiquetaGrado($this->grado_hasta);
    }
}
