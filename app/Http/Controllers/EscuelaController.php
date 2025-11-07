<?php

namespace App\Http\Controllers;

use App\Models\AreaAcademica;
use App\Models\Facultad;
use App\Models\Universidad;
use Illuminate\Http\Request;

class EscuelaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Filter only schools (tipo_area_academica = 'escuela')
            $escuelas = AreaAcademica::where('tipo_area_academica', 'escuela')
                ->with('facultad.sede.universidad')
                ->get();
            $facultades = Facultad::with('sede.universidad')->get();
            $universidades = Universidad::all();
            
            return view('layouts.escuelas.escuela_index', compact('escuelas', 'facultades', 'universidades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las escuelas.'])->withInput();
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
                'id_area_academica' => 'required|string|max:20|unique:area_academicas,id_area_academica',
                'nombre_area_academica' => 'required|string|max:255',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            // Force tipo_area_academica to 'escuela'
            $validatedData['tipo_area_academica'] = 'escuela';

            AreaAcademica::create($validatedData);

            return redirect()->route('escuelas.index')->with('success', 'Escuela creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la escuela. Por favor, intente nuevamente.'])->withInput();
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
            $escuela = AreaAcademica::where('tipo_area_academica', 'escuela')->findOrFail($id);
            $facultades = Facultad::with('sede.universidad')->get();
            $universidades = Universidad::all();
            
            return view('layouts.escuelas.escuela_edit', compact('escuela', 'facultades', 'universidades'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('escuelas.index')->withErrors(['error' => 'Escuela no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la escuela: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id_area_academica' => 'required|string|max:20|unique:area_academicas,id_area_academica,' . $id . ',id_area_academica',
                'nombre_area_academica' => 'required|string|max:255',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            $escuela = AreaAcademica::where('tipo_area_academica', 'escuela')->findOrFail($id);
            $escuela->update([
                'id_area_academica' => $request->id_area_academica,
                'nombre_area_academica' => $request->nombre_area_academica,
                'id_facultad' => $request->id_facultad,
                'tipo_area_academica' => 'escuela', // Ensure it stays as 'escuela'
            ]);

            return redirect()->route('escuelas.index')->with('success', 'Escuela actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('escuelas.index')->withErrors(['error' => 'Escuela no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la escuela: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $escuela = AreaAcademica::where('tipo_area_academica', 'escuela')->findOrFail($id);
            $escuela->delete();
            return redirect()->route('escuelas.index')->with('success', 'Escuela eliminada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Escuela no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la escuela: ' . $e->getMessage()], 500);
        }
    }
}
