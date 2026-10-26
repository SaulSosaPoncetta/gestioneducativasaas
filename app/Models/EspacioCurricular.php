<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Espacio curricular de un plan: área, materia, taller, seminario,
 * práctica... Se dicta en un año/grado con un régimen (anual o cuatrimestral).
 */
class EspacioCurricular extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'espacios_curriculares';

    public const REGIMENES = [
        'anual'                => 'Anual',
        'primer_cuatrimestre'  => '1° cuatrimestre',
        'segundo_cuatrimestre' => '2° cuatrimestre',
    ];

    public const FORMATOS = [
        'materia'     => 'Materia / asignatura',
        'area'        => 'Área',
        'taller'      => 'Taller',
        'laboratorio' => 'Laboratorio',
        'seminario'   => 'Seminario',
        'practica'    => 'Práctica',
        'proyecto'    => 'Proyecto',
    ];

    public const CAMPOS = [
        'general'               => 'Formación general',
        'cientifico_tecnologica' => 'Formación científico-tecnológica',
        'especifica'            => 'Formación específica',
        'tecnica_especifica'    => 'Formación técnica específica',
        'practica_profesional'  => 'Práctica profesional',
    ];

    /** Tipos de correlatividad (nivel superior). */
    public const CORRELATIVIDADES = [
        'regular_para_cursar'  => 'Regular para cursar',
        'aprobada_para_cursar' => 'Aprobada para cursar',
        'aprobada_para_rendir' => 'Aprobada para rendir',
    ];

    protected $fillable = [
        'institucion_id', 'plan_estudio_id', 'nombre', 'codigo', 'grado',
        'regimen', 'formato', 'campo', 'horas_semanales', 'orden',
    ];

    protected function casts(): array
    {
        return [
            'grado'           => 'integer',
            'horas_semanales' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'plan_estudio_id');
    }

    /** Espacios que este necesita (con el tipo en el pivot). */
    public function correlativas(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'correlatividades', 'espacio_curricular_id', 'requiere_id')
            ->withPivot('tipo');
    }

    /** Espacios que necesitan a este. */
    public function habilitaA(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'correlatividades', 'requiere_id', 'espacio_curricular_id')
            ->withPivot('tipo');
    }

    /**
     * Puede ser correlativa de $otro: se dicta antes (año anterior, o mismo
     * año en el 1° cuatrimestre cuando el otro es del 2°).
     */
    public function esAnteriorA(self $otro): bool
    {
        if ($this->id === $otro->id || $this->plan_estudio_id !== $otro->plan_estudio_id) {
            return false;
        }

        return $this->grado < $otro->grado
            || ($this->grado === $otro->grado
                && $this->regimen === 'primer_cuatrimestre'
                && $otro->regimen === 'segundo_cuatrimestre');
    }

    public function getRegimenLabelAttribute(): string
    {
        return self::REGIMENES[$this->regimen] ?? $this->regimen;
    }

    public function getFormatoLabelAttribute(): string
    {
        return self::FORMATOS[$this->formato] ?? $this->formato;
    }

    public function getCampoLabelAttribute(): ?string
    {
        return $this->campo ? (self::CAMPOS[$this->campo] ?? $this->campo) : null;
    }
}
