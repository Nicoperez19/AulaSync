<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class TenantsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {tenant? : The domain of the tenant to migrate} {--fresh : Drop all tables before migrating} {--seed : Seed the database after migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantDomain = $this->argument('tenant');
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');

        $query = Tenant::query();
        
        if ($tenantDomain) {
            $query->where('domain', $tenantDomain);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return 1;
        }

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->name} ({$tenant->domain})");
            
            // Backup default connection
            $defaultConnection = Config::get('database.default');
            
            try {
                // Set tenant connection
                Config::set('database.connections.tenant.database', $tenant->database);
                DB::purge('tenant');
                
                $this->info("Database: {$tenant->database}");

                $options = [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ];

                if ($fresh) {
                     $this->warn("Wiping database for tenant {$tenant->domain}...");
                     Artisan::call('db:wipe', [
                         '--database' => 'tenant',
                         '--force' => true,
                     ], $this->output);
                     
                     $this->info("Running fresh migration...");
                     Artisan::call('migrate', $options, $this->output);
                } else {
                    Artisan::call('migrate', $options, $this->output);
                }
                
                $this->info(Artisan::output());

                if ($seed) {
                    $this->info("Seeding tenant...");
                    
                    // Asegurar que el tenant esté bindeado para los seeders
                    $tenant->makeCurrent();
                    app()->instance('tenant', $tenant);
                    
                    Artisan::call('db:seed', [
                        '--database' => 'tenant',
                        '--class' => 'TenantDatabaseSeeder',
                        '--force' => true,
                    ], $this->output);
                    $this->info(Artisan::output());
                }

            } catch (\Exception $e) {
                $this->error("Error migrating tenant {$tenant->domain}: " . $e->getMessage());
            } finally {
                // Restore default connection just in case
                Config::set('database.default', $defaultConnection);
            }
            
            $this->newLine();
        }
        
        $this->info('Tenants migration completed.');
        return 0;
    }
}
