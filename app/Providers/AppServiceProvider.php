<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Models\Sede;
use App\Models\Profesor;
use App\Models\Espacio;
use App\Models\LicenciaProfesor;
use App\Observers\LicenciaProfesorObserver;
use App\View\Composers\LogoComposer;

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

        // Registrar el Observer para LicenciaProfesor
        LicenciaProfesor::observe(LicenciaProfesorObserver::class);

        // Registrar el ViewComposer para el sidebar
        View::composer('components.sidebar.content', function ($view) {
            $sede = Sede::where('nombre_sede', 'like', '%Talcahuano%')
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
            $tieneProfesores = Profesor::count() > 0;
            
            // Verificar si hay espacios
            $tieneEspacios = Espacio::count() > 0;
            
            $view->with('sede', $sede);
            $view->with('tieneProfesores', $tieneProfesores);
            $view->with('tieneEspacios', $tieneEspacios);
        });

        // Registrar el ViewComposer para el logo institucional
        View::composer([
            'components.application-logo-navbar',
            'components.application-logo-navbar-bot'
        ], LogoComposer::class);
    }
}
