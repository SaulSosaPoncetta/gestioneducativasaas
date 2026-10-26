<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Madre, padre o responsable. Un tutor puede tener varios estudiantes
 * (hermanos) y un estudiante varios tutores.
 */
class Tutor extends Model
{
    use PerteneceAInstitucion;

    protected $table = 'tutores';

    public const VINCULOS = [
        'madre'        => 'Madre',
        'padre'        => 'Padre',
        'tutor_legal'  => 'Tutor/a legal',
        'abuelo'       => 'Abuelo/a',
        'tio'          => 'Tío/a',
        'hermano'      => 'Hermano/a',
        'otro'         => 'Otro',
    ];

    protected $fillable = [
        'institucion_id', 'user_id', 'apellido', 'nombre', 'documento', 'telefono',
        'telefono_alternativo', 'email', 'ocupacion', 'domicilio',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_tutor')
            ->withPivot(['id', 'vinculo', 'es_responsable', 'autorizado_retirar', 'convive', 'recibe_notificaciones'])
            ->withTimestamps();
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public static function vinculoLabel(?string $vinculo): string
    {
        return self::VINCULOS[$vinculo] ?? (string) $vinculo;
    }
}
