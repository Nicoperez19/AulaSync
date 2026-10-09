<?php

namespace App\Services;

use App\Models\Sede;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantSessionService
{
    /**
     * Activa el tenant correspondiente a una sede en la sesión actual.
     *
     * @param Sede $sede
     * @return bool
     */
    public function activar(Sede $sede): bool
    {
        if (!$sede->tenant || !$sede->tenant->is_active) {
            Log::warning('⚠️ TenantSessionService: Sede sin tenant o tenant inactivo', [
                'id_sede' => $sede->id_sede,
                'nombre' => $sede->nombre_sede,
            ]);
            return false;
        }

        session(['tenant_id' => $sede->tenant->id]);
        session()->save();

        $sede->tenant->makeCurrent();

        Log::info('🏢 TenantSessionService: Tenant activado en sesión', [
            'tenant_id' => $sede->tenant->id,
            'tenant_name' => $sede->tenant->name,
            'id_sede' => $sede->id_sede,
        ]);

        return true;
    }

    /**
     * Busca la sede por ID y activa su tenant.
     *
     * @param mixed $idSede
     * @return Sede|null
     */
    public function activarPorIdSede($idSede): ?Sede
    {
        if (!$idSede) {
            return null;
        }

        $sede = Sede::with('tenant')->find($idSede);

        if (!$sede || !$this->activar($sede)) {
            return null;
        }

        return $sede;
    }

    /**
     * Limpia el tenant de la sesión actual.
     */
    public function desactivar(): void
    {
        session()->forget(['tenant_id', 'tenant']);
        if (app()->bound('tenant')) {
            app()->forgetInstance('tenant');
        }
    }

    /**
     * Obtiene el tenant actual activo.
     */
    public function getTenantActual(): ?Tenant
    {
        return Tenant::current();
    }
}
