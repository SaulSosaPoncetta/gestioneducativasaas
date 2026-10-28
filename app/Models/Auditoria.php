<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucion;
use App\Support\InstitucionActual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Registro de auditoría: quién cambió qué y cuándo.
 * Los modelos auditados y sus etiquetas están en MODELOS; se registran
 * con App\Observers\AuditoriaObserver desde AppServiceProvider.
 */
class Auditoria extends Model
{
    use PerteneceAInstitucion;

    public const UPDATED_AT = null;

    /** Clase => [etiqueta, eventos que se registran]. */
    public const MODELOS = [
        Institucion::class        => ['Institución', ['creado', 'actualizado', 'eliminado']],
        InstitucionNivel::class   => ['Nivel', ['creado', 'actualizado', 'eliminado']],
        User::class               => ['Usuario', ['creado', 'actualizado', 'eliminado']],
        CicloLectivo::class       => ['Ciclo lectivo', ['creado', 'actualizado', 'eliminado']],
        PeriodoEvaluacion::class  => ['Período', ['creado', 'actualizado', 'eliminado']],
        Curso::class              => ['Curso', ['creado', 'actualizado', 'eliminado']],
        PlanEstudio::class        => ['Plan de estudio', ['creado', 'actualizado', 'eliminado']],
        EspacioCurricular::class  => ['Espacio curricular', ['creado', 'actualizado', 'eliminado']],
        Estudiante::class         => ['Estudiante', ['creado', 'actualizado', 'eliminado']],
        Tutor::class              => ['Tutor', ['creado', 'actualizado', 'eliminado']],
        Inscripcion::class        => ['Inscripción', ['creado', 'actualizado', 'eliminado']],
        Docente::class            => ['Docente', ['creado', 'actualizado', 'eliminado']],
        Asignacion::class         => ['Asignación docente', ['creado', 'actualizado', 'eliminado']],
        // Asistencia y notas: se cargan en volumen (preceptoría / API), se auditan las correcciones.
        Asistencia::class         => ['Asistencia', ['actualizado', 'eliminado']],
        Calificacion::class       => ['Calificación', ['actualizado', 'eliminado']],
        Cursada::class            => ['Cursada', ['creado', 'actualizado', 'eliminado']],
        MesaExamen::class         => ['Mesa de examen', ['creado', 'actualizado', 'eliminado']],
        MesaInscripcion::class    => ['Inscripción a mesa', ['creado', 'actualizado', 'eliminado']],
        Comunicado::class         => ['Comunicado', ['creado', 'actualizado', 'eliminado']],
        Constancia::class         => ['Constancia', ['creado', 'actualizado']],
    ];

    public const EVENTOS = [
        'creado'          => ['Alta', 'success'],
        'actualizado'     => ['Modificación', 'primary'],
        'eliminado'       => ['Baja', 'danger'],
        'ingreso'         => ['Ingreso', 'secondary'],
        'salida'          => ['Salida', 'secondary'],
        'ingreso_fallido' => ['Ingreso fallido', 'warning'],
    ];

    /** Se puede apagar (ej. en el seeder) con Auditoria::$activa = false. */
    public static bool $activa = true;

    protected $table = 'auditorias';

    protected $fillable = [
        'institucion_id', 'user_id', 'usuario_nombre', 'evento', 'modelo_tipo', 'modelo_id',
        'descripcion', 'antes', 'despues', 'origen', 'ip', 'user_agent', 'url',
    ];

    protected function casts(): array
    {
        return [
            'antes'      => 'array',
            'despues'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEventoLabelAttribute(): string
    {
        return self::EVENTOS[$this->evento][0] ?? $this->evento;
    }

    public function getEventoColorAttribute(): string
    {
        return self::EVENTOS[$this->evento][1] ?? 'light';
    }

    public function getModeloLabelAttribute(): ?string
    {
        return $this->modelo_tipo ? (self::MODELOS[$this->modelo_tipo][0] ?? class_basename($this->modelo_tipo)) : null;
    }

    /** Campos que cambiaron, con valor anterior y nuevo. */
    public function cambios(): array
    {
        $claves = array_unique(array_merge(array_keys($this->antes ?? []), array_keys($this->despues ?? [])));

        return collect($claves)->mapWithKeys(fn ($k) => [$k => [
            'antes'   => $this->antes[$k] ?? null,
            'despues' => $this->despues[$k] ?? null,
        ]])->all();
    }

    /**
     * Guarda un registro. Nunca interrumpe la operación principal:
     * si falla, queda en el log de Laravel.
     */
    public static function registrar(string $evento, string $descripcion, array $extra = []): void
    {
        if (! static::$activa) {
            return;
        }

        try {
            $request = app()->runningInConsole() ? null : request();
            $usuario = array_key_exists('usuario', $extra) ? $extra['usuario'] : Auth::user();
            $institucionId = ($extra['institucion_id'] ?? null) ?? InstitucionActual::id() ?? $usuario?->institucion_id;
            unset($extra['usuario'], $extra['institucion_id']);

            $origen = match (true) {
                $request === null                          => 'consola',
                $request->is('api/*')                      => 'api',
                default                                    => 'web',
            };

            static::sinInstitucion()->create($extra + [
                'institucion_id' => $institucionId,
                'user_id'        => $usuario?->id,
                'usuario_nombre' => $usuario ? mb_substr(trim($usuario->nombre . ' ' . $usuario->apellido), 0, 150) : ($origen === 'api' ? 'GestiónAula (API)' : null),
                'evento'         => $evento,
                'descripcion'    => mb_substr($descripcion, 0, 255),
                'origen'         => $origen,
                'ip'             => $request?->ip(),
                'user_agent'     => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
                'url'            => $request ? mb_substr($request->method() . ' ' . $request->path(), 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar la auditoría: ' . $e->getMessage());
        }
    }
}
