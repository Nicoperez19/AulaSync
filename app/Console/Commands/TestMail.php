<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración de correo enviando un email de prueba';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Probando configuración de correo...");
        $this->info("Enviando email de prueba a: {$email}");
        
        try {
            // Probar configuración básica
            $this->info("Configuración actual:");
            $this->info("MAIL_MAILER: " . config('mail.default'));
            $this->info("MAIL_HOST: " . config('mail.mailers.smtp.host'));
            $this->info("MAIL_PORT: " . config('mail.mailers.smtp.port'));
            $this->info("MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
            $this->info("MAIL_USERNAME: " . config('mail.mailers.smtp.username'));
            $this->info("MAIL_FROM_ADDRESS: " . config('mail.from.address'));
            $this->info("MAIL_FROM_NAME: " . config('mail.from.name'));
            
            // Enviar email de prueba
            Mail::raw('Este es un email de prueba desde AulaSync. Si recibes este mensaje, la configuración de correo está funcionando correctamente.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Prueba de Configuración - AulaSync')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info("✅ Email enviado exitosamente!");
            $this->info("Revisa tu bandeja de entrada (y carpeta de spam)");
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email:");
            $this->error($e->getMessage());
            
            // Log del error para debugging
            Log::error('Error en comando TestMail: ' . $e->getMessage());
            
            // Sugerencias de solución
            $this->warn("\nPosibles soluciones:");
            $this->warn("1. Verifica que MAIL_USERNAME y MAIL_PASSWORD estén correctos");
            $this->warn("2. Asegúrate de usar una contraseña de aplicación de Gmail");
            $this->warn("3. Verifica que la verificación en 2 pasos esté habilitada en Gmail");
            $this->warn("4. Revisa que el puerto 587 esté abierto en tu servidor");
            
            return 1;
        }
        
        return 0;
    }
}
