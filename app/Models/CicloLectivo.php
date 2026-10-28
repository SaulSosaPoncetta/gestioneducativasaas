<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloLectivo extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'ciclos_lectivos';

    public const PLANIFICACION = 'planificacion';
    public const EN_CURSO      = 'en_curso';
    public const CERRADO       = 'cerrado';

    public const ESTADOS = [
        self::PLANIFICACION => 'En planificación',
        self::EN_CURSO      => 'En curso',
        self::CERRADO       => 'Cerrado',
    ];

    protected $fillable = [
        'institucion_id', 'anio', 'nombre', 'fecha_inicio', 'fecha_fin', 'estado',
        'inscripcion_cursadas_desde', 'inscripcion_cursadas_hasta',
    ];

    protected function casts(): array
    {
        return [
            'anio'         => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
            'inscripcion_cursadas_desde' => 'date',
            'inscripcion_cursadas_hasta' => 'date',
        ];
    }

    public function periodos(): HasMany
    {
        return $this->hasMany(PeriodoEvaluacion::class)->orderBy('orden');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    public function scopeEnCurso(Builder $query): Builder
    {
        return $query->where('estado', self::EN_CURSO);
    }

    /** Ciclo en curso, o el más reciente si no hay ninguno en curso. */
    public static function actual(): ?self
    {
        return static::enCurso()->first() ?? static::orderByDesc('anio')->first();
    }

    /** Los estudiantes de nivel superior pueden inscribirse solos a cursadas. */
    public function inscripcionCursadasAbierta(): bool
    {
        return ! $this->estaCerrado()
            && $this->inscripcion_cursadas_desde && $this->inscripcion_cursadas_hasta
            && today()->between($this->inscripcion_cursadas_desde, $this->inscripcion_cursadas_hasta);
    }

    public function estaCerrado(): bool
    {
        return $this->estado === self::CERRADO;
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::EN_CURSO => 'success',
            self::CERRADO  => 'secondary',
            default        => 'warning',
        };
    }
}
