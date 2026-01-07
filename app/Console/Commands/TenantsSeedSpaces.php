<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class TenantsSeedSpaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed-spaces {tenant? : The domain of the tenant to seed spaces for} {--fresh : Wipe spaces table before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed spaces for tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantDomain = $this->argument('tenant');
        $fresh = $this->option('fresh');

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
            $this->info("Seeding spaces for tenant: {$tenant->name} ({$tenant->domain})");

            // Backup default connection
            $defaultConnection = Config::get('database.default');

            try {
                // Set tenant connection
                Config::set('database.connections.tenant.database', $tenant->database);
                DB::purge('tenant');

                // Make tenant current for seeders that depend on Tenant::current()
                $tenant->makeCurrent();

                $this->info("Database: {$tenant->database}");

                if ($fresh) {
                    $this->warn("Wiping spaces table for tenant {$tenant->domain}...");
                    DB::connection('tenant')->table('espacios')->truncate();
                }

                // Seed spaces
                $this->info("Seeding floors and spaces...");
                // First seed floors, then spaces
                Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class' => 'Database\\Seeders\\PisoSeeder',
                    '--force' => true,
                ], $this->output);
                
                Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class' => 'Database\\Seeders\\EspacioSeeder',
                    '--force' => true,
                ], $this->output);

                $this->info(Artisan::output());

            } catch (\Exception $e) {
                $this->error("Error seeding spaces for tenant {$tenant->domain}: " . $e->getMessage());
            } finally {
                // Restore default connection
                Config::set('database.default', $defaultConnection);
            }

            $this->newLine();
        }

        $this->info('Spaces seeding completed for all tenants.');
        return 0;
    }
}