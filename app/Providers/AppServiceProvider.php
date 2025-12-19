<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Sede;
use App\Models\Profesor;
use App\Models\Espacio;
use App\Models\Tenant;
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
        // Force HTTP in non-local environments when behind a proxy (Docker/Nginx)
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('http');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('layouts.show-layout', 'show-layout');

        // Listen to the response event to replace Vite URLs in production
        if (config('app.env') !== 'local') {
            app('events')->listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function ($event) {
                if ($event->response instanceof \Illuminate\Http\Response ||
                    $event->response instanceof \Symfony\Component\HttpFoundation\Response) {
                    
                    $content = $event->response->getContent();
                    $manifest = public_path('build/manifest.json');
                    
                    if (file_exists($manifest)) {
                        $manifestData = json_decode(file_get_contents($manifest), true);
                        
                        // Replace @vite/client
                        $content = str_replace('http://[::1]:5173/@vite/client', '', $content);
                        
                        // Replace CSS references
                        $content = preg_replace_callback(
                            '/href="http:\/\/\[::1\]:5173\/(resources\/[^"]*\.css)"/',
                            function ($matches) use ($manifestData) {
                                $asset = str_replace('resources/', '', $matches[1]);
                                if (isset($manifestData[$asset])) {
                                    return 'href="' . asset("build/" . $manifestData[$asset]['file']) . '"';
                                }
                                return $matches[0];
                            },
                            $content
                        );
                        
                        // Replace JS references
                        $content = preg_replace_callback(
                            '/src="http:\/\/\[::1\]:5173\/(resources\/[^"]*\.js)"/',
                            function ($matches) use ($manifestData) {
                                $asset = str_replace('resources/', '', $matches[1]);
                                if (isset($manifestData[$asset])) {
                                    return 'src="' . asset("build/" . $manifestData[$asset]['file']) . '"';
                                }
                                return $matches[0];
                            },
                            $content
                        );
                        
                        $event->response->setContent($content);
                    }
                }
            });
        }

        // Registrar el Observer para LicenciaProfesor
        LicenciaProfesor::observe(LicenciaProfesorObserver::class);

        // Registrar el ViewComposer para el sidebar
        View::composer('components.sidebar.content', function ($view) {
            // Try to get the current tenant first
            $tenant = Tenant::current();
            
            if ($tenant && $tenant->sede) {
                $sede = $tenant->sede->load(['facultades.pisos.mapas']);
            } else {
                // Fallback to Talcahuano if no tenant
                $sede = Sede::where('nombre_sede', 'like', '%Talcahuano%')
                    ->with(['facultades.pisos.mapas'])
                    ->first();
            }
            
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
