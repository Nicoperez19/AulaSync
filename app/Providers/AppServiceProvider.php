<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('layouts.show-layout', 'show-layout');

        // Registrar el ViewComposer para el sidebar
        View::composer('components.sidebar.content', function ($view) {
            $sede = \App\Models\Sede::where('nombre_sede', 'like', '%Talcahuano%')
                ->with(['facultades.pisos.mapas'])
                ->first();
            
            if ($sede) {
                $primerMapa = $sede->facultades->flatMap(function($facultad) {
                    return $facultad->pisos->flatMap(function($piso) {
                        return $piso->mapas;
                    });
                })->first();
                
                $view->with('primerMapa', $primerMapa);
            } else {
                $view->with('primerMapa', null);
            }
            
            $view->with('sede', $sede);
        });
    }
}
