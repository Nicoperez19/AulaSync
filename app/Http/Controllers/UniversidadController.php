<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use App\Models\Universidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UniversidadController extends Controller
{
    public function index()
    {
        $universidades = Universidad::all();
        $comunas = Comuna::all();
        return view('layouts.university.university_index', compact('universidades', 'comunas'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_universidad' => 'required|string|max:255',
            'nombre_universidad' => 'required|string|max:255',
            'direccion_universidad' => 'required|string|max:255',
            'telefono_universidad' => 'required|regex:/^[0-9+]+$/|max:15',
            'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'comunas_id' => 'required|exists:comunas,id',
        ]);

        $imagenPath = null;

        if ($request->hasFile('imagen_logo')) {
            $file = $request->file('imagen_logo');
            $nombreArchivo = str_replace(' ', '_', strtolower($validatedData['nombre_universidad'])) . '.' . $file->getClientOriginalExtension();
            $imagenPath = $file->storeAs('imagenes/logo_universidad', $nombreArchivo, 'public');
        }

        Universidad::create([
            'id_universidad' => $validatedData['id_universidad'],
            'nombre_universidad' => $validatedData['nombre_universidad'],
            'direccion_universidad' => $validatedData['direccion_universidad'],
            'telefono_universidad' => $validatedData['telefono_universidad'],
            'imagen_logo' => $imagenPath,
            'comunas_id' => $validatedData['comunas_id'],
        ]);

        return redirect()->route('universities.index')->with('success', 'Universidad creada exitosamente.');
    }

    public function edit($id)
    {
        $universidad = Universidad::findOrFail($id);
        $comunas = Comuna::orderBy('nombre_comuna', 'asc')->get();
        return view('layouts.university.university_edit', compact('universidad', 'comunas'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'id_universidad' => 'required|string|max:255',
            'nombre_universidad' => 'required|string|max:255',
            'direccion_universidad' => 'required|string|max:255',
            'telefono_universidad' => 'required|regex:/^[0-9+]+$/|max:15',
            'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'comunas_id' => 'required|exists:comunas,id',
        ]);

        $universidad = Universidad::findOrFail($id);
        $imagenPath = $universidad->imagen_logo;

        if ($request->hasFile('imagen_logo')) {
            if ($universidad->imagen_logo && Storage::disk('public')->exists($universidad->imagen_logo)) {
                Storage::disk('public')->delete($universidad->imagen_logo);
            }

            // Guardar la nueva imagen
            $file = $request->file('imagen_logo');
            $nombreArchivo = str_replace(' ', '_', strtolower($validatedData['nombre_universidad'])) . '.' . $file->getClientOriginalExtension();
            $imagenPath = $file->storeAs('imagenes/logo_universidad', $nombreArchivo, 'public');
        }

        $universidad->update([
            'id_universidad' => $validatedData['id_universidad'],
            'nombre_universidad' => $validatedData['nombre_universidad'],
            'direccion_universidad' => $validatedData['direccion_universidad'],
            'telefono_universidad' => $validatedData['telefono_universidad'],
            'imagen_logo' => $imagenPath,
            'comunas_id' => $validatedData['comunas_id'],
        ]);

        return redirect()->route('universities.index')->with('success', 'Universidad actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $universidad = Universidad::findOrFail($id);

        if ($universidad->imagen_logo && Storage::disk('public')->exists($universidad->imagen_logo)) {
            Storage::disk('public')->delete($universidad->imagen_logo);
        }

        $universidad->delete();

        return redirect()->route('universities.index')->with('success', 'Universidad eliminada exitosamente.');
    }
}
