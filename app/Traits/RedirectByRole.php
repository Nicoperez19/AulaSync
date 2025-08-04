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

        if ($user->hasRole('Usuario')) {
            return redirect()->route('espacios.show');
        } elseif ($user->hasRole('Supervisor')) {
            return redirect(RouteServiceProvider::HOME);
        } elseif ($user->hasRole('Administrador')) {
            return redirect(RouteServiceProvider::HOME);
        } else {
            return redirect(RouteServiceProvider::HOME);
        }
    }
}