<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Tenant;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class FinalizarReservasExpiradas extends Command
{
    protected $signature = 'reservas:finalizar-expiradas';
    protected $description = 'Finaliza automáticamente las reservas de tipo clase al término de cada módulo';

    // Horarios de módulos por día
    private $horariosModulos = [
        'lunes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ],
        'martes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ],
        'miercoles' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ],
        'jueves' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ],
        'viernes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ]
    ];

    public function handle()
    {
        $this->info('=== FINALIZANDO RESERVAS EXPIRADAS ===');

        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        return 0;
    }

    protected function processTenant($tenant)
    {
        $this->info("\nProcesando tenant: {$tenant->name} ({$tenant->domain})");

        try {
            // Configurar conexión de tenant
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            $ahora = Carbon::now();
            $fechaHoy = $ahora->toDateString();
            $horaActual = $ahora->format('H:i:s');
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));

            // Normalizar día
            $mapaDias = [
                'lunes' => 'lunes',
                'martes' => 'martes',
                'miércoles' => 'miercoles',
                'miercoles' => 'miercoles',
                'jueves' => 'jueves',
                'viernes' => 'viernes',
                'sábado' => 'sabado',
                'sabado' => 'sabado'
            ];
            $diaKey = $mapaDias[$diaActual] ?? $diaActual;

            // Buscar TODAS las reservas activas de hoy
            $reservasActivas = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->where('fecha_reserva', $fechaHoy)
                ->get();

            $this->info("  Total de reservas activas encontradas: " . $reservasActivas->count());

            $finalizadas = 0;

            foreach ($reservasActivas as $reserva) {
                $debeFinalizar = false;
                $motivo = '';

                // 1. Verificar por hora_salida (Prioridad)
                if (!empty($reserva->hora_salida)) {
                    if ($reserva->hora_salida <= $horaActual) {
                        $debeFinalizar = true;
                        $motivo = "Hora de salida alcanzada ({$reserva->hora_salida})";
                    }
                }
                // 2. Fallback para clases sin hora_salida (usando módulos)
                elseif ($reserva->tipo_reserva === 'clase' && !empty($reserva->id_asignatura)) {
                    $horaFinModulo = $this->obtenerHoraFinClase($reserva, $diaKey);
                    if ($horaFinModulo && $horaFinModulo <= $horaActual) {
                        $debeFinalizar = true;
                        $motivo = "Término de módulo de clase ({$horaFinModulo})";
                    }
                }
                // 3. Fallback para reservas manuales antiguas (1 hora de duración por defecto)
                else {
                    $horaLimite = Carbon::parse($reserva->hora)->addHour()->format('H:i:s');
                    if ($horaLimite <= $horaActual) {
                        $debeFinalizar = true;
                        $motivo = "Tiempo límite excedido (1h desde inicio a las {$reserva->hora})";
                    }
                }

                if ($debeFinalizar) {
                    $this->finalizarReserva($reserva, $motivo);
                    $finalizadas++;
                }
            }

            $this->info("  Reservas finalizadas en este tenant: {$finalizadas}");

        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: " . $e->getMessage());
            Log::error("Error en FinalizarReservasExpiradas para tenant {$tenant->name}: " . $e->getMessage());
        }
    }

    protected function obtenerHoraFinClase($reserva, $diaKey)
    {
        try {
            $periodo = SemesterHelper::getCurrentPeriod();
            $planificaciones = Planificacion_Asignatura::on('tenant')
                ->where('id_asignatura', $reserva->id_asignatura)
                ->where('id_espacio', $reserva->id_espacio)
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get();

            if ($planificaciones->isEmpty())
                return null;

            $numMaxModulo = 0;
            foreach ($planificaciones as $plan) {
                $parts = explode('.', $plan->id_modulo);
                $num = isset($parts[1]) ? (int) $parts[1] : 0;
                if ($num > $numMaxModulo)
                    $numMaxModulo = $num;
            }

            return $this->horariosModulos[$diaKey][$numMaxModulo]['fin'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function finalizarReserva($reserva, $motivo)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $reserva->estado = 'finalizada';
            if (empty($reserva->hora_salida)) {
                $reserva->hora_salida = Carbon::now()->format('H:i:s');
            }

            $obs = $reserva->observaciones ?? '';
            $nuevaObs = "Finalizada automáticamente: {$motivo}.";
            $reserva->observaciones = $obs ? $obs . "\n" . $nuevaObs : $nuevaObs;
            $reserva->save();

            // Liberar el espacio inmediatamente
            $espacio = Espacio::on('tenant')->find($reserva->id_espacio);
            if ($espacio && $espacio->estado === 'Ocupado') {
                // Verificar si no hay otras reservas activas AHORA para este espacio
                $otraReserva = Reserva::on('tenant')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->where('estado', 'activa')
                    ->where('id_reserva', '!=', $reserva->id_reserva)
                    ->exists();

                if (!$otraReserva) {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                    $this->info("    Espacio {$espacio->nombre_espacio} liberado.");
                }
            }

            DB::connection('tenant')->commit();
            $this->info("  ✅ Reserva {$reserva->id_reserva} finalizada. Motivo: {$motivo}");
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al finalizar reserva {$reserva->id_reserva}: " . $e->getMessage());
            $this->error("  ❌ Error al finalizar reserva {$reserva->id_reserva}: " . $e->getMessage());
        }
    }
}

