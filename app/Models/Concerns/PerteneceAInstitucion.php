<?php

namespace App\Models\Concerns;

use App\Models\Institucion;
use App\Support\InstitucionActual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla los datos por institución (multi-institución SaaS).
 *
 * - Toda consulta se filtra por la institución actual.
 * - Al crear un registro se completa institucion_id automáticamente.
 *
 * Para consultar sin filtro (ej. reportes del superadmin):
 *     Modelo::sinInstitucion()->get();
 */
trait PerteneceAInstitucion
{
    public static function bootPerteneceAInstitucion(): void
    {
        static::addGlobalScope('institucion', function (Builder $query) {
            if (InstitucionActual::existe()) {
                $query->where($query->getModel()->getTable() . '.institucion_id', InstitucionActual::id());
            }
        });

        static::creating(function ($modelo) {
            if (empty($modelo->institucion_id) && InstitucionActual::existe()) {
                $modelo->institucion_id = InstitucionActual::id();
            }
        });
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function scopeSinInstitucion(Builder $query): Builder
    {
        return $query->withoutGlobalScope('institucion');
    }
}
