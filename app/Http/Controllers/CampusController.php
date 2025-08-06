<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Sede;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $sedes = Sede::with('universidad')->get();
            
            return view('layouts.campus.campus_index', compact('sedes'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los campus.'])->withInput();
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
                'id_campus' => 'required|string|max:20|unique:campuses,id_campus',
                'nombre_campus' => 'required|string|max:100',
                'id_sede' => 'required|exists:sedes,id_sede',
            ]);

            Campus::create($validatedData);

            return redirect()->route('campus.index')->with('success', 'Campus creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el campus. Por favor, intente nuevamente.'])->withInput();
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
            $campus = Campus::findOrFail($id);
            $sedes = Sede::with('universidad')->get();
            
            return view('layouts.campus.campus_edit', compact('campus', 'sedes'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('campus.index')->withErrors(['error' => 'Campus no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el campus: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id_campus' => 'required|string|max:20|unique:campuses,id_campus,' . $id . ',id_campus',
                'nombre_campus' => 'required|string|max:100',
                'id_sede' => 'required|exists:sedes,id_sede',
            ]);

            $campus = Campus::findOrFail($id);
            $campus->update([
                'id_campus' => $request->id_campus,
                'nombre_campus' => $request->nombre_campus,
                'id_sede' => $request->id_sede,
            ]);

            return redirect()->route('campus.index')->with('success', 'Campus actualizado exitosamente.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('campus.index')->withErrors(['error' => 'Campus no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el campus: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $campus = Campus::findOrFail($id);
            $campus->delete();
            return redirect()->route('campus.index')->with('success', 'Campus eliminado exitosamente.');

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Campus no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el campus: ' . $e->getMessage()], 500);
        }
    }
} 