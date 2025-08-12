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
            
            // Verificar si hay profesores
            $tieneProfesores = \App\Models\Profesor::count() > 0;
            
            // Verificar si hay espacios
            $tieneEspacios = \App\Models\Espacio::count() > 0;
            
            $view->with('sede', $sede);
            $view->with('tieneProfesores', $tieneProfesores);
            $view->with('tieneEspacios', $tieneEspacios);
        });
    }
}
