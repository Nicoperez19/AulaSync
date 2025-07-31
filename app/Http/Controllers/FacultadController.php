<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\Universidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacultadController extends Controller
{
    public function index()
    {
        $universidades = Universidad::all();
        $sedes = \App\Models\Sede::all();
        $campuses = \App\Models\Campus::all();
        return view('layouts.faculty.facultad_index', compact('universidades', 'sedes', 'campuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_facultad' => 'required|string|max:20|unique:facultades,id_facultad',
            'nombre_facultad' => 'required|string|max:100',
            'id_universidad' => 'required|exists:universidades,id_universidad',
            'id_sede' => 'required|exists:sedes,id_sede',
            'id_campus' => 'nullable|exists:campuses,id_campus'
        ]);

        try {
            $facultad = new Facultad();
            $facultad->id_facultad = $request->id_facultad;
            $facultad->nombre_facultad = $request->nombre_facultad;
            $facultad->id_universidad = $request->id_universidad;
            $facultad->id_sede = $request->id_sede;
            $facultad->id_campus = $request->id_campus;

            $facultad->save();

            return redirect()->route('faculties.index')->with('success', 'Facultad creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al crear la facultad. Intenta de nuevo.');
        }
    }

    public function edit($id)
    {
        $facultad = Facultad::findOrFail($id);
        $universidades = Universidad::all();
        $sedes = \App\Models\Sede::all();
        $campuses = \App\Models\Campus::all();
        return view('layouts.faculty.facultad_edit', compact('facultad', 'universidades', 'sedes', 'campuses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_facultad' => 'required|string|max:20|unique:facultades,id_facultad,' . $id . ',id_facultad',
            'nombre_facultad' => 'required|string|max:100',
            'id_universidad' => 'required|exists:universidades,id_universidad',
            'id_sede' => 'required|exists:sedes,id_sede',
            'id_campus' => 'nullable|exists:campuses,id_campus'
        ]);

        try {
            $facultad = Facultad::findOrFail($id);
            $facultad->id_facultad = $request->id_facultad;
            $facultad->nombre_facultad = $request->nombre_facultad;
            $facultad->id_universidad = $request->id_universidad;
            $facultad->id_sede = $request->id_sede;
            $facultad->id_campus = $request->id_campus;

            $facultad->save();

            return redirect()->route('faculties.index')->with('success', 'Facultad actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar la facultad. Intenta de nuevo.');
        }
    }

    public function destroy($id)
    {
        try {
            $facultad = Facultad::findOrFail($id);
            $facultad->delete();

            return redirect()->route('faculties.index')->with('success', 'Facultad eliminada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('faculties.index')->with('error', 'Ocurrió un error al eliminar la facultad. Intenta de nuevo.');
        }
    }
}
