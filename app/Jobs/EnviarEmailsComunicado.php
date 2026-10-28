<?php

namespace App\Jobs;

use App\Mail\ComunicadoMail;
use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envía por email el comunicado a los destinatarios que tienen email.
 * Se despacha "después de la respuesta": no necesita un worker de colas.
 */
class EnviarEmailsComunicado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $comunicadoId)
    {
    }

    public function handle(): void
    {
        $comunicado = Comunicado::sinInstitucion()->with('institucion')->find($this->comunicadoId);

        if (! $comunicado) {
            return;
        }

        ComunicadoDestinatario::with('usuario')
            ->where('comunicado_id', $comunicado->id)
            ->whereNull('email_enviado_at')
            ->get()
            ->each(function (ComunicadoDestinatario $d) use ($comunicado) {
                $email = $d->usuario?->email;

                if (! $email) {
                    return;
                }

                try {
                    Mail::to($email)->send(new ComunicadoMail($comunicado, $d));
                    $d->update(['email_enviado_at' => now()]);
                } catch (\Throwable $e) {
                    Log::warning("No se pudo enviar el comunicado {$comunicado->id} a {$email}: {$e->getMessage()}");
                }
            });
    }
}
