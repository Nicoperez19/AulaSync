<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Services\CorreoAdministrativoService;

class CorreoPersonalizado extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $contenidoHtml;
    public $nombreDestinatario;
    public $remitenteEmail;
    public $remitenteNombre;

    /**
     * Crea una nueva instancia del mensaje
     * 
     * @param string $asunto Asunto del correo
     * @param string $contenidoHtml Contenido HTML del correo
     * @param string|null $nombreDestinatario Nombre del destinatario
     * @param string|null $idAreaAcademica ID del área académica para usar el correo del asistente académico
     */
    public function __construct(
        string $asunto, 
        string $contenidoHtml, 
        ?string $nombreDestinatario = null,
        ?string $idAreaAcademica = null
    )
    {
        $this->asunto = $asunto;
        $this->contenidoHtml = $contenidoHtml;
        $this->nombreDestinatario = $nombreDestinatario;
        
        // Obtener correo del asistente académico del área si se proporciona
        if ($idAreaAcademica) {
            $correoData = CorreoAdministrativoService::getCorreoAreaAcademica($idAreaAcademica);
            $this->remitenteEmail = $correoData['email'];
            $this->remitenteNombre = $correoData['name'];
        } else {
            $this->remitenteEmail = config('mail.from.address');
            $this->remitenteNombre = config('mail.from.name');
        }
    }

    /**
     * Obtiene el envelope del mensaje
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->remitenteEmail, $this->remitenteNombre),
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
