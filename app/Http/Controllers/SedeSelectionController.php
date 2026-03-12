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
        
        // Los superusuarios ven TODAS las sedes disponibles
        if ($user->is_superuser) {
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

        return view('sedes.selection', compact('sedes'));
    }

    /**
     * Seleccionar sede y guardar en sesión (ya no redirige a subdominio)
     */
    public function redirect(Request $request, $sedeId)
    {
        $sede = Sede::with('tenant')->findOrFail($sedeId);

        if (!$sede->tenant || !$sede->tenant->is_active) {
            return back()->with('error', 'Esta sede no está disponible actualmente.');
        }

        // Almacenar el tenant en la sesión
        // El sistema ahora identifica tenants por sesión en lugar de subdominio
        session(['tenant_id' => $sede->tenant->id]);

        // Establecer el tenant como actual
        $sede->tenant->makeCurrent();

        // DEBUG: Log para verificar
        $user = Auth::user();
        \Log::info('SedeSelectionController::redirect', [
            'sede_id' => $sedeId,
            'tenant_id' => $sede->tenant->id,
            'tenant_domain' => $sede->tenant->domain,
            'is_initialized' => $sede->tenant->is_initialized,
            'needs_initialization' => $sede->tenant->needsInitialization(),
            'user_roles' => $user->getRoleNames(),
            'has_control_docente_role' => $user->hasRole('Control Docente'),
        ]);

        // Verificar si el tenant necesita inicialización
        if ($sede->tenant->needsInitialization()) {
            \Log::info('Redirecting to tenant initialization');
            return redirect()->route('tenant.initialization.index');
        }

        // Si es Control Docente, redirigir directamente al plano
        if ($user->hasRole('Control Docente')) {
            \Log::info('✅ Control Docente detectado, redirigiendo directamente al plano');
            return redirect()->route('plano.index');
        }

        // Ya autenticado y tenant seleccionado, redirigir al dashboard según rol
        \Log::info('Sede seleccionada, redirigiendo según rol');
        return $this->redirectByRole();
    }
}
