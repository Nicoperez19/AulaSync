<?php

namespace App\Console\Commands;

use App\Mail\ConfirmacionDevolucion;
use App\Mail\ConfirmacionReserva;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarCorreoReservaCommand extends Command
{
    protected $signature = 'reservas:enviar-correo
                            {id_reserva : ID de la reserva}
                            {--tipo=confirmacion : confirmacion o devolucion}
                            {--email= : Correo destinatario opcional}
                            {--tenant= : ID o base de datos del tenant}';

    protected $description = 'Envía en segundo plano el correo de confirmación o devolución de una reserva';

    public function handle(): int
    {
        $idReserva = (string) $this->argument('id_reserva');
        $tipo = (string) ($this->option('tipo') ?: 'confirmacion');
        $emailFallback = $this->option('email') ?: null;
        $tenantOpt = $this->option('tenant') ?: null;

        try {
            $reservaService = app(\App\Services\ReservaService::class);
            if (!$reservaService->smtpDisponibleParaEnvio()) {
                return self::SUCCESS;
            }

            // Configurar tenant si se proporcionó
            if ($tenantOpt) {
                $tenant = Tenant::where('id', $tenantOpt)
                    ->orWhere('database', $tenantOpt)
                    ->first();

                if ($tenant) {
                    $tenant->makeCurrent();
                    if ($tenant->database) {
                        config(['database.connections.tenant.database' => $tenant->database]);
                        app('db')->purge('tenant');
                    }
                } else {
                    config(['database.connections.tenant.database' => $tenantOpt]);
                    app('db')->purge('tenant');
                }
            }

            $reserva = Reserva::on('tenant')
                ->with(['profesor', 'solicitante', 'espacio', 'asignatura'])
                ->where('id_reserva', $idReserva)
                ->first();

            if (!$reserva) {
                return self::SUCCESS;
            }

            $destinatario = $this->resolverEmail($reserva) ?: $emailFallback;
            if (!$destinatario || !filter_var($destinatario, FILTER_VALIDATE_EMAIL) || str_ends_with($destinatario, '@temp.com')) {
                return self::SUCCESS;
            }

            // Limitar timeout de conexión SMTP para no colgar procesos en segundo plano
            config(['mail.mailers.smtp.timeout' => 5]);

            if ($tipo === 'devolucion') {
                Mail::to($destinatario)->send(new ConfirmacionDevolucion($reserva));
                Log::info("📧 Correo de devolución de reserva {$idReserva} enviado a {$destinatario}");
            } else {
                Mail::to($destinatario)->send(new ConfirmacionReserva($reserva));
                Log::info("📧 Comprobante de reserva {$idReserva} enviado a {$destinatario}");
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            app(\App\Services\ReservaService::class)->registrarFalloSmtp($msg);
            Log::warning("⚠️ No se pudo enviar el correo ({$tipo}) de la reserva {$idReserva}: {$msg}");
        }

        return self::SUCCESS;
    }

    private function resolverEmail(Reserva $reserva): ?string
    {
        if ($reserva->run_profesor) {
            $profesor = $reserva->relationLoaded('profesor') && $reserva->profesor
                ? $reserva->profesor
                : Profesor::on('tenant')->where('run_profesor', $reserva->run_profesor)->first();
            if ($profesor && !empty($profesor->email)) {
                return $profesor->email;
            }
        }

        if ($reserva->run_solicitante) {
            $solicitante = $reserva->relationLoaded('solicitante') && $reserva->solicitante
                ? $reserva->solicitante
                : Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
            if ($solicitante && !empty($solicitante->correo)) {
                return $solicitante->correo;
            }
        }

        return null;
    }
}
