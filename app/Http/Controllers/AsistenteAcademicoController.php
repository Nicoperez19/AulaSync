<?php

namespace App\Http\Controllers;

use App\Models\AsistenteAcademico;
use App\Models\AreaAcademica;
use Illuminate\Http\Request;

class AsistenteAcademicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Only show escuelas (schools), not departments
            $escuelas = AreaAcademica::where('tipo_area_academica', 'escuela')
                ->with('facultad.sede.universidad')
                ->get();
            return view('layouts.asistentes_academicos.asistente_academico_index', compact('escuelas'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los asistentes académicos.'])->withInput();
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
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|unique:asistentes_academicos,email',
                'telefono' => 'nullable|string|max:20',
                'id_area_academica' => 'required|exists:area_academicas,id_area_academica',
            ]);

            AsistenteAcademico::create($validatedData);

            return redirect()->route('asistentes-academicos.index')->with('success', 'Asistente académico creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el asistente académico. Por favor, intente nuevamente.'])->withInput();
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
            $asistenteAcademico = AsistenteAcademico::with('areaAcademica')->findOrFail($id);
            $escuelas = AreaAcademica::where('tipo_area_academica', 'escuela')
                ->with('facultad.sede.universidad')
                ->get();
            return view('layouts.asistentes_academicos.asistente_academico_edit', compact('asistenteAcademico', 'escuelas'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('asistentes-academicos.index')->withErrors(['error' => 'Asistente académico no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el asistente académico: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|unique:asistentes_academicos,email,' . $id,
                'telefono' => 'nullable|string|max:20',
                'id_area_academica' => 'required|exists:area_academicas,id_area_academica',
            ]);

            $asistenteAcademico = AsistenteAcademico::findOrFail($id);
            $asistenteAcademico->update([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'id_area_academica' => $request->id_area_academica,
            ]);

            return redirect()->route('asistentes-academicos.index')->with('success', 'Asistente académico actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('asistentes-academicos.index')->withErrors(['error' => 'Asistente académico no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el asistente académico: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $asistenteAcademico = AsistenteAcademico::findOrFail($id);
            $asistenteAcademico->delete();
            return redirect()->route('asistentes-academicos.index')->with('success', 'Asistente académico eliminado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Asistente académico no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el asistente académico: ' . $e->getMessage()], 500);
        }
    }
}
