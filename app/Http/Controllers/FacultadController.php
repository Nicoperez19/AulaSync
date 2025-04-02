<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facultad;
use App\Models\Universidad;
use Illuminate\Support\Facades\Storage;

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
        $validatedData = $request->validate([
            'id_facultad' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'id_universidad' => 'required|exists:universidades,id_universidad',
            'logo_facultad' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $imagenPath = null;
        if ($request->hasFile('logo_facultad')) {
            $file = $request->file('logo_facultad');
            $nombreArchivo = str_replace(' ', '_', strtolower($validatedData['nombre'])) . '.' . $file->getClientOriginalExtension();
            $imagenPath = $file->storeAs('imagenes/logo_facultad', $nombreArchivo, 'public');
        }

        Facultad::create([
            'id_facultad' => $validatedData['id_facultad'],
            'nombre' => $validatedData['nombre'],
            'ubicacion' => $validatedData['ubicacion'],
            'id_universidad' => $validatedData['id_universidad'],
            'logo_facultad' => $imagenPath,
        ]);

        return redirect()->route('layouts.faculty.facultad_index')->with('success', 'Facultad creada exitosamente.');
    }

    public function edit($id)
    {
        $facultad = Facultad::findOrFail($id);
        $universidades = Universidad::all();
        return view('layouts.faculty.facultad_edit', compact('facultad', 'universidades'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'id_universidad' => 'required|exists:universidades,id_universidad',
            'logo_facultad' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $facultad = Facultad::findOrFail($id);
        $imagenPath = $facultad->logo_facultad;

        if ($request->hasFile('logo_facultad')) {
            if ($facultad->logo_facultad && Storage::disk('public')->exists($facultad->logo_facultad)) {
                Storage::disk('public')->delete($facultad->logo_facultad);
            }
            $file = $request->file('logo_facultad');
            $nombreArchivo = str_replace(' ', '_', strtolower($validatedData['nombre'])) . '.' . $file->getClientOriginalExtension();
            $imagenPath = $file->storeAs('imagenes/logo_facultad', $nombreArchivo, 'public');
        }

        $facultad->update([
            'nombre' => $validatedData['nombre'],
            'ubicacion' => $validatedData['ubicacion'],
            'id_universidad' => $validatedData['id_universidad'],
            'logo_facultad' => $imagenPath,
        ]);

        return redirect()->route('faculties.index')->with('success', 'Facultad eliminada exitosamente.');
    }

    public function destroy($id)
    {
        $facultad = Facultad::findOrFail($id);

        if ($facultad->logo_facultad && Storage::disk('public')->exists($facultad->logo_facultad)) {
            Storage::disk('public')->delete($facultad->logo_facultad);
        }

        $facultad->delete();

        return redirect()->route('faculties.index')->with('success', 'Facultad eliminada exitosamente.');
    }
}
