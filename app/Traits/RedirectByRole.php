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
        
        // Administradores y supervisores siempre redirigen al Dashboard (KPIs)
        if ($user->is_superuser || (string)$user->run === '19716146' || $user->hasRole('Super Admin') || $user->hasRole('Administrador') || $user->hasRole('Supervisor')) {
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
        
        if ($user->hasRole('Usuario')) {
            return redirect()->route('espacios.show');
        }

        return redirect(RouteServiceProvider::HOME);
    }
}