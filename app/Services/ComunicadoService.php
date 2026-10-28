<?php

namespace App\Services;

use App\Jobs\EnviarEmailsComunicado;
use App\Models\Asignacion;
use App\Models\CicloLectivo;
use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use App\Models\Inscripcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calcula los destinatarios de un comunicado y lo publica.
 * Reciben quienes tienen cuenta en el portal: familias (tutores que reciben
 * avisos), estudiantes y docentes del alcance elegido, en el ciclo actual.
 */
class ComunicadoService
{
    /**
     * @return array{destinatarios: Collection, sin_cuenta: int}
     *   destinatarios: user_id => ['tipo' => ..., 'referencia' => ...]
     */
    public function destinatarios(Comunicado $comunicado): array
    {
        $ciclo = CicloLectivo::actual();
        $ids   = $comunicado->alcance_ids ?? [];
        $para  = $comunicado->para ?? [];

        $destinatarios = collect();
        $sinCuenta = 0;

        if (! $ciclo) {
            return ['destinatarios' => $destinatarios, 'sin_cuenta' => 0];
        }

        $inscripciones = Inscripcion::with(['estudiante.tutores', 'curso.nivel', 'plan'])
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->where('estado', Inscripcion::ACTIVA)
            ->when($comunicado->alcance === 'niveles', fn ($q) => $q->whereIn('institucion_nivel_id', $ids))
            ->when($comunicado->alcance === 'cursos', fn ($q) => $q->whereIn('curso_id', $ids))
            ->when($comunicado->alcance === 'carreras', fn ($q) => $q->whereNull('curso_id')->whereIn('plan_estudio_id', $ids))
            ->get();

        foreach ($inscripciones as $insc) {
            $e = $insc->estudiante;

            if (in_array('estudiantes', $para, true)) {
                if ($e->user_id) {
                    $destinatarios->put($e->user_id, ['tipo' => 'estudiante', 'referencia' => "{$e->nombre} {$e->apellido} ({$insc->destino})"]);
                } else {
                    $sinCuenta++;
                }
            }

            if (in_array('familias', $para, true)) {
                foreach ($e->tutores->where('pivot.recibe_notificaciones', true) as $t) {
                    if (! $t->user_id) {
                        $sinCuenta++;
                        continue;
                    }

                    $previo = $destinatarios->get($t->user_id);
                    $hijo = "{$e->nombre} ({$insc->destino})";
                    $destinatarios->put($t->user_id, [
                        'tipo'       => $previo['tipo'] ?? 'familia',
                        'referencia' => $previo && $previo['tipo'] === 'familia' ? $previo['referencia'] . ', ' . $hijo : 'Familia de ' . $hijo,
                    ]);
                }
            }
        }

        if (in_array('docentes', $para, true)) {
            Asignacion::with(['docente', 'curso', 'espacio.plan'])->activas()
                ->where('ciclo_lectivo_id', $ciclo->id)
                ->when($comunicado->alcance === 'niveles', fn ($q) => $q->where(fn ($w) => $w
                    ->whereHas('curso', fn ($c) => $c->whereIn('institucion_nivel_id', $ids))
                    ->orWhereHas('espacio.plan', fn ($p) => $p->whereIn('institucion_nivel_id', $ids))))
                ->when($comunicado->alcance === 'cursos', fn ($q) => $q->whereIn('curso_id', $ids))
                ->when($comunicado->alcance === 'carreras', fn ($q) => $q->whereNull('curso_id')
                    ->whereHas('espacio', fn ($e) => $e->whereIn('plan_estudio_id', $ids)))
                ->get()
                ->pluck('docente')
                ->unique('id')
                ->each(function ($d) use ($destinatarios, &$sinCuenta) {
                    if (! $d->user_id) {
                        $sinCuenta++;
                        return;
                    }
                    if (! $destinatarios->has($d->user_id)) {
                        $destinatarios->put($d->user_id, ['tipo' => 'docente', 'referencia' => "Docente {$d->nombre} {$d->apellido}"]);
                    }
                });
        }

        return ['destinatarios' => $destinatarios, 'sin_cuenta' => $sinCuenta];
    }

    /** Publica: guarda los destinatarios y manda los emails después de responder. */
    public function publicar(Comunicado $comunicado): int
    {
        $calculo = $this->destinatarios($comunicado);

        if ($calculo['destinatarios']->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($comunicado, $calculo) {
            foreach ($calculo['destinatarios'] as $userId => $d) {
                ComunicadoDestinatario::firstOrCreate(
                    ['comunicado_id' => $comunicado->id, 'user_id' => $userId],
                    ['tipo' => $d['tipo'], 'referencia' => mb_substr($d['referencia'], 0, 200)]
                );
            }

            $comunicado->update(['publicado_at' => now()]);
        });

        if ($comunicado->enviar_email) {
            EnviarEmailsComunicado::dispatch($comunicado->id)->afterResponse();
        }

        return $calculo['destinatarios']->count();
    }
}
