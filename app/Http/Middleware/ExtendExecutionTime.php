<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExtendExecutionTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, int $seconds = 120)
    {
        // Configurar límites de tiempo de ejecución específicos para esta ruta
        set_time_limit($seconds);
        ini_set('max_execution_time', $seconds);
        
        // Configuraciones adicionales para evitar timeouts
        ini_set('memory_limit', '512M');
        ini_set('max_input_time', $seconds);
        
        // Para rutas Livewire específicas, aplicar configuraciones especiales
        if ($request->hasHeader('X-Livewire') || str_contains($request->path(), 'livewire')) {
            set_time_limit(180); // 3 minutos para Livewire
            ini_set('max_execution_time', 180);
        }

        return $next($request);
    }
}