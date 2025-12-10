<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureTenantStorage();
    }

    /**
     * Configure storage to be tenant-aware
     */
    protected function configureTenantStorage(): void
    {
        // Listen for when a tenant becomes current
        \Spatie\Multitenancy\Models\Concerns\UsesTenantModel::current();
        
        // Configure public disk to use tenant-specific directory
        config([
            'filesystems.disks.public.root' => function () {
                $tenant = Tenant::current();
                if ($tenant) {
                    return storage_path("app/public/tenant-{$tenant->id}");
                }
                return storage_path('app/public');
            },
        ]);
    }
}
