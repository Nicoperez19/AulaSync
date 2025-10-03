<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ReporteSemanalClasesNoRealizadas extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;
    public $pdf;

    /**
     * Create a new message instance.
     */
    public function __construct($datos, $pdf)
    {
        $this->datos = $datos;
        $this->pdf = $pdf;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte Semanal - Clases No Realizadas (Semana ' . $this->datos['periodo']['semana'] . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-semanal-clases-no-realizadas',
            with: ['datos' => $this->datos],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'Reporte_Semanal_Clases_No_Realizadas.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
