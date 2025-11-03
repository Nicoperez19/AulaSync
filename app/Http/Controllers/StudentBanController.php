<?php

namespace App\Http\Controllers;

use App\Models\StudentBan;
use App\Models\User;
use App\Models\Profesor;
use App\Models\Solicitante;
use Illuminate\Http\Request;

class StudentBanController extends Controller
{
    /**
     * Display a listing of bans
     */
    public function index()
    {
        $bans = StudentBan::orderBy('created_at', 'desc')->paginate(20);
        
        // Enriquecer los datos con información del usuario
        $bans->getCollection()->transform(function ($ban) {
            $ban->user_info = $this->getUserInfo($ban->run);
            return $ban;
        });

        return view('layouts.bans.index', compact('bans'));
    }

    /**
     * Show the form for creating a new ban
     */
    public function create()
    {
        return view('layouts.bans.create');
    }

    /**
     * Store a newly created ban
     */
    public function store(Request $request)
    {
        $request->validate([
            'run' => 'required|string',
            'reason' => 'required|string|max:500',
            'banned_until' => 'required|date|after:now',
        ], [
            'run.required' => 'El RUN es obligatorio',
            'reason.required' => 'La razón del baneo es obligatoria',
            'reason.max' => 'La razón no puede exceder 500 caracteres',
            'banned_until.required' => 'La fecha de fin del baneo es obligatoria',
            'banned_until.after' => 'La fecha de fin debe ser posterior a la fecha actual',
        ]);

        // Verificar que el usuario existe
        $userInfo = $this->getUserInfo($request->run);
        if (!$userInfo) {
            return redirect()->back()
                ->withErrors(['run' => 'No se encontró un usuario con ese RUN'])
                ->withInput();
        }

        StudentBan::create([
            'run' => $request->run,
            'reason' => $request->reason,
            'banned_until' => $request->banned_until,
        ]);

        return redirect()->route('bans.index')
            ->with('success', 'Baneo creado exitosamente');
    }

    /**
     * Show the form for editing a ban
     */
    public function edit($id)
    {
        $ban = StudentBan::findOrFail($id);
        $ban->user_info = $this->getUserInfo($ban->run);
        
        return view('layouts.bans.edit', compact('ban'));
    }

    /**
     * Update the specified ban
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'banned_until' => 'required|date|after:now',
        ], [
            'reason.required' => 'La razón del baneo es obligatoria',
            'reason.max' => 'La razón no puede exceder 500 caracteres',
            'banned_until.required' => 'La fecha de fin del baneo es obligatoria',
            'banned_until.after' => 'La fecha de fin debe ser posterior a la fecha actual',
        ]);

        $ban = StudentBan::findOrFail($id);
        $ban->update([
            'reason' => $request->reason,
            'banned_until' => $request->banned_until,
        ]);

        return redirect()->route('bans.index')
            ->with('success', 'Baneo actualizado exitosamente');
    }

    /**
     * Remove the specified ban
     */
    public function destroy($id)
    {
        $ban = StudentBan::findOrFail($id);
        $ban->delete();

        return redirect()->route('bans.index')
            ->with('success', 'Baneo eliminado exitosamente');
    }

    /**
     * Get user information from different tables
     */
    private function getUserInfo($run)
    {
        // Buscar en Profesores
        $profesor = Profesor::where('run_profesor', $run)->first();
        if ($profesor) {
            return [
                'name' => $profesor->name,
                'email' => $profesor->email ?? 'No disponible',
                'type' => 'Profesor'
            ];
        }

        // Buscar en Solicitantes
        $solicitante = Solicitante::where('run_solicitante', $run)->first();
        if ($solicitante) {
            return [
                'name' => $solicitante->nombre,
                'email' => $solicitante->correo ?? 'No disponible',
                'type' => 'Solicitante'
            ];
        }

        // Buscar en Users
        $user = User::where('run', $run)->first();
        if ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email ?? 'No disponible',
                'type' => 'Usuario'
            ];
        }

        return null;
    }

    /**
     * Check if user is banned (API endpoint)
     */
    public function checkBan($run)
    {
        $ban = StudentBan::getActiveBan($run);
        
        if ($ban) {
            return response()->json([
                'banned' => true,
                'reason' => $ban->reason,
                'banned_until' => $ban->banned_until->format('d/m/Y H:i'),
                'remaining_time' => $ban->remaining_time,
            ]);
        }

        return response()->json([
            'banned' => false,
        ]);
    }
}
