<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $password;
    public ?string $roleName;
    public string $loginUrl;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string|null $password
     * @param string|null $roleName
     * @param string|null $loginUrl
     */
    public function __construct(User $user, ?string $password = null, ?string $roleName = null, ?string $loginUrl = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->roleName = $roleName ?? ($user->roles->pluck('name')->first() ?? 'Usuario');
        $this->loginUrl = $loginUrl ?? route('login');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a AulaSync - Tus credenciales de acceso',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-usuario',
            with: [
                'user' => $this->user,
                'password' => $this->password,
                'roleName' => $this->roleName,
                'loginUrl' => $this->loginUrl,
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
