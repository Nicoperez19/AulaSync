<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use App\Models\Universidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UniversidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $universidades = Universidad::all();
        $comunas = Comuna::orderBy('nombre_comuna', 'asc')->get();
        return view('layouts.university.university_index', compact('universidades', 'comunas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre_universidad' => 'required|string|max:255',
                'direccion_universidad' => 'required|string|max:255',
                'telefono_universidad' => 'required|string|max:15',
                'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'id_comuna' => 'required|exists:comunas,id',
            ]);

            $imagenPath = null;
            if ($request->hasFile('imagen_logo')) {
                $imagenPath = $request->file('imagen_logo')->store('logos', 'public');
            }

            Universidad::create([
                'nombre_universidad' => $validatedData['nombre_universidad'],
                'direccion_universidad' => $validatedData['direccion_universidad'],
                'telefono_universidad' => $validatedData['telefono_universidad'],
                'imagen_logo' => $imagenPath,
                'id_comuna' => $validatedData['id_comuna'],
            ]);

            return redirect()->route('universidades.index')->with('success', 'Universidad creada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la universidad. Por favor, intente nuevamente.'])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $universidad = Universidad::findOrFail($id);
        $comunas = Comuna::orderBy('nombre_comuna', 'asc')->get();
        return view('layouts.university.university_edit', compact('universidad', 'comunas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    try {
        $validatedData = $request->validate([
            'nombre_universidad' => 'required|string|max:255',
            'direccion_universidad' => 'required|string|max:255',
            'telefono_universidad' => 'required|string|max:15',
            'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'id_comuna' => 'required|exists:comunas,id',
        ]);

        $universidad = Universidad::findOrFail($id);

        if ($request->hasFile('imagen_logo')) {
            // Elimina la imagen antigua si existe
            if ($universidad->imagen_logo) {
                Storage::disk('public')->delete($universidad->imagen_logo);
            }
            $imagenPath = $request->file('imagen_logo')->store('logos', 'public');
        } else {
            $imagenPath = $universidad->imagen_logo;
        }

        $universidad->update([
            'nombre_universidad' => $validatedData['nombre_universidad'],
            'direccion_universidad' => $validatedData['direccion_universidad'],
            'telefono_universidad' => $validatedData['telefono_universidad'],
            'imagen_logo' => $imagenPath,
            'id_comuna' => $validatedData['id_comuna'],
        ]);

        return redirect()->route('universidades.index')->with('success', 'Universidad actualizada exitosamente.');

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Hubo un problema al actualizar la universidad. Por favor, intente nuevamente.'])->withInput();
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $universidad = Universidad::findOrFail($id);
            $universidad->delete();
            return redirect()->route('universitys.index')->with('success', 'Universidad eliminado exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Universidad no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la Universidad: ' . $e->getMessage()], 500);
        }
    }
}
