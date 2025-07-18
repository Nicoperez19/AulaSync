<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            // Obtener el tiempo de la última actividad
            $lastActivity = Session::get('last_activity');
            $timeout = config('session.lifetime', 120) * 60; // Convertir minutos a segundos
            
            // Si no hay registro de última actividad, establecerlo ahora
            if (!$lastActivity) {
                Session::put('last_activity', time());
                return $next($request);
            }
            
            // Verificar si ha pasado demasiado tiempo
            if (time() - $lastActivity > $timeout) {
                // Guardar la URL actual para redirigir después del login
                $intendedUrl = $request->fullUrl();
                Session::put('url.intended', $intendedUrl);
                
                // La sesión ha expirado
                Auth::logout();
                Session::flush();
                
                // Redirigir al login con mensaje
                return redirect()->route('login')->with('session_expired', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.');
            }
            
            // Actualizar la última actividad
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
} 