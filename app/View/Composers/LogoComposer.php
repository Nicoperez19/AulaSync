<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\Tenant;

class LogoComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Try to get the current tenant first
        $tenant = Tenant::current();
        
        if ($tenant && $tenant->sede) {
            $sedeActual = $tenant->sede;
            $idSede = $sedeActual->id_sede;
        } else {
            // Sin tenant activo (ej: pantalla de login): sin sesgo de sede
            $sedeActual = null;
            $idSede = null;
        }

        // Usar 'generic' como cache key cuando no hay sede para no contaminar cache por sede
        $cacheKey = $idSede ? "logo_institucional_path_{$idSede}" : 'logo_institucional_path_generic';

        $logoPath = Cache::remember($cacheKey, 3600, function () use ($idSede, $sedeActual) {
            // First check if sede has logo in its own field
            if ($sedeActual && $sedeActual->logo) {
                $path = 'sedes/logos/' . $sedeActual->logo;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                     return asset('storage/' . $path);
                }
            }
            
            // Fallback to configuration table (solo si hay sede activa)
            if ($idSede) {
                $logoInstitucional = Configuracion::where('clave', "logo_institucional_{$idSede}")->first();
                if ($logoInstitucional && $logoInstitucional->valor) {
                    $path = 'images/logo/' . $logoInstitucional->valor;
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        return asset('storage/' . $path);
                    }
                }
            }
            
            // Verificar si el fallback por defecto existe
            if (file_exists(public_path('images/logo_IT_talcahuano.png'))) {
                return asset('images/logo_IT_talcahuano.png');
            }

            // Fallback final genérico si todo falla
            return asset('images/logo_instituto_tecnologico-01.png');
        });

        $view->with('logoInstitucional', $logoPath);
        $view->with('sedeActual', $sedeActual);
        $view->with('idSedeActual', $idSede);
    }
}
