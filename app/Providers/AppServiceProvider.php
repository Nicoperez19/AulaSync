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
        // Force HTTPS in non-local environments if configured or accessed via HTTPS
        if (env('APP_ENV') !== 'local') {
            if (str_starts_with(config('app.url'), 'https://') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
                URL::forceScheme('https');
                config(['session.secure' => true]);
            } else {
                URL::forceScheme('http');
            }
        }

        if ($this->app->runningUnitTests()) {
            $this->loadMigrationsFrom(database_path('migrations/central'));
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

                    static $cachedManifestData = null;
                    if ($cachedManifestData === null && file_exists($manifest)) {
                        $cachedManifestData = json_decode(file_get_contents($manifest), true) ?: [];
                    }

                    if ($cachedManifestData !== null) {
                        $manifestData = $cachedManifestData;

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

        // Registrar el ViewComposer para el sidebar (cacheado por tenant para evitar queries en cada vista)
        View::composer('components.sidebar.content', function ($view) {
            $tenant = Tenant::current();
            $tenantCacheKey = $tenant ? "tenant_{$tenant->id}" : 'default';

            $sidebarData = \Illuminate\Support\Facades\Cache::remember("sidebar_meta_{$tenantCacheKey}", 300, function () use ($tenant) {
                if ($tenant && $tenant->sede) {
                    $sede = $tenant->sede->load(['facultades.pisos.mapas']);
                } else {
                    $sede = Sede::where('nombre_sede', 'like', '%Talcahuano%')
                        ->with(['facultades.pisos.mapas'])
                        ->first();
                }

                $primerMapa = null;
                if ($sede) {
                    $primerMapa = $sede->facultades->flatMap(function ($facultad) {
                        return $facultad->pisos->flatMap(function ($piso) {
                            return $piso->mapas;
                        });
                    })->first();
                }

                return [
                    'sede' => $sede,
                    'primerMapa' => $primerMapa,
                    'tieneProfesores' => Profesor::exists(),
                    'tieneEspacios' => Espacio::exists(),
                ];
            });

            $view->with($sidebarData);
        });

        // Registrar el ViewComposer para el logo institucional
        View::composer([
            'components.application-logo-navbar',
            'components.application-logo-navbar-bot'
        ], LogoComposer::class);
    }
}
