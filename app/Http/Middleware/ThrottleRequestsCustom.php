<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsCustom extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        // Rutas que no deben tener rate limiting
        $excludedRoutes = [
            'api/espacios/estados',
            'api/verificar-usuario/*',
        ];

        // Verificar si la ruta actual está excluida
        foreach ($excludedRoutes as $excludedRoute) {
            if ($this->routeMatches($request, $excludedRoute)) {
                return $next($request);
            }
        }

        // Aplicar rate limiting normal para otras rutas
        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }

    /**
     * Check if the current route matches the excluded pattern
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $pattern
     * @return bool
     */
    private function routeMatches(Request $request, string $pattern): bool
    {
        $currentPath = $request->path();
        
        // Convertir wildcard a regex y escapar caracteres especiales
        $regexPattern = preg_quote($pattern, '/');
        $regexPattern = str_replace('\*', '.*', $regexPattern);
        
        return preg_match('/^' . $regexPattern . '$/', $currentPath);
    }
}
