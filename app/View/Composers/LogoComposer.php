<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Configuracion;
use App\Models\Sede;

class LogoComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Obtener la sede actual (por ahora Talcahuano por defecto, puede extenderse para detectar dinámicamente)
        $sedeActual = Sede::where('nombre_sede', 'like', '%Talcahuano%')->first();
        $idSede = $sedeActual ? $sedeActual->id_sede : 'TH';

        // Cache the logo path for 60 minutes to avoid repeated database queries
        $logoPath = Cache::remember("logo_institucional_path_{$idSede}", 3600, function () use ($idSede) {
            $logoInstitucional = Configuracion::where('clave', "logo_institucional_{$idSede}")->first();
            
            if ($logoInstitucional && $logoInstitucional->valor) {
                return asset('storage/images/logo/' . $logoInstitucional->valor);
            }
            
            return asset('images/logo_IT_talcahuano.png');
        });

        $view->with('logoInstitucional', $logoPath);
        $view->with('sedeActual', $sedeActual);
        $view->with('idSedeActual', $idSede);
    }
}
