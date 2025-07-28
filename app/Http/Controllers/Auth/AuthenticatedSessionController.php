<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Verificar si hay una URL guardada por expiración de sesión
        $intendedUrl = $request->session()->get('url.intended');
        
        if ($intendedUrl) {
            // Limpiar la URL guardada
            $request->session()->forget('url.intended');
            return redirect($intendedUrl);
        }

        // Verificar si hay una URL enviada desde el formulario (localStorage)
        $formIntendedUrl = $request->input('intended_url');
        if ($formIntendedUrl && filter_var($formIntendedUrl, FILTER_VALIDATE_URL)) {
            return redirect($formIntendedUrl);
        }

        // Redirigir según el rol del usuario
        $user = Auth::user();
        
        if ($user->hasRole('Usuario')) {
            // Usuario va al monitoreo de espacios
            // Buscar el primer mapa disponible
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

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
