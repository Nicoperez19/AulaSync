<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth; // ADD THIS IMPORT

class TenantMiddleware
{
    /**
     * Routes that are excluded from tenant check (by name)
     */
    protected $excludedRoutes = [
        'sedes.selection',
        'sedes.redirect',
        'tenant.initialization.*',
        'login',
        'register',
        'password.request',
        'password.reset',
        'verification.notice',
    ];

    /**
     * URL paths that are excluded from tenant check
     * These are checked by URL path when route names are not yet resolved
     */
    protected $excludedPaths = [
        'sedes/selection',
        'sedes/redirect',
        'tenant/initialization',
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'verify-email',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ---------------------------------------------------------
        // PASO 1: Intentar establecer el contexto del tenant (Set Up)
        // ---------------------------------------------------------
        
        $tenantSet = false;

        // Opción A: Obtener de sesión (Prioridad alta)
        if (session()->has('tenant_id')) {
            $tenant = Tenant::find(session('tenant_id'));
            if ($tenant && $tenant->is_active) {
                $tenant->makeCurrent();
                $tenantSet = true;
            } else {
                session()->forget(['tenant_id', 'tenant']);
            }
        }

        // Opción B: Obtener por subdominio (Si no hay sesión)
        if (!$tenantSet) {
            $host = $request->getHost();
            $subdomain = $this->getSubdomain($host);

            if ($subdomain && $subdomain !== 'www') {
                $tenant = Tenant::where('domain', $subdomain)->where('is_active', true)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                    session(['tenant_id' => $tenant->id]);
                    $tenantSet = true;
                }
            }
        }

        // ---------------------------------------------------------
        // PASO 2: Verificar Exclusiones (Skip checks)
        // ---------------------------------------------------------

        // Verificar por Path
        foreach ($this->excludedPaths as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                return $next($request);
            }
        }

        // Verificar por Nombre de Ruta
        if ($request->routeIs('tenant.initialization.*')) {
            return $next($request);
        }

        foreach ($this->excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // ---------------------------------------------------------
        // PASO 3: Validaciones de Acceso (Enforce rules)
        // ---------------------------------------------------------

        // Si tenemos un tenant válido
        if ($tenantSet) {
            $tenant = Tenant::current();

            // Verificar si necesita inicialización
            // ¡IMPORTANTE! Si ya estamos en las rutas de inicialización, las exclusiones de arriba (PASO 2)
            // ya habrán retornado $next, así que aquí solo llegan las rutas NO excluidas (ej. dashboard).
            if ($tenant->needsInitialization()) {
                return redirect()->route('tenant.initialization.index');
            }

            // Tenant válido y listo, continuar
            return $next($request);
        }

        // ---------------------------------------------------------
        // PASO 4: Manejo de No Tenant (Fallback)
        // ---------------------------------------------------------

        // Si el usuario NO está autenticado, dejar pasar (Laravel manejará auth)
        if (!Auth::check()) {
            return $next($request);
        }

        // Si el usuario SÍ está autenticado pero no tiene tenant, redirigir a selección
        // excepto si ya estamos intentando ir a selección
        if (!$request->routeIs('sedes.selection') && !$request->is('sedes/selection')) {
            return redirect()->route('sedes.selection');
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
