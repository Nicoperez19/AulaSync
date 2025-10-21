<?php

namespace App\Mail;

use App\Models\RecuperacionClase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionRecuperacionClase extends Mailable
{
    use Queueable, SerializesModels;

    public $recuperacion;

    /**
     * Create a new message instance.
     */
    public function __construct(RecuperacionClase $recuperacion)
    {
        $this->recuperacion = $recuperacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notificación de Recuperación de Clase',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recuperacion-clase',
            with: [
                'recuperacion' => $this->recuperacion,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
