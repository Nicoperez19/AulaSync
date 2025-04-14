<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Universidad;

class CarreraController extends Controller
{
    public function index()
    {
        try {
            $universidades = Universidad::all();
            $carreras = Carrera::with('facultad')->get();
            $facultades = Facultad::all();
            return view('layouts.career.carrera_index', compact('carreras', 'facultades','universidades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las carreras.'])->withInput();
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_carrera' => 'required|string|max:255',
                'nombre' => 'required|string|max:255',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            Carrera::create($validatedData);

            return redirect()->route('careers.index')->with('success', 'Carrera creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la carrera. Por favor, intente nuevamente.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $carrera = Carrera::findOrFail($id);
            $facultades = Facultad::all();
            return view('layouts.career.carrera_edit', compact('carrera', 'facultades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los datos de la carrera.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'id_facultad' => 'required|exists:facultades,id_facultad',
            ]);

            $carrera = Carrera::findOrFail($id);
            $carrera->update($validatedData);

            return redirect()->route('careers.index')->with('success', 'Carrera actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al actualizar la carrera.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $carrera = Carrera::findOrFail($id);
            $carrera->delete();
    
            return redirect()->route('careers.index')->with('success', 'Carrera eliminada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Carrera no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la carrera: ' . $e->getMessage()], 500);
        }
    }
}
