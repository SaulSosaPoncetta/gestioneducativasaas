<?php

namespace App\Mail;

use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Comunicado $comunicado, public ComunicadoDestinatario $destinatario)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->comunicado->institucion->nombre_visible . '] ' . $this->comunicado->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comunicado');
    }
}
