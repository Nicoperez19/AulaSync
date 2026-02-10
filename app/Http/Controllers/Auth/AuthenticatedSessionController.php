<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Traits\RedirectByRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RedirectByRole;

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

        // Obtener el usuario autenticado
        $user = Auth::user();

        // DEBUG: Log de valores del usuario para diagnosticar redirección
        Log::info('🔐 Login - Datos del usuario', [
            'run' => $user->run,
            'name' => $user->name,
            'is_superuser' => $user->is_superuser,
            'is_superuser_type' => gettype($user->is_superuser),
            'id_sede' => $user->id_sede,
            'id_sede_type' => gettype($user->id_sede),
        ]);

        // Si el usuario es superusuario, mostrar selección de sedes
        if ($user->is_superuser) {
            Log::info('✅ Superusuario detectado, mostrando selección de sedes', [
                'run' => $user->run,
            ]);
            
            return redirect()->route('sedes.selection');
        }

        // Si el usuario NO es superusuario pero tiene una sede asignada
        if ($user->id_sede) {
            Log::info('✅ Usuario con sede asignada, redirigiendo automáticamente', [
                'run' => $user->run,
                'id_sede' => $user->id_sede,
            ]);
            
            // Redirigir directamente a la sede asignada
            return redirect()->route('sedes.redirect', ['sede' => $user->id_sede]);
        }

        // Usuario NO es superusuario Y NO tiene sede asignada
        // Esto es un error de configuración - debe contactar al administrador
        Log::warning('❌ Usuario sin sede asignada y sin permisos de superusuario', [
            'run' => $user->run,
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('error', 'Tu cuenta no tiene una sede asignada. Por favor, contacta al administrador del sistema.');
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
