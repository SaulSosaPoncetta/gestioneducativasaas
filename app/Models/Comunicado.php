<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class Comunicado extends Model
{
    use PerteneceAInstitucion;

    public const ALCANCES = [
        'institucion' => 'Toda la institución',
        'niveles'     => 'Niveles',
        'cursos'      => 'Cursos',
        'carreras'    => 'Carreras',
    ];

    public const PARA = [
        'familias'    => 'Familias',
        'estudiantes' => 'Estudiantes',
        'docentes'    => 'Docentes',
    ];

    protected $fillable = [
        'institucion_id', 'autor_id', 'titulo', 'cuerpo', 'alcance', 'alcance_ids', 'para',
        'requiere_confirmacion', 'enviar_email', 'adjunto_path', 'adjunto_nombre', 'publicado_at',
    ];

    protected function casts(): array
    {
        return [
            'alcance_ids'           => 'array',
            'para'                  => 'array',
            'requiere_confirmacion' => 'boolean',
            'enviar_email'          => 'boolean',
            'publicado_at'          => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(ComunicadoDestinatario::class);
    }

    public function scopePublicados(Builder $query): Builder
    {
        return $query->whereNotNull('publicado_at');
    }

    public function estaPublicado(): bool
    {
        return $this->publicado_at !== null;
    }

    /** Cantidad de comunicados sin leer de un usuario (para el aviso del navbar). */
    public static function sinLeerPara(User $usuario): int
    {
        return ComunicadoDestinatario::where('user_id', $usuario->id)->whereNull('leido_at')->count();
    }

    /** Texto con saltos de línea y links clickeables (el resto se escapa). */
    public function cuerpoHtml(): HtmlString
    {
        $html = nl2br(e($this->cuerpo));
        $html = preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener">$1</a>', $html);

        return new HtmlString($html);
    }

    /** "Familias y estudiantes de 3° Grado A, 3° Grado B". */
    public function getDescripcionDestinoAttribute(): string
    {
        $para = collect($this->para)->map(fn ($p) => mb_strtolower(self::PARA[$p] ?? $p))->join(', ', ' y ');

        $donde = match ($this->alcance) {
            'niveles'  => InstitucionNivel::whereIn('id', $this->alcance_ids ?? [])->get()->map->nombre->join(', '),
            'cursos'   => Curso::with('nivel')->whereIn('id', $this->alcance_ids ?? [])->get()->map->nombre->join(', '),
            'carreras' => PlanEstudio::whereIn('id', $this->alcance_ids ?? [])->pluck('nombre')->join(', '),
            default    => 'toda la institución',
        };

        return ucfirst($para) . ' · ' . $donde;
    }
}
