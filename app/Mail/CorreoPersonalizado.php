<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorreoPersonalizado extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $contenidoHtml;
    public $nombreDestinatario;

    /**
     * Crea una nueva instancia del mensaje
     */
    public function __construct(string $asunto, string $contenidoHtml, ?string $nombreDestinatario = null)
    {
        $this->asunto = $asunto;
        $this->contenidoHtml = $contenidoHtml;
        $this->nombreDestinatario = $nombreDestinatario;
    }

    /**
     * Obtiene el envelope del mensaje
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.correo-personalizado',
            with: [
                'contenidoHtml' => $this->contenidoHtml,
                'nombreDestinatario' => $this->nombreDestinatario,
                'asunto' => $this->asunto,
            ]
        );
    }

    /**
     * Obtiene los adjuntos del mensaje
     */
    public function attachments(): array
    {
        return [];
    }
}
