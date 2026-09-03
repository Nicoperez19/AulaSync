<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LlaveNoDevuelta;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FinalizarReservasNoDevueltas extends Command
{
    protected $signature = 'reservas:finalizar-no-devueltas';

    protected $description = 'Finaliza reservas sin devolución de llave tras 1 h del módulo; también cierra reservas activas de días anteriores';

    public function handle()
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        return 0;
    }

    protected function processTenant(Tenant $tenant)
    {
        try {
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            $ahora = Carbon::now();
            $hoy   = $ahora->toDateString();

            // ── A. Reservas activas de DÍAS ANTERIORES (el comando nocturno falló) ──────
            $reservasAnteriores = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->whereNotNull('run_profesor')
                ->whereNull('hora_salida')
                ->where('fecha_reserva', '<', $hoy)
                ->get();

            foreach ($reservasAnteriores as $reserva) {
                // Registrar en trazabilidad sin logs
                LlaveNoDevuelta::create([
                    'id_reserva'            => $reserva->id_reserva,
                    'id_espacio'            => $reserva->id_espacio,
                    'run_profesor'          => $reserva->run_profesor,
                    'id_asignatura'         => $reserva->id_asignatura,
                    'fecha_clase'           => $reserva->fecha_reserva,
                    'hora_entrada'          => $reserva->hora,
                    'hora_termino_esperada' => null,
                    'cerrado_en'            => $ahora,
                ]);

                $reserva->update([
                    'estado'        => 'finalizada',
                    'hora_salida'   => '23:59:59',
                    'observaciones' => trim(
                        ($reserva->observaciones ?? '') .
                        "\n⚠️ Llave no devuelta — Espacio liberado automáticamente (detección tardía) el {$ahora->toDateString()}."
                    ),
                    'updated_at'    => $ahora,
                ]);

                // Liberar el espacio si sigue marcado como Ocupado
                Espacio::on('tenant')
                    ->where('id_espacio', $reserva->id_espacio)
                    ->where('estado', 'Ocupado')
                    ->update(['estado' => 'Disponible', 'updated_at' => $ahora]);
            }

            // ── B. Reservas activas de HOY que superaron la hora de gracia (1 h tras el módulo) ─
            $reservasHoy = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->where(function ($q) {
                    $q->whereNotNull('run_profesor')
                      ->orWhereNotNull('run_solicitante');
                })
                ->whereNull('hora_salida')
                ->where('fecha_reserva', $hoy)
                ->get();

            foreach ($reservasHoy as $reserva) {
                $horaTerminoStr = null;

                // 1. Intentar obtener término de la planificación específica de la asignatura y espacio
                if ($reserva->id_asignatura) {
                    $planificaciones = Planificacion_Asignatura::on('tenant')
                        ->where('id_asignatura', $reserva->id_asignatura)
                        ->where('id_espacio', $reserva->id_espacio)
                        ->with('modulo')
                        ->get();

                    if ($planificaciones->isNotEmpty()) {
                        $horaTerminoStr = $planificaciones
                            ->filter(fn($p) => !empty($p->modulo?->hora_termino))
                            ->map(fn($p) => $p->modulo->hora_termino)
                            ->max();
                    }
                }

                // 2. Fallback: calcular según hora de entrada + módulos (50 min por módulo) o 1 hora por defecto
                if (!$horaTerminoStr && $reserva->hora) {
                    $duracionMinutos = 50 * ($reserva->modulos ?? 1);
                    $horaTerminoStr = Carbon::parse($hoy . ' ' . $reserva->hora)->addMinutes($duracionMinutos)->format('H:i:s');
                }

                if (!$horaTerminoStr) {
                    continue;
                }

                $horaTermino = Carbon::parse($hoy . ' ' . $horaTerminoStr);
                $horaLimite  = $horaTermino->copy()->addHour();

                if ($ahora->gte($horaLimite)) {
                    $usuarioLabel = $reserva->run_profesor ? "Profesor {$reserva->run_profesor}" : "Solicitante {$reserva->run_solicitante}";

                    $reserva->update([
                        'estado'        => 'finalizada',
                        'hora_salida'   => $horaLimite->format('H:i:s'),
                        'observaciones' => trim(
                            ($reserva->observaciones ?? '') .
                            "\n⚠️ Llave no devuelta — Espacio liberado automáticamente 1 h después del término del módulo ({$horaTermino->format('H:i')}) [{$usuarioLabel}]."
                        ),
                        'updated_at'    => $ahora,
                    ]);

                    // Registrar en trazabilidad sin logs
                    LlaveNoDevuelta::create([
                        'id_reserva'            => $reserva->id_reserva,
                        'id_espacio'            => $reserva->id_espacio,
                        'run_profesor'          => $reserva->run_profesor ?? $reserva->run_solicitante,
                        'id_asignatura'         => $reserva->id_asignatura,
                        'fecha_clase'           => $reserva->fecha_reserva,
                        'hora_entrada'          => $reserva->hora,
                        'hora_termino_esperada' => $horaTermino->format('H:i:s'),
                        'cerrado_en'            => $ahora,
                    ]);

                    // Liberar el espacio si no quedan otras reservas activas AHORA
                    $otraReserva = Reserva::on('tenant')
                        ->where('id_espacio', $reserva->id_espacio)
                        ->where('estado', 'activa')
                        ->where('id_reserva', '!=', $reserva->id_reserva)
                        ->exists();

                    if (!$otraReserva) {
                        Espacio::on('tenant')
                            ->where('id_espacio', $reserva->id_espacio)
                            ->where('estado', 'Ocupado')
                            ->update(['estado' => 'Disponible', 'updated_at' => $ahora]);
                    }
                }
            }

        } catch (\Exception $e) {
            $this->error("Error procesando tenant {$tenant->name}: " . $e->getMessage());
        }
    }

    private function obtenerDiaSemana(string $fecha): string
    {
        $dias = [
            'Sunday'    => 'Domingo',
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
        ];
        return $dias[Carbon::parse($fecha)->format('l')] ?? 'Desconocido';
    }
}
