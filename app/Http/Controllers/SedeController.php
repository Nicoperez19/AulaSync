<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Universidad;
use App\Models\Comuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SedeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $universidades = Universidad::all();
            $comunas = Comuna::all();
            
            return view('layouts.sedes.sede_index', compact('universidades', 'comunas'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las sedes.'])->withInput();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_sede' => 'required|string|max:20|unique:sedes,id_sede',
                'nombre_sede' => 'required|string|max:100',
                'prefijo_sala' => 'nullable|string|max:10',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'comuna_id' => 'required|exists:comunas,id',
            ]);

            Sede::create($validatedData);

            return redirect()->route('sedes.index')->with('success', 'Sede creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la sede. Por favor, intente nuevamente.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $sede = Sede::findOrFail($id);
            $universidades = Universidad::all();
            $comunas = Comuna::all();
            
            return view('layouts.sedes.sede_edit', compact('sede', 'universidades', 'comunas'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('sedes.index')->withErrors(['error' => 'Sede no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la sede: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id_sede' => 'required|string|max:20|unique:sedes,id_sede,' . $id . ',id_sede',
                'nombre_sede' => 'required|string|max:100',
                'prefijo_sala' => 'nullable|string|max:10',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'comuna_id' => 'required|exists:comunas,id',
            ]);

            $sede = Sede::findOrFail($id);
            $sede->update([
                'id_sede' => $request->id_sede,
                'nombre_sede' => $request->nombre_sede,
                'prefijo_sala' => $request->prefijo_sala,
                'id_universidad' => $request->id_universidad,
                'comuna_id' => $request->comuna_id,
            ]);

            return redirect()->route('sedes.index')->with('success', 'Sede actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('sedes.index')->withErrors(['error' => 'Sede no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la sede: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $sede = Sede::findOrFail($id);
            $sede->delete();
            return redirect()->route('sedes.index')->with('success', 'Sede eliminada exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Sede no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la sede: ' . $e->getMessage()], 500);
        }
    }
} 