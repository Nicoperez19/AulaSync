<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use App\Services\ClasesDocenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocenteDashboardController extends Controller
{
    /**
     * Muestra el dashboard del docente con sus asignaturas y clases temporales.
     */
    public function index(Request $request, ClasesDocenteService $clasesService)
    {
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? Auth::user()->run);
        $docente = $request->attributes->get('docente_activo') ?? Profesor::where('run_profesor', $docenteRun)->first();
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);

        $clases = $clasesService->getClasesDocente($docenteRun);

        return view('docente.dashboard', compact('clases', 'docente', 'docenteRun', 'esSuperadmin'));
    }

    /**
     * Vista informativa cuando la sede aún no ha sido inicializada por el administrador.
     */
    public function sedeNoInicializada()
    {
        return view('docente.sede-no-inicializada');
    }
}
