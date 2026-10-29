<?php

namespace App\Console\Commands;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Models\Tenant;
use App\Models\VetoSalaEstudio;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalizarReservasExpiradas extends Command
{
    protected $signature = 'reservas:finalizar-expiradas';

    protected $description = 'Finaliza automáticamente las reservas de tipo clase al término de cada módulo';

    /**
     * Margen máximo (en minutos) después de la hora de salida antes de auto-finalizar la reserva,
     * permitiendo que el docente registre su salida normalmente al terminar el módulo.
     */
    private const MARGEN_SALIDA_MINUTOS = 10;

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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'sabado' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
        ],
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
            // Establecer este tenant como el actual
            $tenant->makeCurrent();

            $ahora = Carbon::now();
            $fechaHoy = $ahora->toDateString();
            $horaActual = $ahora->format('H:i:s');
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
            $diaKey = ModulosHelper::normalizarDia($diaActual);

            // Buscar TODAS las reservas activas de hoy
            // Usamos withoutGlobalScopes() para asegurar que procesamos todo el tenant
            $reservasActivas = Reserva::withoutGlobalScopes()
                ->where('estado', 'activa')
                ->where('fecha_reserva', $fechaHoy)
                ->get();

            $this->info('  Total de reservas activas encontradas: '.$reservasActivas->count());

            $finalizadas = 0;

            foreach ($reservasActivas as $reserva) {
                // Salas de estudio: el espacio se libera ÚNICAMENTE cuando el alumno/encargado escanea el carnet.
                // Si excede las 2 horas (120 minutos) sin devolución, se aplica sanción automática de 1 semana.
                if ($reserva->esSalaEstudio()) {
                    $inicioStr = ($reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('Y-m-d') : $reserva->fecha_reserva) . ' ' . $reserva->hora;
                    $inicio = Carbon::parse($inicioStr);
                    $minutosTranscurridos = (int) $inicio->diffInMinutes($ahora, false);

                    if ($minutosTranscurridos > 120) {
                        $runResponsable = $reserva->run_solicitante ?? $reserva->run_profesor;
                        if ($runResponsable && !VetoSalaEstudio::estaVetado($runResponsable)) {
                            VetoSalaEstudio::aplicarVetoPorTiempoExcedido(
                                $runResponsable,
                                $reserva->id_reserva,
                                $minutosTranscurridos,
                                7
                            );
                            $this->warn("  ⚠️ Sanción automática de 1 semana aplicada a {$runResponsable} por exceder 2h en sala {$reserva->id_espacio} ({$minutosTranscurridos} min).");
                        }
                    }
                    // No finalizamos automáticamente la reserva para que no se suelte la sala sola
                    continue;
                }

                $debeFinalizar = false;
                $motivo = '';

                // 1. Verificar por hora_salida (Prioridad) con margen de gracia (10 min máx)
                if (!empty($reserva->hora_salida)) {
                    $horaSalidaConMargen = Carbon::parse($fechaHoy . ' ' . $reserva->hora_salida)
                        ->addMinutes(self::MARGEN_SALIDA_MINUTOS)
                        ->format('H:i:s');
                    if ($horaSalidaConMargen <= $horaActual) {
                        $debeFinalizar = true;
                        $motivo = "Hora de salida alcanzada ({$reserva->hora_salida} + " . self::MARGEN_SALIDA_MINUTOS . " min margen)";
                    }
                }
                // 2. Fallback para clases sin hora_salida (usando módulos) con margen de gracia
                elseif ($reserva->tipo_reserva === 'clase' && !empty($reserva->id_asignatura)) {
                    $horaFinModulo = $this->obtenerHoraFinClase($reserva, $diaKey);
                    if ($horaFinModulo) {
                        $horaFinConMargen = Carbon::parse($fechaHoy . ' ' . $horaFinModulo)
                            ->addMinutes(self::MARGEN_SALIDA_MINUTOS)
                            ->format('H:i:s');
                        if ($horaFinConMargen <= $horaActual) {
                            $debeFinalizar = true;
                            $motivo = "Término de módulo de clase ({$horaFinModulo} + " . self::MARGEN_SALIDA_MINUTOS . " min margen)";
                        }
                    }
                }
                // 3. Fallback para reservas manuales antiguas (1 hora de duración por defecto + margen)
                else {
                    $horaLimite = Carbon::parse($fechaHoy . ' ' . $reserva->hora)
                        ->addHour()
                        ->addMinutes(self::MARGEN_SALIDA_MINUTOS)
                        ->format('H:i:s');
                    if ($horaLimite <= $horaActual) {
                        $debeFinalizar = true;
                        $motivo = "Tiempo límite excedido (1h + " . self::MARGEN_SALIDA_MINUTOS . " min margen desde inicio a las {$reserva->hora})";
                    }
                }

                if ($debeFinalizar) {
                    // Si se calculó una hora de fin para la clase, usarla para la salida
                    $horaSalidaCalculada = null;
                    if ($reserva->tipo_reserva === 'clase' && ! empty($reserva->id_asignatura)) {
                        $horaSalidaCalculada = $this->obtenerHoraFinClase($reserva, $diaKey);
                    }
                    $this->finalizarReserva($reserva, $motivo, $horaSalidaCalculada);
                    $finalizadas++;
                }
            }

            $this->info("  Reservas finalizadas en este tenant: {$finalizadas}");

        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: ".$e->getMessage());
            Log::error("Error en FinalizarReservasExpiradas para tenant {$tenant->name}: ".$e->getMessage());
        }
    }

    protected function obtenerHoraFinClase($reserva, $diaKey)
    {
        try {
            $periodo = SemesterHelper::getCurrentPeriod();
            $espacioLimpio = str_replace(['-', ' '], '', $reserva->id_espacio);
            $runLimpio = preg_replace('/[^0-9kK]/', '', $reserva->run_profesor ?? '');

            $planificaciones = Planificacion_Asignatura::on('tenant')
                ->where(function ($q) use ($reserva, $espacioLimpio) {
                    $q->where('id_espacio', $reserva->id_espacio)
                      ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                })
                ->where(function ($q) use ($reserva, $runLimpio) {
                    $q->where('id_asignatura', $reserva->id_asignatura);
                    if (!empty($runLimpio)) {
                        $q->orWhereHas('asignatura', function ($aq) use ($runLimpio) {
                            $aq->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                        });
                    }
                })
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->whereHas('modulo', function ($q) use ($diaKey) {
                    $q->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(dia, 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u')) = ?", [$diaKey]);
                })
                ->get();

            if ($planificaciones->isEmpty()) {
                return null;
            }

            $modulosDia = [];
            foreach ($planificaciones as $plan) {
                $parts = explode('.', $plan->id_modulo);
                $num = isset($parts[1]) ? (int) $parts[1] : 0;
                if ($num > 0) {
                    $modulosDia[$num] = true;
                }
            }

            if (empty($modulosDia)) {
                return null;
            }

            // Si la reserva tiene modulo_inicio, encadenar desde ahí; si no, usar el máximo del bloque
            $moduloBase = $reserva->modulo_inicio ? (int) $reserva->modulo_inicio : min(array_keys($modulosDia));
            $numMaxModulo = $moduloBase;
            while (isset($modulosDia[$numMaxModulo + 1])) {
                $numMaxModulo++;
            }

            return $this->horariosModulos[$diaKey][$numMaxModulo]['fin'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function finalizarReserva($reserva, $motivo, $horaSalida = null)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $reserva->estado = 'finalizada';
            if (empty($reserva->hora_salida)) {
                $reserva->hora_salida = $horaSalida ?? Carbon::now()->format('H:i:s');
            }

            $obs = $reserva->observaciones ?? '';
            $nuevaObs = "Finalizada automáticamente: {$motivo}.";
            $reserva->observaciones = $obs ? $obs."\n".$nuevaObs : $nuevaObs;
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

                if (! $otraReserva) {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                    $this->info("    Espacio {$espacio->nombre_espacio} liberado.");
                }
            }

            DB::connection('tenant')->commit();
            $this->info("  ✅ Reserva {$reserva->id_reserva} finalizada. Motivo: {$motivo}");
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al finalizar reserva {$reserva->id_reserva}: ".$e->getMessage());
            $this->error("  ❌ Error al finalizar reserva {$reserva->id_reserva}: ".$e->getMessage());
        }
    }
}
