<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Tenant;
use App\Models\Mapa;
use App\Traits\RedirectByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SedeSelectionController extends Controller
{
    use RedirectByRole;

    /**
     * Mostrar la página de selección de sedes
     * Filtra según los permisos del usuario
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = Sede::whereHas('tenant', function ($query) {
            $query->where('is_active', true);
        })
        ->with(['tenant', 'universidad', 'comuna']);
        
        // Los superusuarios y administradores maestros ven TODAS las sedes disponibles
        if ($user->is_superuser || $user->hasRole('Administrador') || $user->hasRole('Supervisor') || $user->hasRole('Director') || $user->hasRole('Super Admin') || (string)$user->run === '19716146') {
            // No aplicar filtros - ver todas las sedes activas
        }
        // Si el usuario tiene una sede específica asignada, mostrar solo esa
        elseif ($user->id_sede) {
            $query->where('id_sede', $user->id_sede);
        } 
        // Si el usuario tiene universidad asignada pero no sede, mostrar todas las de esa U
        elseif ($user->id_universidad) {
            $query->where('id_universidad', $user->id_universidad);
        }
        
        $sedes = $query->get();

        // Si solo hay una sede disponible y el usuario es Director, auto-seleccionar y redirigir
        if ($sedes->count() === 1 && $user->hasRole('Director')) {
            return redirect()->route('sedes.redirect', ['sede' => $sedes->first()->id_sede]);
        }

        return view('sedes.selection', compact('sedes'));
    }

    /**
     * Seleccionar sede y guardar en sesión (ya no redirige a subdominio)
     */
    public function redirect(Request $request, $sedeId)
    {
        $sede = Sede::with('tenant')->findOrFail($sedeId);

        $tenantService = app(\App\Services\TenantSessionService::class);
        if (!$tenantService->activar($sede)) {
            return back()->with('error', 'Esta sede no está disponible actualmente.');
        }

        // DEBUG: Log para verificar
        $user = Auth::user();

        // Si es Profesor puro, NUNCA debe ver el wizard y va a su portal
        if ($user->hasRole('Profesor') && !$user->hasAnyRole(['Administrador', 'Supervisor', 'Director', 'Super Admin']) && !$user->is_superuser && (string)$user->run !== '19716146') {
            if ($sede->tenant->needsInitialization()) {
                return \Illuminate\Support\Facades\Route::has('docente.sede-no-inicializada')
                    ? redirect()->route('docente.sede-no-inicializada')
                    : redirect('/docente/sede-no-inicializada');
            }
            return \Illuminate\Support\Facades\Route::has('docente.dashboard')
                ? redirect()->route('docente.dashboard')
                : redirect('/docente');
        }

        // Verificar si el tenant necesita inicialización (para otros roles)
        if ($sede->tenant->needsInitialization()) {
            return redirect()->route('tenant.initialization.index');
        }

        // Si es Control Docente puro, redirigir directamente al plano
        if ($user->hasRole('Control Docente') && !$user->hasAnyRole(['Administrador', 'Supervisor', 'Director', 'Super Admin']) && !$user->is_superuser && (string)$user->run !== '19716146') {
            return redirect()->route('plano.index');
        }

        // Ya autenticado y tenant seleccionado, redirigir al dashboard según rol
        return $this->redirectByRole();
    }
}
