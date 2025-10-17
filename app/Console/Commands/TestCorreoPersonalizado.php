<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\CorreoPersonalizado;

class TestCorreoPersonalizado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'correo:test {email} {--nombre=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un correo de prueba al email especificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $nombre = $this->option('nombre') ?? 'Usuario';

        $this->info("Enviando correo de prueba a: {$email}");

        try {
            $asunto = 'Correo de Prueba - AulaSync';
            $contenidoHtml = "
                <h2>¡Hola {$nombre}!</h2>
                <p>Este es un correo de prueba del sistema de correos masivos de AulaSync.</p>
                <p><strong>Características:</strong></p>
                <ul>
                    <li>✅ Envío de correos personalizados</li>
                    <li>✅ Soporte para HTML</li>
                    <li>✅ Destinatarios internos y externos</li>
                    <li>✅ Plantillas predefinidas</li>
                </ul>
                <p>Si recibes este correo, significa que la configuración está funcionando correctamente.</p>
                <p>Saludos,<br><strong>Equipo AulaSync</strong></p>
            ";

            Mail::to($email)->send(new CorreoPersonalizado($asunto, $contenidoHtml, $nombre));

            $this->info("✅ Correo enviado exitosamente a {$email}");
            $this->newLine();
            $this->info("Verifica la bandeja de entrada (y también la carpeta de spam).");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar el correo:");
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn("Posibles soluciones:");
            $this->line("1. Verifica que MAIL_* esté configurado en .env");
            $this->line("2. Si usas Gmail, asegúrate de usar una Contraseña de Aplicación");
            $this->line("3. Revisa que el puerto y encryption sean correctos (587/tls)");
            $this->line("4. Verifica los logs en storage/logs/laravel.log");

            return Command::FAILURE;
        }
    }
}
