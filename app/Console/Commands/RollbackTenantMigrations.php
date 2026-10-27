<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class RollbackTenantMigrations extends Command
{
    protected $signature = 'tenants:migrate:rollback {--step=1}';

    protected $description = 'Rollback migrations for all tenants';

    public function handle()
    {
        $step = $this->option('step');
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Rolling back tenant: {$tenant->name} ({$tenant->sede_id})");
            $this->info("Database: {$tenant->database}");

            \Artisan::call('migrate:rollback', [
                '--database' => 'tenant',
                '--step' => $step,
            ]);

            $this->info("✓ Rollback completed\n");
        }

        $this->info('All tenants rollback completed.');
    }
}
