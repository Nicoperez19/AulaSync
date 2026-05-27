<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

class SetupTenantDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:setup
                            {--seed : Ejecutar seeders después de las migraciones}
                            {--fresh : Ejecutar migrate:fresh en lugar de migrate (¡CUIDADO! Se perderán todos los datos)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar migraciones y seeders en las bases de datos de cada tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Configuración de bases de datos de tenants ===');
        $this->newLine();

        // Mostrar información de conexión
        $this->info('Conexión a BD:');
        $this->line('  Host: ' . config('database.connections.tenant.host'));
        $this->line('  Usuario: ' . config('database.connections.tenant.username'));
        $this->newLine();

        // Obtener todos los tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->error('No se encontraron tenants configurados.');
            $this->info('Por favor ejecuta primero: php artisan db:seed --class=CentralDatabaseSeeder');
            return 1;
        }

        $this->info("Se encontraron {$tenants->count()} tenants:");
        foreach ($tenants as $tenant) {
            $this->line("  - {$tenant->name} ({$tenant->domain}) -> {$tenant->database}");
        }
        $this->newLine();

        if ($this->option('fresh')) {
            if (!$this->confirm('⚠️  ¿Estás seguro de que quieres ejecutar migrate:fresh? Se perderán todos los datos de las tablas.', false)) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        $this->newLine();
        $this->info('✅ Proceso completado exitosamente');

        return 0;
    }

    /**
     * Procesar un tenant: conectar a su BD, ejecutar migraciones y seeders.
     * 
     * NOTA: Las operaciones se ejecutan usando la conexión 'tenant' definida en config/database.php
     * con las credenciales DB_USERNAME y DB_PASSWORD del archivo .env
     */
    protected function processTenant(Tenant $tenant)
    {
        $dbName = $tenant->database;

        $this->newLine();
        $this->info("Procesando tenant: {$tenant->name}");
        $this->line("  Database: {$dbName}");
        $this->line("  Usuario BD: " . config('database.connections.tenant.username'));

        try {
            // Configurar la conexión tenant para usar esta base de datos
            config(['database.connections.tenant.database' => $dbName]);

            // Purgar conexión para forzar reconexión con la nueva base de datos
            app('db')->purge('tenant');

            if ($this->option('fresh') && $exists) {
                $this->warn("  Eliminando database existente: {$dbName}");
                $adminDB->statement("DROP DATABASE `{$dbName}`");
                $exists = false;
            }

            if (!$exists) {
                $this->line("  Creando database: {$dbName}");
                $adminDB->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Otorgar permisos al usuario aulasync sobre la nueva base de datos
                // $adminDB->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO 'aulasync'@'%'");
                // Otorgar permisos al usuario gestoraulasit sobre la nueva base de datos
                // $adminDB->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO 'gestoraulasit'@'%'");
                // $adminDB->statement("FLUSH PRIVILEGES");
            } else {
                $this->line("  La database ya existe: {$dbName}");
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
                } else {
                    $this->line("  ✓ Seeders completados exitosamente");
                }
            }

            $this->info("  ✅ {$tenant->name} configurado correctamente");

        } catch (\Exception $e) {
            $this->error("  ❌ Error procesando {$tenant->name}: " . $e->getMessage());
        }
    }
}
