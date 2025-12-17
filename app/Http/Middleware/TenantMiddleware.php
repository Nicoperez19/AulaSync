<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Routes that are excluded from tenant check (by name)
     */
    protected $excludedRoutes = [
        'sedes.selection',
        'sedes.redirect',
        'tenant.initialization.*',
    ];

    /**
     * URL paths that are excluded from tenant check
     * These are checked by URL path when route names are not yet resolved
     */
    protected $excludedPaths = [
        'sedes/selection',
        'sedes/redirect',
        'tenant/initialization',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar primero si la ruta está excluida por path (funciona antes de que las rutas se resuelvan)
        foreach ($this->excludedPaths as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                // Para rutas de inicialización, establecer el tenant si hay subdominio
                if (str_contains($path, 'tenant/initialization')) {
                    $host = $request->getHost();
                    $subdomain = $this->getSubdomain($host);

                    if ($subdomain && $subdomain !== 'www') {
                        $tenant = Tenant::where('domain', $subdomain)->where('is_active', true)->first();
                        if ($tenant) {
                            $tenant->makeCurrent();
                        }
                    }
                }
                return $next($request);
            }
        }

        // Verificar si es una ruta de inicialización (backup por nombre de ruta)
        if ($request->routeIs('tenant.initialization.*')) {
            $host = $request->getHost();
            $subdomain = $this->getSubdomain($host);

            if ($subdomain && $subdomain !== 'www') {
                $tenant = Tenant::where('domain', $subdomain)->where('is_active', true)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                }
            }

            return $next($request);
        }

        // Check if route is excluded from tenant check by name
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        $host = $request->getHost();

        // Extraer el subdominio
        $subdomain = $this->getSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            // Buscar el tenant por el subdominio
            $tenant = Tenant::where('domain', $subdomain)
                ->where('is_active', true)
                ->first();

            if ($tenant) {
                // Establecer el tenant actual
                $tenant->makeCurrent();

                // Check if tenant needs initialization
                if ($tenant->needsInitialization()) {
                    return redirect()->route('tenant.initialization.index');
                }
            } else {
                // Si no se encuentra el tenant, redirigir a selección de sedes
                return redirect()->route('sedes.selection');
            }
        } else {
            // Si no hay subdominio (dominio base como localhost:8000),
            // verificar si estamos en la página raíz o login
            if ($request->is('/') || $request->is('login') || $request->is('')) {
                // Redirigir a la selección de sedes
                return redirect()->route('sedes.selection');
            }

            // Para otras rutas públicas que no requieren tenant, continuar normalmente
            // Esto permite que rutas como modulos-actuales funcionen sin tenant
        }

        return $next($request);
    }

    /**
     * Extraer el subdominio del host
     */
    protected function getSubdomain(string $host): ?string
    {
        // Obtener el dominio base de la configuración
        $appUrl = config('app.url');
        $appDomain = parse_url($appUrl, PHP_URL_HOST);

        // Si estamos en localhost, buscar subdominios en el formato: subdomain.localhost
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $parts = explode('.', $host);
            if (count($parts) > 1) {
                return $parts[0];
            }
            return null;
        }

        // Para dominios reales
        if ($appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            if ($subdomain !== $appDomain) {
                return $subdomain;
            }
        }

        return null;
    }
}
