<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;

class MultiTenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el tenant actual como singleton
        $this->app->singleton('tenant', function () {
            return null;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Aquí se pueden agregar listeners de eventos de tenancy
        // Por ejemplo, para crear/eliminar bases de datos de tenant
    }
}
