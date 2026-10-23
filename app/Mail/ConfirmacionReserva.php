<?php

namespace App\Mail;

use App\Models\Reserva;
use App\Services\ComprobanteReservaService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfirmacionReserva extends Mailable
{
    use Queueable, SerializesModels;

    public Reserva $reserva;
    public string $nombreUsuario;
    public string $idEspacio;
    public string $nombreEspacio;
    public string $tipoEspacio;
    public string $horaReserva;
    public string $fechaReserva;
    public ?string $nombreAsignatura;
    public string $idReserva;
    public string $urlComprobante;

    /**
     * Crea una nueva instancia del mailable a partir de una Reserva.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;

        // Nombre del responsable (profesor o solicitante)
        if ($reserva->run_profesor && $reserva->profesor) {
            $this->nombreUsuario = $reserva->profesor->name;
        } elseif ($reserva->run_solicitante && $reserva->solicitante) {
            $this->nombreUsuario = $reserva->solicitante->nombre;
        } else {
            $this->nombreUsuario = 'Usuario';
        }

        // Datos del espacio
        $espacio = $reserva->espacio;
        $this->idEspacio      = $reserva->id_espacio;
        $this->nombreEspacio  = $espacio ? ($espacio->nombre_espacio ?? $reserva->id_espacio) : $reserva->id_espacio;
        $this->tipoEspacio    = $espacio ? ($espacio->tipo_espacio ?? 'No especificado') : 'No especificado';

        // Datos de tiempo
        $this->horaReserva  = substr($reserva->hora, 0, 5);
        $this->fechaReserva = $reserva->fecha_reserva instanceof \Carbon\Carbon
            ? $reserva->fecha_reserva->format('d/m/Y')
            : \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y');

        // Asignatura (opcional)
        $this->nombreAsignatura = $reserva->asignatura
            ? $reserva->asignatura->nombre_asignatura
            : null;

        $this->idReserva = $reserva->id_reserva;
        $this->urlComprobante = route('reservas.comprobante', $reserva->id_reserva);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Reserva – ' . $this->idEspacio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacion-reserva',
        );
    }

    /**
     * Adjuntar comprobante PDF de la reserva al correo
     */
    public function attachments(): array
    {
        try {
            $pdfContent = app(ComprobanteReservaService::class)->generarPdf($this->reserva)->output();

            return [
                Attachment::fromData(fn () => $pdfContent, "comprobante-reserva-{$this->idReserva}.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            Log::warning('No se pudo adjuntar el PDF de comprobante al correo de confirmación: ' . $e->getMessage());
            return [];
        }
    }
}
