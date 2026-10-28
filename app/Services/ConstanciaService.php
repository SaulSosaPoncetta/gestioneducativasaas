<?php

namespace App\Services;

use App\Models\CicloLectivo;
use App\Models\Constancia;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\MesaInscripcion;
use App\Support\InstitucionActual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Emite constancias numeradas por año, con código de verificación.
 * Los datos se copian al emitir: si después cambia el legajo, la
 * constancia sigue diciendo lo que decía el día que se entregó.
 */
class ConstanciaService
{
    /** Inscripción vigente para constancia de alumno regular (ciclo actual). */
    public function inscripcionVigente(Estudiante $estudiante): ?Inscripcion
    {
        $ciclo = CicloLectivo::actual();

        return $ciclo
            ? $estudiante->inscripcionesActivas($ciclo->id)->with(['curso.nivel', 'plan', 'nivel', 'ciclo'])->first()
            : null;
    }

    /** Exámenes rendidos (con nota), para constancia de examen. */
    public function examenesRendidos(Estudiante $estudiante): Collection
    {
        return MesaInscripcion::with('mesa.espacio.plan')
            ->where('estudiante_id', $estudiante->id)
            ->whereIn('resultado', ['aprobado', 'desaprobado'])
            ->get()
            ->sortByDesc(fn ($i) => $i->mesa->fecha)
            ->values();
    }

    public function emitirAlumnoRegular(Estudiante $estudiante, ?string $presentarAnte, ?int $usuarioId, string $origen = 'gestion'): Constancia
    {
        $insc = $this->inscripcionVigente($estudiante);

        if (! $insc || $estudiante->estado !== 'activo') {
            throw ValidationException::withMessages([
                'tipo' => 'No tiene una inscripción activa en el ciclo lectivo actual: no se puede emitir constancia de alumno regular.',
            ]);
        }

        $superior = ! $insc->curso_id;

        return $this->crear($estudiante, 'alumno_regular', $presentarAnte, $usuarioId, $origen, [
            'inscripcion_id' => $insc->id,
        ], [
            'superior'   => $superior,
            'nivel'      => $insc->nivel->nombre,
            'destino'    => $insc->destino,
            'turno'      => $insc->curso?->turno_label,
            'resolucion' => $insc->plan?->resolucion,
            'condicion'  => $insc->condicion_label,
            'ciclo'      => $insc->ciclo->anio,
        ]);
    }

    public function emitirExamen(MesaInscripcion $examen, ?string $presentarAnte, bool $incluirNota, ?int $usuarioId, string $origen = 'gestion'): Constancia
    {
        $examen->loadMissing('mesa.espacio.plan', 'estudiante');

        if (! in_array($examen->resultado, ['aprobado', 'desaprobado'], true)) {
            throw ValidationException::withMessages([
                'mesa_inscripcion_id' => 'Solo se emite constancia de exámenes con acta cargada en los que el estudiante se presentó.',
            ]);
        }

        $mesa = $examen->mesa;

        return $this->crear($examen->estudiante, 'examen', $presentarAnte, $usuarioId, $origen, [
            'mesa_inscripcion_id' => $examen->id,
        ], [
            'espacio'      => $mesa->espacio->nombre,
            'grado'        => $mesa->espacio->grado,
            'carrera'      => $mesa->espacio->plan->nombre,
            'resolucion'   => $mesa->espacio->plan->resolucion,
            'fecha'        => $mesa->fecha->format('Y-m-d H:i'),
            'llamado'      => $mesa->llamado,
            'condicion'    => $examen->condicion,
            'incluir_nota' => $incluirNota,
            'nota'         => $incluirNota ? $examen->nota : null,
            'resultado'    => $incluirNota ? $examen->resultado : null,
            'libro'        => $mesa->libro,
            'folio'        => $mesa->folio,
        ]);
    }

    public function anular(Constancia $constancia, string $motivo): void
    {
        $constancia->update(['anulada_at' => now(), 'motivo_anulacion' => mb_substr($motivo, 0, 200)]);
    }

    private function crear(Estudiante $e, string $tipo, ?string $presentarAnte, ?int $usuarioId, string $origen, array $extra, array $datos): Constancia
    {
        $institucion = InstitucionActual::get();

        $datos += [
            'estudiante' => [
                'apellido'  => $e->apellido,
                'nombre'    => $e->nombre,
                'documento' => $e->documento_label,
                'legajo'    => $e->legajo,
                'genero'    => $e->genero,
            ],
            'lugar' => $institucion?->localidad,
        ];

        return DB::transaction(function () use ($e, $tipo, $presentarAnte, $usuarioId, $origen, $extra, $datos) {
            $anio = (int) now()->year;

            $numero = (int) Constancia::where('anio', $anio)->lockForUpdate()->max('numero') + 1;

            return Constancia::create($extra + [
                'estudiante_id'  => $e->id,
                'tipo'           => $tipo,
                'anio'           => $anio,
                'numero'         => $numero,
                'codigo'         => $this->codigoNuevo(),
                'presentar_ante' => filled($presentarAnte) ? mb_substr(trim($presentarAnte), 0, 150) : null,
                'datos'          => $datos,
                'emitida_por'    => $usuarioId,
                'origen'         => $origen,
            ]);
        });
    }

    /** 12 caracteres sin letras/números que se confunden (0/O, 1/I/L). */
    private function codigoNuevo(): string
    {
        $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $codigo = '';
            for ($i = 0; $i < 12; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
        } while (Constancia::sinInstitucion()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    /** "el alumno" / "la alumna" / "le estudiante" según el género del legajo. */
    public static function articulo(array $estudiante): array
    {
        return match ($estudiante['genero'] ?? null) {
            'femenino'  => ['la', 'alumna', 'de la interesada'],
            'masculino' => ['el', 'alumno', 'del interesado'],
            default     => ['el/la', 'estudiante', 'de la persona interesada'],
        };
    }
}
