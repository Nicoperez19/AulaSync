<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QRService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QRService::class, function ($app) {
            return new QRService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
