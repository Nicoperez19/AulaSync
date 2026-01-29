<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

class SetupSingleTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:setup
                            {tenant : ID, domain o sede_id del tenant (ej: 1, th, LA)}
                            {--seed : Ejecutar seeders después de las migraciones}
                            {--fresh : Ejecutar migrate:fresh en lugar de migrate (¡CUIDADO! Se perderán todos los datos)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar migraciones y seeders para un tenant específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantIdentifier = $this->argument('tenant');
        
        // Buscar el tenant por ID, domain o sede_id
        $tenant = Tenant::where('id', $tenantIdentifier)
            ->orWhere('domain', strtolower($tenantIdentifier))
            ->orWhere('sede_id', strtoupper($tenantIdentifier))
            ->first();

        if (!$tenant) {
            $this->error("No se encontró el tenant: {$tenantIdentifier}");
            $this->info('Tenants disponibles:');
            Tenant::all()->each(function ($t) {
                $this->line("  - ID: {$t->id} | Domain: {$t->domain} | Sede: {$t->sede_id} | DB: {$t->database}");
            });
            return 1;
        }

        $this->info("=== Configuración de tenant: {$tenant->name} ===");
        $this->newLine();
        $this->processTenant($tenant);

        return 0;
    }

    /**
     * Procesar un tenant específico
     */
    protected function processTenant(Tenant $tenant)
    {
        $dbName = $tenant->database;

        $this->info("Tenant: {$tenant->name}");
        $this->line("  Domain: {$tenant->domain}");
        $this->line("  Sede ID: {$tenant->sede_id}");
        $this->line("  Database: {$dbName}");
        $this->line("  Usuario BD: " . config('database.connections.tenant.username'));
        $this->newLine();

        if ($this->option('fresh')) {
            if (!$this->confirm('⚠️  ¿Estás seguro de que quieres ejecutar migrate:fresh? Se perderán todos los datos de las tablas.', false)) {
                $this->info('Operación cancelada.');
                return;
            }
        }

        try {
            // Configurar la conexión tenant para usar esta base de datos
            config(['database.connections.tenant.database' => $dbName]);
            
            // Si el tenant tiene un host específico, usarlo
            if ($tenant->database_host) {
                config(['database.connections.tenant.host' => $tenant->database_host]);
            }

            // Purgar conexión para forzar reconexión con la nueva base de datos
            app('db')->purge('tenant');

            // Verificar conexión a la base de datos
            try {
                DB::connection('tenant')->getPdo();
                $this->line("  ✓ Conexión establecida a {$dbName}");
            } catch (\Exception $e) {
                throw new \Exception("No se pudo conectar a la base de datos '{$dbName}'. Asegúrate de que existe y las credenciales son correctas. Error: " . $e->getMessage());
            }

            // Ejecutar migraciones
            $migrateCommand = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
            $this->line("  Ejecutando {$migrateCommand}...");

            $exitCode = Artisan::call($migrateCommand, [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                $this->error("  Error ejecutando migraciones (exit code: {$exitCode})");
                $output = Artisan::output();
                $this->line($output);
                throw new \Exception("Migraciones fallaron: {$output}");
            } else {
                $this->line("  ✓ Migraciones completadas exitosamente");
            }

            // Ejecutar seeders si se solicita
            if ($this->option('seed')) {
                $this->line("  Ejecutando seeders para {$tenant->sede_id}...");

                // Hacer el tenant actual y bindearlo en el container
                $tenant->makeCurrent();
                app()->instance('tenant', $tenant);

                $exitCode = Artisan::call('db:seed', [
                    '--class' => 'TenantDatabaseSeeder',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);

                if ($exitCode !== 0) {
                    $this->warn("  ⚠ Seeders completados con advertencias");
                    $this->line(Artisan::output());
                } else {
                    $this->line("  ✓ Seeders completados exitosamente");
                }
            }

            $this->newLine();
            $this->info("✅ {$tenant->name} configurado correctamente");

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("❌ Error procesando {$tenant->name}: " . $e->getMessage());
        }
    }
}
