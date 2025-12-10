<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantInitialization
{
    /**
     * Routes that are allowed without initialization
     */
    protected $allowedRoutes = [
        'tenant.initialization.*',
        'logout',
        'login',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::current();
        
        // Si no hay tenant, continuar normalmente
        if (!$tenant) {
            return $next($request);
        }

        // Si el tenant ya está inicializado, continuar normalmente
        if ($tenant->is_initialized) {
            return $next($request);
        }

        // Verificar si la ruta actual está permitida
        foreach ($this->allowedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Redirigir al wizard de inicialización
        return redirect()->route('tenant.initialization.index');
    }
}
