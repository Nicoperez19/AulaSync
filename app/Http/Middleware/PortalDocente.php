<?php

namespace App\Http\Middleware;

use App\Models\Profesor;
use App\Models\Tenant;
use App\Services\TenantSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class PortalDocente
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $esSuperAdmin = $user->is_superuser || $user->hasRole('Super Admin') || (string)$user->run === '19716146';
        $esProfesor = $user->hasRole('Profesor');

        // Solo profesores y superadministradores pueden ingresar al portal docente
        if (!$esSuperAdmin && !$esProfesor) {
            abort(403, 'Acceso denegado. El portal docente es exclusivo para profesores y el superadministrador.');
        }

        $tenantService = app(TenantSessionService::class);

        // Asegurar tenant en sesión
        if (!session()->has('tenant_id') || !Tenant::current()) {
            if ($user->id_sede) {
                $tenantService->activarPorIdSede($user->id_sede);
            } elseif ($esSuperAdmin) {
                return redirect()->route('sedes.selection')->with('info', 'Por favor selecciona una sede para acceder al portal docente.');
            } else {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tu cuenta de docente no tiene una sede asignada.');
            }
        }

        $tenant = Tenant::current();
        if ($tenant && $tenant->needsInitialization() && !$esSuperAdmin) {
            return redirect()->route('docente.sede-no-inicializada');
        }

        $docenteActivo = null;
        $docenteActivoRun = null;

        if ($esSuperAdmin) {
            // El superadministrador puede simular a un docente
            $simuladoRun = session('docente_simulado_run');
            if ($simuladoRun) {
                $docenteActivo = Profesor::where('run_profesor', $simuladoRun)->first();
                $docenteActivoRun = $simuladoRun;
            } else {
                // Tomar el primer docente de la sede como predeterminado si existe
                $primerDocente = Profesor::first();
                if ($primerDocente) {
                    $docenteActivo = $primerDocente;
                    $docenteActivoRun = $primerDocente->run_profesor;
                    session(['docente_simulado_run' => $docenteActivoRun]);
                }
            }
        } else {
            // Usuario docente normal
            $docenteActivoRun = (string)$user->run;
            $docenteActivo = Profesor::where('run_profesor', $docenteActivoRun)->first();
        }

        // Compartir datos en request y vistas
        $request->attributes->set('docente_activo', $docenteActivo);
        $request->attributes->set('docente_activo_run', $docenteActivoRun);
        $request->attributes->set('es_superadmin_simulando', $esSuperAdmin);

        View::share('docenteActivo', $docenteActivo);
        View::share('docenteActivoRun', $docenteActivoRun);
        View::share('esSuperadminSimulando', $esSuperAdmin);

        return $next($request);
    }
}
