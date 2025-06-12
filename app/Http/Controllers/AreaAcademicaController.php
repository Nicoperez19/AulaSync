<?php
namespace App\Http\Controllers;

use App\Models\AreaAcademica;
use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AreaAcademicaController extends Controller
{
    public function index()
    {
        try {
            $areasAcademicas = AreaAcademica::with('facultad')->get();
            $facultades = Facultad::all();
            return view('layouts.academic_area.academic_area_index', compact('areasAcademicas','facultades'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al obtener las áreas académicas: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_area_academica' => 'required|string|max:20|unique:area_academicas,id_area_academica',
                'nombre_area_academica' => 'required|string|max:255',
                'tipo_area_academica' => 'required|in:departamento,escuela',
                'id_facultad' => 'required|string|max:20|exists:facultades,id_facultad',
            ]);

            AreaAcademica::create([
                'id_area_academica' => $request->id_area_academica,
                'nombre_area_academica' => $request->nombre_area_academica,
                'tipo_area_academica' => $request->tipo_area_academica,
                'id_facultad' => $request->id_facultad,
            ]);

            return redirect()->route('academic_areas.index')->with('success', 'Área Académica creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el área académica: ' . $e->getMessage()]);
        }
    }

    public function edit(string $id)
    {
        try {
            $areaAcademica = AreaAcademica::findOrFail($id);
            $facultades = Facultad::all();
            return view('layouts.academic_area.academic_area_edit', compact('areaAcademica', 'facultades'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('academic_areas.index')->withErrors(['error' => 'Área Académica no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el área académica: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nombre_area_academica' => 'required|string|max:255',
                'tipo_area_academica' => 'required|in:departamento,escuela',
                'id_facultad' => 'required|string|max:20|exists:facultades,id_facultad',
            ]);

            $areaAcademica = AreaAcademica::findOrFail($id);
            $areaAcademica->update([
                'nombre_area_academica' => $request->nombre_area_academica,
                'tipo_area_academica' => $request->tipo_area_academica,
                'id_facultad' => $request->id_facultad,
            ]);

            return redirect()->route('academic_areas.index')->with('success', 'Área Académica actualizada exitosamente.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('academic_areas.index')->withErrors(['error' => 'Área Académica no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el área académica: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $areaAcademica = AreaAcademica::findOrFail($id);
            $areaAcademica->delete();
            return redirect()->route('academic_areas.index')->with('success', 'Área Académica eliminada exitosamente.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('academic_areas.index')->withErrors(['error' => 'Área Académica no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el área académica: ' . $e->getMessage()]);
        }
    }
}
