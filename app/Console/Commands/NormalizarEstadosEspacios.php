<?php

namespace App\Console\Commands;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NormalizarEstadosEspacios extends Command
{
    protected $signature = 'espacios:normalizar-estados {--tenant=* : IDs de tenants a normalizar (vacío = todos)}';

    protected $description = 'Normaliza todos los estados de espacios y reservas a minúsculas en la base de datos';

    public function handle()
    {
        $this->info('=== NORMALIZANDO ESTADOS A MINÚSCULAS ===');

        $tenantIds = $this->option('tenant');

        if (empty($tenantIds)) {
            // Normalizar todos los tenants
            $tenants = Tenant::all();
        } else {
            // Normalizar solo los tenants especificados
            $tenants = Tenant::whereIn('id', $tenantIds)->get();
        }

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants a procesar.');

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

            // 1. Normalizar tabla 'espacios'
            $this->line("  📦 Normalizando tabla 'espacios'...");

            // Obtener todos los espacios con estados diferentes a minúsculas
            $espacios = Espacio::on('tenant')->get();
            $espaciosActualizados = 0;

            foreach ($espacios as $espacio) {
                $estadoAnterior = $espacio->estado;
                $estadoNormalizado = strtolower($estadoAnterior);

                if ($estadoAnterior !== $estadoNormalizado) {
                    $espacio->estado = $estadoNormalizado;
                    $espacio->save();
                    $espaciosActualizados++;

                    $this->line("    ✓ {$espacio->id_espacio}: '{$estadoAnterior}' → '{$estadoNormalizado}'");
                }
            }

            if ($espaciosActualizados === 0) {
                $this->info("    ✅ Todos los espacios ya están normalizados ({$espacios->count()} espacios)");
            } else {
                $this->info("    ✅ {$espaciosActualizados} espacios normalizados de {$espacios->count()} totales");
            }

            // 2. Normalizar tabla 'reservas' (campo 'estado')
            $this->line("  📋 Normalizando tabla 'reservas'...");

            $reservas = Reserva::on('tenant')->get();
            $reservasActualizadas = 0;

            foreach ($reservas as $reserva) {
                $estadoAnterior = $reserva->estado;
                $estadoNormalizado = strtolower($estadoAnterior);

                if ($estadoAnterior !== $estadoNormalizado) {
                    $reserva->estado = $estadoNormalizado;
                    $reserva->save();
                    $reservasActualizadas++;

                    $this->line("    ✓ {$reserva->id_reserva}: '{$estadoAnterior}' → '{$estadoNormalizado}'");
                }
            }

            if ($reservasActualizadas === 0) {
                $this->info("    ✅ Todas las reservas ya están normalizadas ({$reservas->count()} reservas)");
            } else {
                $this->info("    ✅ {$reservasActualizadas} reservas normalizadas de {$reservas->count()} totales");
            }

            Log::info('Normalización de estados completada', [
                'tenant' => $tenant->domain,
                'espacios_actualizados' => $espaciosActualizados,
                'reservas_actualizadas' => $reservasActualizadas,
            ]);

        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: ".$e->getMessage());
            Log::error("Error en NormalizarEstadosEspacios para tenant {$tenant->name}", [
                'tenant' => $tenant->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
