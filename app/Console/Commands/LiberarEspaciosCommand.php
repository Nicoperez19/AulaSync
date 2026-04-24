<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Libera todos los espacios ocupados y finaliza las reservas activas a las 12 de la noche';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de liberación de espacios y reservas...');

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

    protected function processTenant(Tenant $tenant)
    {
        $this->info("\nProcesando tenant: {$tenant->name} ({$tenant->domain})");

        try {
            // Configurar conexión de tenant
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            // 1. Finalizar todas las reservas activas o programadas del día (o anteriores)
            $reservasActualizadas = Reserva::on('tenant')
                ->whereIn('estado', ['activa', 'programada'])
                ->where('fecha_reserva', '<=', Carbon::now()->toDateString())
                ->get();

            $totalReservas = 0;
            foreach ($reservasActualizadas as $reserva) {
                $motivo = $reserva->estado === 'activa' 
                    ? 'Reserva finalizada automáticamente por reset diario (posible olvido de check-out).' 
                    : 'Reserva cancelada automáticamente por reset diario (el usuario no asistió).';

                $reserva->update([
                    'estado' => 'finalizada',
                    'hora_salida' => $reserva->estado === 'activa' ? Carbon::now()->format('H:i:s') : $reserva->hora_salida,
                    'observaciones' => trim(($reserva->observaciones ?? '') . "\n" . $motivo),
                    'updated_at' => Carbon::now()
                ]);
                $totalReservas++;
            }

            $this->line("  Se finalizaron {$totalReservas} reservas (activas/programadas).");

            // 2. Cambiar todos los espacios ocupados o reservados a disponibles
            $espaciosLiberados = Espacio::on('tenant')
                ->whereIn('estado', ['Ocupado', 'Reservado'])
                ->update([
                    'estado' => 'Disponible',
                    'updated_at' => Carbon::now()
                ]);

            $this->line("  Se liberaron {$espaciosLiberados} espacios (Ocupados/Reservados).");

            $this->info("  ✅ Proceso completado: {$totalReservas} reservas procesadas + {$espaciosLiberados} espacios liberados");
        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: " . $e->getMessage());
            Log::error("Error en LiberarEspaciosCommand para tenant {$tenant->name}", [
                'tenant' => $tenant->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
