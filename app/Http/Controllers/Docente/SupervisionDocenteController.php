<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisionDocenteController extends Controller
{
    /**
     * Lista de docentes para que el superadministrador elija a quién supervisar/simular.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $esSuperAdmin = $user->is_superuser || $user->hasRole('Super Admin') || (string)$user->run === '19716146';

        if (!$esSuperAdmin) {
            abort(403, 'Solo el superadministrador puede acceder a la supervisión docente.');
        }

        $search = $request->input('search');

        $query = Profesor::withCount(['asignaturas']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('run_profesor', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $profesores = $query->orderBy('name')->paginate(20)->withQueryString();
        $docenteSimuladoRun = session('docente_simulado_run');

        return view('docente.supervision.index', compact('profesores', 'docenteSimuladoRun', 'search'));
    }

    /**
     * Selecciona un docente para ver el portal en su nombre.
     */
    public function seleccionar(Request $request, string $run)
    {
        $user = Auth::user();
        $esSuperAdmin = $user->is_superuser || $user->hasRole('Super Admin') || (string)$user->run === '19716146';

        if (!$esSuperAdmin) {
            abort(403);
        }

        $profesor = Profesor::where('run_profesor', $run)->firstOrFail();

        session(['docente_simulado_run' => (string) $profesor->run_profesor]);
        session()->save();

        return redirect()->route('docente.dashboard')->with('info', "Supervisando como: {$profesor->name} ({$profesor->run_profesor})");
    }

    /**
     * Restablece la simulación de docente.
     */
    public function restablecer()
    {
        session()->forget('docente_simulado_run');
        session()->save();

        return redirect()->route('docente.supervision')->with('info', 'Simulación de docente finalizada.');
    }
}
