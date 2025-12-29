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
                            {--seed : Ejecutar seeders después de crear las databases}
                            {--fresh : Eliminar y recrear las databases (¡CUIDADO! Se perderán todos los datos)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear bases de datos para cada tenant configurado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Configuración de bases de datos de tenants ===');
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
            if (!$this->confirm('⚠️  ¿Estás seguro de que quieres ELIMINAR todas las databases de tenants? Se perderán todos los datos.', false)) {
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

    protected function processTenant(Tenant $tenant)
    {
        $dbName = $tenant->database;

        $this->newLine();
        $this->info("Procesando tenant: {$tenant->name}");
        $this->line("  Database: {$dbName}");

        try {
            // Usar conexión administrativa (root) para operaciones DDL
            $adminDB = DB::connection('tenant-admin');
            
            // Verificar si la database existe (compatible con MariaDB)
            $databases = $adminDB->select("SHOW DATABASES WHERE `Database` = '{$dbName}'");
            $exists = !empty($databases);

            if ($this->option('fresh') && $exists) {
                $this->warn("  Eliminando database existente: {$dbName}");
                $adminDB->statement("DROP DATABASE `{$dbName}`");
                $exists = false;
            }

            if (!$exists) {
                $this->line("  Creando database: {$dbName}");
                $adminDB->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Otorgar permisos al usuario aulasync sobre la nueva base de datos
                $adminDB->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO 'aulasync'@'%'");
                // Otorgar permisos al usuario gestoraulasit sobre la nueva base de datos
                $adminDB->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO 'gestoraulasit'@'%'");
                $adminDB->statement("FLUSH PRIVILEGES");
            } else {
                $this->line("  La database ya existe: {$dbName}");
            }

            // Ejecutar migraciones
            $this->line("  Ejecutando migraciones...");
            config(['database.connections.tenant.database' => $dbName]);
            
            // Purge connection to force reconnect with new database
            app('db')->purge('tenant');
            
            $exitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
            
            if ($exitCode !== 0) {
                $this->error("  Error ejecutando migraciones (exit code: {$exitCode})");
                $output = Artisan::output();
                $this->line($output);
                throw new \Exception("Migraciones fallaron: {$output}");
            } else {
                $this->line("  Migraciones completadas exitosamente");
            }

            // Ejecutar seeders si se solicita
            if ($this->option('seed')) {
                $this->line("  Ejecutando seeders para {$tenant->sede_id}...");
                
                // Hacer el tenant actual y bindearlo en el container
                $tenant->makeCurrent();
                app()->instance('tenant', $tenant);
                
                Artisan::call('db:seed', [
                    '--class' => 'TenantDatabaseSeeder',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
            }

            $this->info("  ✅ {$tenant->name} configurado correctamente");

        } catch (\Exception $e) {
            $this->error("  ❌ Error procesando {$tenant->name}: " . $e->getMessage());
        }
    }
}
