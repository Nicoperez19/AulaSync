<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ReporteMensualClasesNoRealizadas extends Mailable
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
            subject: 'Reporte Mensual - Clases No Realizadas (' . ucfirst($this->datos['periodo']['mes']) . ' ' . $this->datos['periodo']['anio'] . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-mensual-clases-no-realizadas',
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
            Attachment::fromData(fn () => $this->pdf->output(), 'Reporte_Mensual_Clases_No_Realizadas.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
