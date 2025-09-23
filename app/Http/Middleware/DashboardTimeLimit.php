<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardTimeLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Aumentar el límite de tiempo para el dashboard
        if ($request->route() && $request->route()->getName() === 'dashboard') {
            set_time_limit(120); // 2 minutos
            ini_set('memory_limit', '512M'); // Aumentar memoria si es necesario
        }

        return $next($request);
    }
}