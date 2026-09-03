<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\LlaveNoDevuelta;
use App\Models\Reserva;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LiberarEspaciosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espacios:liberar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera todos los espacios ocupados al cierre del día y registra las llaves no devueltas';

    /**
     * Execute the console command.
     */
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
            $tenant->makeCurrent();

            $ahora        = Carbon::now();
            // Si corre de madrugada (00:00 - 05:59), limpia hasta el día de ayer.
            // Si se ejecuta tarde en la noche (ej. 23:00), limpia hasta el día de hoy.
            $fechaLimite  = ($ahora->hour < 6) ? $ahora->copy()->subDay()->toDateString() : $ahora->toDateString();
            $cerradoEn    = $ahora;

            // ── 1. Reservas ACTIVAS de días anteriores (llave no devuelta / sin cierre) ──
            $reservasActivas = Reserva::withoutGlobalScopes()
                ->where('estado', 'activa')
                ->where('fecha_reserva', '<=', $fechaLimite)
                ->get();

            foreach ($reservasActivas as $reserva) {
                // Registrar en la tabla de trazabilidad (sin logs)
                LlaveNoDevuelta::create([
                    'id_reserva'           => $reserva->id_reserva,
                    'id_espacio'           => $reserva->id_espacio,
                    'run_profesor'         => $reserva->run_profesor ?? $reserva->run_solicitante,
                    'id_asignatura'        => $reserva->id_asignatura,
                    'fecha_clase'          => $reserva->fecha_reserva,
                    'hora_entrada'         => $reserva->hora,
                    'hora_termino_esperada'=> $reserva->hora_salida,
                    'cerrado_en'           => $cerradoEn,
                ]);

                // Cerrar la reserva con observación clara
                $usuarioTipo = $reserva->run_profesor ? 'Profesor ' . $reserva->run_profesor : 'Solicitante ' . ($reserva->run_solicitante ?? 'Desconocido');
                $reserva->update([
                    'estado'        => 'finalizada',
                    'hora_salida'   => $reserva->hora_salida ?? '23:59:59',
                    'observaciones' => trim(
                        ($reserva->observaciones ?? '') .
                        "\n⚠️ Llave no devuelta — Espacio liberado automáticamente al cierre del día {$reserva->fecha_reserva} ({$usuarioTipo})."
                    ),
                    'updated_at'    => $ahora,
                ]);
            }

            // ── 2. Reservas PROGRAMADAS sin uso de días anteriores (no se presentó) ──
            $reservasProgramadas = Reserva::withoutGlobalScopes()
                ->whereIn('estado', ['programada'])
                ->where('fecha_reserva', '<=', $fechaLimite)
                ->get();

            foreach ($reservasProgramadas as $reserva) {
                $reserva->update([
                    'estado'        => 'finalizada',
                    'observaciones' => trim(
                        ($reserva->observaciones ?? '') .
                        "\nReserva cancelada automáticamente — usuario no se presentó ({$reserva->fecha_reserva})."
                    ),
                    'updated_at'    => $ahora,
                ]);
            }

            // ── 3. Liberar espacios físicos que quedaron en estado Ocupado/Reservado ──
            Espacio::withoutGlobalScopes()
                ->whereIn('estado', ['Ocupado', 'Reservado'])
                ->update([
                    'estado'     => 'Disponible',
                    'updated_at' => $ahora,
                ]);

        } catch (\Exception $e) {
            // Solo mostramos en consola, nunca en logs de archivo
            $this->error("Error procesando tenant {$tenant->name}: " . $e->getMessage());
        }
    }
}
