<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InconsistenciaSistema extends Mailable
{
    use Queueable, SerializesModels;

    public $datosInconsistencias;

    /**
     * Create a new message instance.
     */
    public function __construct($datosInconsistencias)
    {
        $this->datosInconsistencias = $datosInconsistencias;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Alerta: Inconsistencias detectadas en SIA',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inconsistencia-sistema',
            with: [
                'datos' => $this->datosInconsistencias,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
