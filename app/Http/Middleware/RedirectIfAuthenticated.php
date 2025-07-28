<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Redirigir según el rol del usuario
                if ($user->hasRole('Usuario')) {
                    // Usuario va al monitoreo de espacios
                    $primerMapa = \App\Models\Mapa::first();
                    if ($primerMapa) {
                        return redirect()->route('plano.show', $primerMapa->id_mapa);
                    } else {
                        // Si no hay mapas, ir al tablero académico
                        return redirect()->route('modulos.actuales');
                    }
                } elseif ($user->hasRole('Supervisor')) {
                    // Supervisor va al dashboard
                    return redirect(RouteServiceProvider::HOME);
                } else {
                    // Administrador va al dashboard
                    return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }
}
