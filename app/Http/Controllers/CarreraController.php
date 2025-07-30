<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrera;
use App\Models\AreaAcademica;
use App\Models\Facultad;
use App\Models\Universidad;

class CarreraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            $facultades = Facultad::with('sede.universidad')->get();
            $universidades = Universidad::all();
            
            return view('layouts.career.carrera_index', compact('carreras', 'areasAcademicas', 'facultades', 'universidades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las carreras.'])->withInput();
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
                'id_carrera' => 'required|string|max:20|unique:carreras,id_carrera',
                'nombre' => 'required|string|max:100',
                'id_area_academica' => 'required|exists:area_academicas,id_area_academica',
            ]);

            Carrera::create($validatedData);

            return redirect()->route('careers.index')->with('success', 'Carrera creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la carrera. Por favor, intente nuevamente.'])->withInput();
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
            $carrera = Carrera::findOrFail($id);
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            $facultades = Facultad::with('sede.universidad')->get();
            $universidades = Universidad::all();
            
            return view('layouts.career.carrera_edit', compact('carrera', 'areasAcademicas', 'facultades', 'universidades'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('careers.index')->withErrors(['error' => 'Carrera no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la carrera: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id_carrera' => 'required|string|max:20|unique:carreras,id_carrera,' . $id . ',id_carrera',
                'nombre' => 'required|string|max:100',
                'id_area_academica' => 'required|exists:area_academicas,id_area_academica',
            ]);

            $carrera = Carrera::findOrFail($id);
            $carrera->update([
                'id_carrera' => $request->id_carrera,
                'nombre' => $request->nombre,
                'id_area_academica' => $request->id_area_academica,
            ]);

            return redirect()->route('careers.index')->with('success', 'Carrera actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('careers.index')->withErrors(['error' => 'Carrera no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la carrera: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $carrera = Carrera::findOrFail($id);
            $carrera->delete();
            return redirect()->route('careers.index')->with('success', 'Carrera eliminada exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Carrera no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la carrera: ' . $e->getMessage()], 500);
        }
    }
}
