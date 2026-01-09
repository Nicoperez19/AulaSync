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

            // 1. Finalizar todas las reservas activas
            $reservasFinalizadas = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->update([
                    'estado' => 'finalizada',
                    'hora_salida' => Carbon::now()->format('H:i:s'),
                    'updated_at' => Carbon::now()
                ]);

            $this->line("  Se finalizaron {$reservasFinalizadas} reservas activas.");

            // 2. Cambiar todos los espacios ocupados a disponibles
            $espaciosLiberados = Espacio::on('tenant')
                ->where('estado', 'Ocupado')
                ->update([
                    'estado' => 'disponible',
                    'updated_at' => Carbon::now()
                ]);

            $this->line("  Se liberaron {$espaciosLiberados} espacios ocupados.");

            $this->info("  ✅ Proceso completado: {$reservasFinalizadas} reservas finalizadas + {$espaciosLiberados} espacios liberados");
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
