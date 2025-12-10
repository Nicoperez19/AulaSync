<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class TenantList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list {--active : Show only active tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Tenant::query();

        if ($this->option('active')) {
            $query->where('is_active', true);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Domain', 'Prefix', 'Sede ID', 'Active', 'Default'],
            $tenants->map(function ($tenant) {
                return [
                    $tenant->id,
                    $tenant->name,
                    $tenant->domain,
                    $tenant->prefijo_espacios ?? 'N/A',
                    $tenant->sede_id ?? 'N/A',
                    $tenant->is_active ? '✓' : '✗',
                    $tenant->is_default ? '✓' : '✗',
                ];
            })
        );

        return 0;
    }
}
