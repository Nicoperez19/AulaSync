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
        
        // Control Docente redirige al Plano Digital
        if ($user->hasRole('Control Docente')) {
            return redirect()->route('plano.index');
        }
        
        // Verificar si el usuario tiene el permiso dashboard
        $hasDashboardPermission = false;
        try {
            $hasDashboardPermission = $user->hasPermissionTo('dashboard');
        } catch (\Exception $e) {
            // Si hay error con permisos, asumir que no tiene acceso
            $hasDashboardPermission = false;
        }

        if ($user->hasRole('Usuario')) {
            return redirect()->route('espacios.show');
        } elseif (($user->hasRole('Supervisor') || $user->hasRole('Administrador')) && $hasDashboardPermission) {
            return redirect(RouteServiceProvider::HOME);
        } else {
            // Si no tiene permisos para dashboard o es un rol desconocido, enviar a espacios
            return redirect()->route('espacios.show');
        }
    }
}