<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InconsistenciaSistema;

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
        $this->info('Fecha y hora: ' . Carbon::now()->format('Y-m-d H:i:s'));
        
        if ($this->option('demo')) {
            $this->warn('🔧 MODO DEMO ACTIVADO - Simulando inconsistencias');
        }
        
        $this->newLine();

        // 1. Estado de espacios
        $this->info('📍 ESTADO DE ESPACIOS:');
        if ($this->option('demo')) {
            $this->line("   • Disponibles: 25 (DEMO)");
            $this->line("   • Ocupados: 4 (DEMO)");
            $this->line("   • En mantenimiento: 0 (DEMO)");
            $this->line("   • Total: 29 (DEMO)");
        } else {
            $espaciosDisponibles = Espacio::where('estado', 'disponible')->count();
            $espaciosOcupados = Espacio::where('estado', 'Ocupado')->count();
            $espaciosMantenimiento = Espacio::where('estado', 'mantenimiento')->count();
            $totalEspacios = Espacio::count();

            $this->line("   • Disponibles: {$espaciosDisponibles}");
            $this->line("   • Ocupados: {$espaciosOcupados}");
            $this->line("   • En mantenimiento: {$espaciosMantenimiento}");
            $this->line("   • Total: {$totalEspacios}");
        }
        $this->newLine();

        // 2. Estado de reservas
        $this->info('📅 ESTADO DE RESERVAS:');
        if ($this->option('demo')) {
            $this->line("   • Activas: 5 (DEMO)");
            $this->line("   • Finalizadas: 15 (DEMO)");
            $this->line("   • Hoy: 3 (DEMO)");
            $this->line("   • Total: 20 (DEMO)");
        } else {
            $reservasActivas = Reserva::where('estado', 'activa')->count();
            $reservasFinalizadas = Reserva::where('estado', 'finalizada')->count();
            $reservasHoy = Reserva::whereDate('fecha_reserva', Carbon::today())->count();
            $totalReservas = Reserva::count();

            $this->line("   • Activas: {$reservasActivas}");
            $this->line("   • Finalizadas: {$reservasFinalizadas}");
            $this->line("   • Hoy: {$reservasHoy}");
            $this->line("   • Total: {$totalReservas}");
        }
        $this->newLine();

        // 3. Detectar inconsistencias
        $this->info('🔍 DETECCIÓN DE INCONSISTENCIAS:');
        
        if ($this->option('demo')) {
            // Simular inconsistencias para modo demo
            $espaciosOcupadosSinReserva = collect([
                ['id_espacio' => 'TH-DEMO1', 'nombre_espacio' => 'Taller Demo 1', 'estado' => 'Ocupado'],
                ['id_espacio' => 'TH-DEMO2', 'nombre_espacio' => 'Taller Demo 2', 'estado' => 'Ocupado']
            ]);
            
            $reservasActivasEnEspaciosDisponibles = collect([
                [
                    'id_reserva' => 'RDEMO001',
                    'id_espacio' => 'TH-DEMO3',
                    'run_profesor' => '12345678',
                    'run_solicitante' => null,
                    'fecha_reserva' => Carbon::now()->format('Y-m-d'),
                    'hora' => '10:00:00',
                    'espacio' => ['id_espacio' => 'TH-DEMO3', 'nombre_espacio' => 'Taller Demo 3']
                ]
            ]);
            
            $reservasAntiguas = collect([
                [
                    'id_reserva' => 'RDEMO002',
                    'id_espacio' => 'TH-DEMO4',
                    'run_profesor' => null,
                    'run_solicitante' => 'SOL001',
                    'fecha_reserva' => Carbon::yesterday()->format('Y-m-d'),
                    'hora' => '14:00:00',
                    'espacio' => ['id_espacio' => 'TH-DEMO4', 'nombre_espacio' => 'Taller Demo 4']
                ]
            ]);
            
            $this->warn("   ⚠️  [DEMO] Espacios ocupados sin reserva activa: {$espaciosOcupadosSinReserva->count()}");
            $this->warn("   ⚠️  [DEMO] Reservas activas en espacios disponibles: {$reservasActivasEnEspaciosDisponibles->count()}");
            $this->warn("   ⚠️  [DEMO] Reservas activas de días anteriores: {$reservasAntiguas->count()}");
            
        } else {
            // Modo normal
            $espaciosOcupadosSinReserva = Espacio::where('estado', 'Ocupado')
                ->whereNotIn('id_espacio', function($query) {
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
                $this->info("   ✅ No hay espacios ocupados sin reserva activa");
            }

            // Reservas activas en espacios disponibles
            $reservasActivasEnEspaciosDisponibles = Reserva::where('estado', 'activa')
                ->whereHas('espacio', function($query) {
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
                $this->info("   ✅ No hay reservas activas en espacios disponibles");
            }

            // Reservas activas antiguas (más de 24 horas)
            $reservasAntiguas = Reserva::where('estado', 'activa')
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
                $this->info("   ✅ No hay reservas activas de días anteriores");
            }
        }

        $this->newLine();
        $this->info('=== FIN DE VERIFICACIÓN ===');

        // Enviar correo si hay inconsistencias
        $this->enviarCorreoSiHayInconsistencias(
            $espaciosOcupadosSinReserva,
            $reservasActivasEnEspaciosDisponibles,
            $reservasAntiguas
        );

        return 0;
    }

    /**
     * Enviar correo de alerta si hay inconsistencias
     */
    private function enviarCorreoSiHayInconsistencias($espaciosOcupadosSinReserva, $reservasActivasEnEspaciosDisponibles, $reservasAntiguas)
    {
        $hayInconsistencias = $espaciosOcupadosSinReserva->count() > 0 || 
                             $reservasActivasEnEspaciosDisponibles->count() > 0 || 
                             $reservasAntiguas->count() > 0;

        if (!$hayInconsistencias) {
            $this->info('✅ No se detectaron inconsistencias - No se enviará correo');
            return;
        }

        try {
            // Preparar datos para el correo
            $datosInconsistencias = [
                'fecha_verificacion' => Carbon::now()->format('Y-m-d H:i:s'),
                'espacios_ocupados_sin_reserva' => $espaciosOcupadosSinReserva->map(function($espacio) {
                    return [
                        'id_espacio' => is_object($espacio) ? $espacio->id_espacio : $espacio['id_espacio'],
                        'nombre_espacio' => is_object($espacio) ? $espacio->nombre_espacio : $espacio['nombre_espacio'],
                        'estado' => is_object($espacio) ? $espacio->estado : $espacio['estado']
                    ];
                })->toArray(),
                'reservas_activas_espacios_disponibles' => $reservasActivasEnEspaciosDisponibles->map(function($reserva) {
                    $reservaArray = is_array($reserva) ? $reserva : [
                        'id_reserva' => $reserva->id_reserva,
                        'espacio_id' => $reserva->id_espacio,
                        'espacio_nombre' => $reserva->espacio->nombre_espacio ?? 'N/A',
                        'tipo_usuario' => $reserva->run_profesor ? 'Profesor' : 'Solicitante',
                        'usuario' => $reserva->run_profesor ?? $reserva->run_solicitante,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora' => $reserva->hora
                    ];
                    
                    if (is_array($reserva)) {
                        return [
                            'id_reserva' => $reserva['id_reserva'],
                            'espacio_id' => $reserva['id_espacio'],
                            'espacio_nombre' => $reserva['espacio']['nombre_espacio'] ?? 'N/A',
                            'tipo_usuario' => $reserva['run_profesor'] ? 'Profesor' : 'Solicitante',
                            'usuario' => $reserva['run_profesor'] ?? $reserva['run_solicitante'],
                            'fecha_reserva' => $reserva['fecha_reserva'],
                            'hora' => $reserva['hora']
                        ];
                    }
                    
                    return $reservaArray;
                })->toArray(),
                'reservas_antiguas' => $reservasAntiguas->map(function($reserva) {
                    if (is_array($reserva)) {
                        return [
                            'id_reserva' => $reserva['id_reserva'],
                            'espacio_id' => $reserva['id_espacio'],
                            'espacio_nombre' => $reserva['espacio']['nombre_espacio'] ?? 'N/A',
                            'tipo_usuario' => $reserva['run_profesor'] ? 'Profesor' : 'Solicitante',
                            'usuario' => $reserva['run_profesor'] ?? $reserva['run_solicitante'],
                            'fecha_reserva' => $reserva['fecha_reserva'],
                            'hora' => $reserva['hora']
                        ];
                    }
                    
                    return [
                        'id_reserva' => $reserva->id_reserva,
                        'espacio_id' => $reserva->id_espacio,
                        'espacio_nombre' => $reserva->espacio->nombre_espacio ?? 'N/A',
                        'tipo_usuario' => $reserva->run_profesor ? 'Profesor' : 'Solicitante',
                        'usuario' => $reserva->run_profesor ?? $reserva->run_solicitante,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora' => $reserva->hora
                    ];
                })->toArray(),
                'estadisticas' => $this->option('demo') ? [
                    'total_espacios' => 29,
                    'espacios_disponibles' => 25,
                    'espacios_ocupados' => 4,
                    'reservas_activas' => 5,
                    'reservas_finalizadas' => 15
                ] : [
                    'total_espacios' => Espacio::count(),
                    'espacios_disponibles' => Espacio::where('estado', 'disponible')->count(),
                    'espacios_ocupados' => Espacio::where('estado', 'Ocupado')->count(),
                    'reservas_activas' => Reserva::where('estado', 'activa')->count(),
                    'reservas_finalizadas' => Reserva::where('estado', 'finalizada')->count()
                ]
            ];

            // Obtener correos de administradores desde configuración
            $correosAdmin = config('mail.admin_emails', ['admin@aulasync.com']);
            
            // Si no hay configuración, intentar obtener correos de usuarios admin
            if ($correosAdmin === ['admin@aulasync.com']) {
                $admins = \App\Models\User::role('admin')->pluck('email')->toArray();
                if (!empty($admins)) {
                    $correosAdmin = $admins;
                }
            }

            // Enviar correo a cada administrador
            foreach ($correosAdmin as $correoAdmin) {
                Mail::to($correoAdmin)->send(new InconsistenciaSistema($datosInconsistencias));
            }

            $this->warn("📧 Se envió alerta por inconsistencias a: " . implode(', ', $correosAdmin));
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo de inconsistencias: " . $e->getMessage());
            Log::error('Error enviando correo de inconsistencias: ' . $e->getMessage(), [
                'espacios_inconsistentes' => $espaciosOcupadosSinReserva->count(),
                'reservas_inconsistentes' => $reservasActivasEnEspaciosDisponibles->count(),
                'reservas_antiguas' => $reservasAntiguas->count()
            ]);
        }
    }
}
