<?php

namespace App\Traits;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;

trait RedirectByRole
{
    /**
     * Redirigir al usuario según su rol
     */
    protected function redirectByRole()
    {
        $user = Auth::user();
        
        // Administradores, supervisores y directores siempre redirigen al Dashboard (KPIs)
        if ($user->is_superuser || (string)$user->run === '19716146' || $user->hasRole('Super Admin') || $user->hasRole('Administrador') || $user->hasRole('Supervisor') || $user->hasRole('Director')) {
            return redirect(RouteServiceProvider::HOME);
        }

        // Control Docente redirige al Plano Digital
        if ($user->hasRole('Control Docente')) {
            return redirect()->route('plano.index');
        }

        // Profesor redirige al Portal Docente
        if ($user->hasRole('Profesor')) {
            return \Illuminate\Support\Facades\Route::has('docente.dashboard')
                ? redirect()->route('docente.dashboard')
                : redirect('/docente');
        }
        
        if ($user->hasAnyRole(['Usuario', 'Estudiante en Práctica'])) {
            return redirect()->route('espacios.show');
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
