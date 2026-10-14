<?php

namespace App\Console\Commands;

use App\Mail\InconsistenciaSistema;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificarEstadoSistemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sistema:verificar-estado {--demo : Modo demo para probar envío de correos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado actual de espacios y reservas para detectar inconsistencias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE ESTADO DEL SISTEMA ===');
        $this->info('Fecha y hora: '.Carbon::now()->format('Y-m-d H:i:s'));

        if ($this->option('demo')) {
            $this->warn('🔧 MODO DEMO ACTIVADO - Simulando inconsistencias');
            $this->processDemoMode();

            return 0;
        }

        // Obtener todos los tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants configurados.');

            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        return 0;
    }

    protected function processDemoMode()
    {
        $this->newLine();

        // 1. Estado de espacios
        $this->info('📍 ESTADO DE ESPACIOS:');
        $this->line('   • Disponibles: 25 (DEMO)');
        $this->line('   • Ocupados: 4 (DEMO)');
        $this->line('   • En mantenimiento: 0 (DEMO)');
        $this->line('   • Total: 29 (DEMO)');
        $this->newLine();

        // 2. Estado de reservas
        $this->info('📅 ESTADO DE RESERVAS:');
        $this->line('   • Activas: 5 (DEMO)');
        $this->line('   • Finalizadas: 15 (DEMO)');
        $this->line('   • Hoy: 3 (DEMO)');
        $this->line('   • Total: 20 (DEMO)');
    }

    protected function processTenant(Tenant $tenant)
    {
        $this->newLine();
        $this->info("=== Verificando tenant: {$tenant->name} ({$tenant->domain}) ===");

        try {
            // Configurar conexión de tenant
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            // 1. Estado de espacios
            $this->info('📍 ESTADO DE ESPACIOS:');
            $espaciosDisponibles = Espacio::on('tenant')->where('estado', 'disponible')->count();
            $espaciosOcupados = Espacio::on('tenant')->where('estado', 'Ocupado')->count();
            $espaciosMantenimiento = Espacio::on('tenant')->where('estado', 'mantenimiento')->count();
            $totalEspacios = Espacio::on('tenant')->count();

            $this->line("   • Disponibles: {$espaciosDisponibles}");
            $this->line("   • Ocupados: {$espaciosOcupados}");
            $this->line("   • En mantenimiento: {$espaciosMantenimiento}");
            $this->line("   • Total: {$totalEspacios}");
            $this->newLine();

            // 2. Estado de reservas
            $this->info('📅 ESTADO DE RESERVAS:');
            $reservasActivas = Reserva::on('tenant')->where('estado', 'activa')->count();
            $reservasFinalizadas = Reserva::on('tenant')->where('estado', 'finalizada')->count();
            $reservasHoy = Reserva::on('tenant')->whereDate('fecha_reserva', Carbon::today())->count();
            $totalReservas = Reserva::on('tenant')->count();

            $this->line("   • Activas: {$reservasActivas}");
            $this->line("   • Finalizadas: {$reservasFinalizadas}");
            $this->line("   • Hoy: {$reservasHoy}");
            $this->line("   • Total: {$totalReservas}");
            $this->newLine();

            // 3. Detectar inconsistencias
            $this->info('🔍 DETECCIÓN DE INCONSISTENCIAS:');

            // Espacios ocupados sin reserva activa
            $espaciosOcupadosSinReserva = Espacio::on('tenant')
                ->where('estado', 'Ocupado')
                ->whereNotIn('id_espacio', function ($query) {
                    $query->select('id_espacio')
                        ->from('reservas')
                        ->where('estado', 'activa');
                })
                ->get();

            if ($espaciosOcupadosSinReserva->count() > 0) {
                $this->warn("   ⚠️  Espacios ocupados sin reserva activa: {$espaciosOcupadosSinReserva->count()}");
                foreach ($espaciosOcupadosSinReserva as $espacio) {
                    $this->line("      - {$espacio->id_espacio}: {$espacio->nombre_espacio}");
                }
            } else {
                $this->info('   ✅ No hay espacios ocupados sin reserva activa');
            }

            // Reservas activas en espacios disponibles
            $reservasActivasEnEspaciosDisponibles = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->whereHas('espacio', function ($query) {
                    $query->where('estado', 'disponible');
                })
                ->get();

            if ($reservasActivasEnEspaciosDisponibles->count() > 0) {
                $this->warn("   ⚠️  Reservas activas en espacios disponibles: {$reservasActivasEnEspaciosDisponibles->count()}");
                foreach ($reservasActivasEnEspaciosDisponibles as $reserva) {
                    $tipoUsuario = $reserva->run_profesor ? 'Profesor' : 'Solicitante';
                    $usuario = $reserva->run_profesor ?? $reserva->run_solicitante;
                    $this->line("      - Reserva {$reserva->id_reserva}: {$reserva->espacio->id_espacio} ({$tipoUsuario}: {$usuario})");
                }
            } else {
                $this->info('   ✅ No hay reservas activas en espacios disponibles');
            }

            // Reservas activas antiguas (más de 24 horas)
            $reservasAntiguas = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->where('fecha_reserva', '<', Carbon::today())
                ->get();

            if ($reservasAntiguas->count() > 0) {
                $this->warn("   ⚠️  Reservas activas de días anteriores: {$reservasAntiguas->count()}");
                foreach ($reservasAntiguas as $reserva) {
                    $tipoUsuario = $reserva->run_profesor ? 'Profesor' : 'Solicitante';
                    $usuario = $reserva->run_profesor ?? $reserva->run_solicitante;
                    $this->line("      - Reserva {$reserva->id_reserva}: {$reserva->fecha_reserva} ({$tipoUsuario}: {$usuario})");
                }
            } else {
                $this->info('   ✅ No hay reservas activas de días anteriores');
            }

            // Enviar correo si hay inconsistencias
            $this->enviarCorreoSiHayInconsistencias(
                $tenant,
                $espaciosOcupadosSinReserva,
                $reservasActivasEnEspaciosDisponibles,
                $reservasAntiguas
            );
        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: ".$e->getMessage());
            Log::error("Error en VerificarEstadoSistemaCommand para tenant {$tenant->name}", [
                'tenant' => $tenant->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Enviar correo de alerta si hay inconsistencias
     */
    private function enviarCorreoSiHayInconsistencias(Tenant $tenant, $espaciosOcupadosSinReserva, $reservasActivasEnEspaciosDisponibles, $reservasAntiguas)
    {
        $hayInconsistencias = $espaciosOcupadosSinReserva->count() > 0 ||
                             $reservasActivasEnEspaciosDisponibles->count() > 0 ||
                             $reservasAntiguas->count() > 0;

        if (! $hayInconsistencias) {
            $this->info('✅ No se detectaron inconsistencias - No se enviará correo');

            return;
        }

        try {
            // Preparar datos para el correo
            $datosInconsistencias = [
                'fecha_verificacion' => Carbon::now()->format('Y-m-d H:i:s'),
                'espacios_ocupados_sin_reserva' => $espaciosOcupadosSinReserva->map(function ($espacio) {
                    return [
                        'id_espacio' => is_object($espacio) ? $espacio->id_espacio : $espacio['id_espacio'],
                        'nombre_espacio' => is_object($espacio) ? $espacio->nombre_espacio : $espacio['nombre_espacio'],
                        'estado' => is_object($espacio) ? $espacio->estado : $espacio['estado'],
                    ];
                })->toArray(),
                'reservas_activas_espacios_disponibles' => $reservasActivasEnEspaciosDisponibles->map(function ($reserva) {
                    $reservaArray = is_array($reserva) ? $reserva : [
                        'id_reserva' => $reserva->id_reserva,
                        'espacio_id' => $reserva->id_espacio,
                        'espacio_nombre' => $reserva->espacio->nombre_espacio ?? 'N/A',
                        'tipo_usuario' => $reserva->run_profesor ? 'Profesor' : 'Solicitante',
                        'usuario' => $reserva->run_profesor ?? $reserva->run_solicitante,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora' => $reserva->hora,
                    ];

                    if (is_array($reserva)) {
                        return [
                            'id_reserva' => $reserva['id_reserva'],
                            'espacio_id' => $reserva['id_espacio'],
                            'espacio_nombre' => $reserva['espacio']['nombre_espacio'] ?? 'N/A',
                            'tipo_usuario' => $reserva['run_profesor'] ? 'Profesor' : 'Solicitante',
                            'usuario' => $reserva['run_profesor'] ?? $reserva['run_solicitante'],
                            'fecha_reserva' => $reserva['fecha_reserva'],
                            'hora' => $reserva['hora'],
                        ];
                    }

                    return $reservaArray;
                })->toArray(),
                'reservas_antiguas' => $reservasAntiguas->map(function ($reserva) {
                    if (is_array($reserva)) {
                        return [
                            'id_reserva' => $reserva['id_reserva'],
                            'espacio_id' => $reserva['id_espacio'],
                            'espacio_nombre' => $reserva['espacio']['nombre_espacio'] ?? 'N/A',
                            'tipo_usuario' => $reserva['run_profesor'] ? 'Profesor' : 'Solicitante',
                            'usuario' => $reserva['run_profesor'] ?? $reserva['run_solicitante'],
                            'fecha_reserva' => $reserva['fecha_reserva'],
                            'hora' => $reserva['hora'],
                        ];
                    }

                    return [
                        'id_reserva' => $reserva->id_reserva,
                        'espacio_id' => $reserva->id_espacio,
                        'espacio_nombre' => $reserva->espacio->nombre_espacio ?? 'N/A',
                        'tipo_usuario' => $reserva->run_profesor ? 'Profesor' : 'Solicitante',
                        'usuario' => $reserva->run_profesor ?? $reserva->run_solicitante,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora' => $reserva->hora,
                    ];
                })->toArray(),
                'estadisticas' => $this->option('demo') ? [
                    'total_espacios' => 29,
                    'espacios_disponibles' => 25,
                    'espacios_ocupados' => 4,
                    'reservas_activas' => 5,
                    'reservas_finalizadas' => 15,
                ] : [
                    'total_espacios' => Espacio::count(),
                    'espacios_disponibles' => Espacio::where('estado', 'disponible')->count(),
                    'espacios_ocupados' => Espacio::where('estado', 'Ocupado')->count(),
                    'reservas_activas' => Reserva::where('estado', 'activa')->count(),
                    'reservas_finalizadas' => Reserva::where('estado', 'finalizada')->count(),
                ],
            ];

            // Obtener correos de administradores desde configuración
            $correosAdmin = config('mail.admin_emails', ['admin@aulasync.com']);

            // Si no hay configuración, intentar obtener correos de usuarios admin
            if ($correosAdmin === ['admin@aulasync.com']) {
                $admins = User::role('admin')->pluck('email')->toArray();
                if (! empty($admins)) {
                    $correosAdmin = $admins;
                }
            }

            // Enviar correo a cada administrador
            foreach ($correosAdmin as $correoAdmin) {
                Mail::to($correoAdmin)->send(new InconsistenciaSistema($datosInconsistencias));
            }

            $this->warn('📧 Se envió alerta por inconsistencias a: '.implode(', ', $correosAdmin));

        } catch (\Exception $e) {
            $this->error('❌ Error al enviar correo de inconsistencias: '.$e->getMessage());
            Log::error('Error enviando correo de inconsistencias: '.$e->getMessage(), [
                'espacios_inconsistentes' => $espaciosOcupadosSinReserva->count(),
                'reservas_inconsistentes' => $reservasActivasEnEspaciosDisponibles->count(),
                'reservas_antiguas' => $reservasAntiguas->count(),
            ]);
        }
    }
}
