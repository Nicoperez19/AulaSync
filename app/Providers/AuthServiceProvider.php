<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Otorgar acceso global a Superusuarios y cuenta máster 19716146
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->is_superuser || (string)$user->run === '19716146' || $user->hasRole('Super Admin')) {
                return true;
            }
        });
    }
}
