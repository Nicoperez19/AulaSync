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
            // Fallback to Talcahuano if no tenant
            $sedeActual = Sede::where('nombre_sede', 'like', '%Talcahuano%')->first();
            $idSede = $sedeActual ? $sedeActual->id_sede : 'TH';
        }

        // Cache the logo path for 60 minutes to avoid repeated database queries
        $logoPath = Cache::remember("logo_institucional_path_{$idSede}", 3600, function () use ($idSede, $sedeActual) {
            // First check if sede has logo in its own field
            if ($sedeActual && $sedeActual->logo) {
                $path = 'sedes/logos/' . $sedeActual->logo;
                // Verificar si existe en el disco publico
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                     return asset('storage/' . $path);
                }
            }
            
            // Fallback to configuration table
            $logoInstitucional = Configuracion::where('clave', "logo_institucional_{$idSede}")->first();
            
            if ($logoInstitucional && $logoInstitucional->valor) {
                $path = 'images/logo/' . $logoInstitucional->valor;
                // Verificar si existe en el disco publico
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
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
