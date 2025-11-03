<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\Solicitante;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bans = Ban::with('solicitante')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('layouts.bans.index', compact('bans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $solicitantes = Solicitante::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('layouts.bans.create', compact('solicitantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'run_solicitante' => 'required|exists:solicitantes,run_solicitante',
            'razon' => 'required|string|min:10|max:500',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        Ban::create([
            'run_solicitante' => $request->run_solicitante,
            'razon' => $request->razon,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => true,
        ]);

        return redirect()->route('bans.index')
            ->with('success', 'Baneo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ban = Ban::with('solicitante')->findOrFail($id);
        return view('layouts.bans.show', compact('ban'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ban = Ban::findOrFail($id);
        $solicitantes = Solicitante::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('layouts.bans.edit', compact('ban', 'solicitantes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ban = Ban::findOrFail($id);

        $request->validate([
            'razon' => 'required|string|min:10|max:500',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'boolean',
        ]);

        $ban->update([
            'razon' => $request->razon,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('bans.index')
            ->with('success', 'Baneo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ban = Ban::findOrFail($id);
        $ban->delete();

        return redirect()->route('bans.index')
            ->with('success', 'Baneo eliminado exitosamente.');
    }

    /**
     * Desactivar un ban (unban)
     */
    public function unban(string $id)
    {
        $ban = Ban::findOrFail($id);
        $ban->update(['activo' => false]);

        return redirect()->route('bans.index')
            ->with('success', 'Solicitante desbaneado exitosamente.');
    }
}
