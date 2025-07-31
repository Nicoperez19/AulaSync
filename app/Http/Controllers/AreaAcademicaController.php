<?php
namespace App\Http\Controllers;

use App\Models\AreaAcademica;
use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AreaAcademicaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $facultades = Facultad::with('sede.universidad')->get();
            return view('layouts.academic_area.academic_area_index', compact('facultades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las áreas académicas.'])->withInput();
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
                'tipo_area_academica' => 'required|in:departamento,escuela',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            AreaAcademica::create($validatedData);

            return redirect()->route('academic_areas.index')->with('success', 'Área Académica creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el área académica. Por favor, intente nuevamente.'])->withInput();
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
            $areaAcademica = AreaAcademica::findOrFail($id);
            $facultades = Facultad::with('sede.universidad')->get();
            
            return view('layouts.academic_area.academic_area_edit', compact('areaAcademica', 'facultades'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('academic_areas.index')->withErrors(['error' => 'Área Académica no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el área académica: ' . $e->getMessage()]);
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
                'tipo_area_academica' => 'required|in:departamento,escuela',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            $areaAcademica = AreaAcademica::findOrFail($id);
            $areaAcademica->update([
                'id_area_academica' => $request->id_area_academica,
                'nombre_area_academica' => $request->nombre_area_academica,
                'tipo_area_academica' => $request->tipo_area_academica,
                'id_facultad' => $request->id_facultad,
            ]);

            return redirect()->route('academic_areas.index')->with('success', 'Área Académica actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('academic_areas.index')->withErrors(['error' => 'Área Académica no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el área académica: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $areaAcademica = AreaAcademica::findOrFail($id);
            $areaAcademica->delete();
            return redirect()->route('academic_areas.index')->with('success', 'Área Académica eliminada exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Área Académica no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el área académica: ' . $e->getMessage()], 500);
        }
    }
}

