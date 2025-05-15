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
        $facultades = Facultad::with('universidad')->get();
        $universidades = Universidad::all();
        return view('layouts.faculty.facultad_index', compact('facultades', 'universidades'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_facultad' => 'required|string|max:255',
                'nombre_facultad' => 'required|string|max:255',
                'ubicacion_facultad' => 'required|string|max:255',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'logo_facultad' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            $imagenNombre = null;

            if ($request->hasFile('logo_facultad')) {
                $file = $request->file('logo_facultad');
                $imagenNombre = str_replace(' ', '_', strtolower($validatedData['nombre_facultad'])) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/logo_facultad'), $imagenNombre);
            }

            Facultad::create([
                'id_facultad' => $validatedData['id_facultad'],
                'nombre_facultad' => $validatedData['nombre_facultad'],
                'ubicacion_facultad' => $validatedData['ubicacion_facultad'],
                'id_universidad' => $validatedData['id_universidad'],
                'logo_facultad' => $imagenNombre,
            ]);

            return redirect()->route('faculties.index')->with('success', 'Facultad creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('faculties.index')->with('error', 'Ocurrió un error al crear la facultad. Intenta de nuevo.');
        }
    }

    public function edit($id)
    {
        $facultad = Facultad::findOrFail($id);
        $universidades = Universidad::all();
        return view('layouts.faculty.facultad_edit', compact('facultad', 'universidades'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre_facultad' => 'required|string|max:255',
                'ubicacion_facultad' => 'required|string|max:255',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'logo_facultad' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            $facultad = Facultad::findOrFail($id);
            $imagenNombre = $facultad->logo_facultad;

            if ($request->hasFile('logo_facultad')) {
                if ($facultad->logo_facultad && file_exists(public_path('images/logo_facultad/' . $facultad->logo_facultad))) {
                    unlink(public_path('images/logo_facultad/' . $facultad->logo_facultad));
                }

                $file = $request->file('logo_facultad');
                $imagenNombre = str_replace(' ', '_', strtolower($validatedData['nombre_facultad'])) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/logo_facultad'), $imagenNombre);
            }

            $facultad->update([
                'nombre_facultad' => $validatedData['nombre_facultad'],
                'ubicacion_facultad' => $validatedData['ubicacion_facultad'],
                'id_universidad' => $validatedData['id_universidad'],
                'logo_facultad' => $imagenNombre,
            ]);

            return redirect()->route('faculties.index')->with('success', 'Facultad actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('faculties.index')->with('error', 'Ocurrió un error al actualizar la facultad. Intenta de nuevo.');
        }
    }

    public function destroy($id)
    {
        try {
            $facultad = Facultad::findOrFail($id);

            if ($facultad->logo_facultad && file_exists(public_path('images/logo_facultad/' . $facultad->logo_facultad))) {
                unlink(public_path('images/logo_facultad/' . $facultad->logo_facultad));
            }

            $facultad->delete();

            return redirect()->route('faculties.index')->with('success', 'Facultad eliminada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('faculties.index')->with('error', 'Ocurrió un error al eliminar la facultad. Intenta de nuevo.');
        }
    }
}
