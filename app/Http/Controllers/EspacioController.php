<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use Illuminate\Http\Request;

class EspacioController extends Controller
{
    // Mostrar los espacios y filtrar por facultad y piso si es necesario
    public function index(Request $request)
    {
        $facultades = Facultad::all(); // Obtener todas las facultades

        $pisos = Piso::when($request->facultad_filter, function ($query, $facultadId) {
            return $query->where('id_facultad', $facultadId);
        })->get();

        $espacios = Espacio::with('piso.facultad')
            ->when($request->facultad_filter, function ($query, $facultadId) {
                return $query->whereHas('piso', function ($query) use ($facultadId) {
                    return $query->where('id_facultad', $facultadId);
                });
            })
            ->when($request->piso_filter, function ($query, $pisoId) {
                return $query->where('id', $pisoId);
            })
            ->get();

        return view('layouts.spaces.spaces_index', compact('espacios', 'pisos', 'facultades'));
    }

    // Crear un nuevo espacio
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:pisos,id',  // Verifica que el id del piso exista en la tabla pisos
                'tipo_espacio' => 'required|in:Aula,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:0',
            ]);

            Espacio::create([
                'id' => $request->id,
                'tipo_espacio' => $request->tipo_espacio,
                'estado' => $request->estado,
                'puestos_disponibles' => $request->puestos_disponibles,
            ]);

            return redirect()->route('espacios.index')->with('success', 'Espacio creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('espacios.index')->with('error', 'Error al crear el espacio: ' . $e->getMessage());
        }
    }

    // Mostrar el formulario para editar un espacio
    public function edit(string $id_espacio)
    {
        $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
        $pisos = Piso::all();
        return view('layouts.spaces.edit', compact('espacio', 'pisos'));
    }

    // Actualizar el espacio
    public function update(Request $request, string $id_espacio)
    {
        try {
            $request->validate([
                'id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Aula,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:0',
            ]);

            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->update([
                'id' => $request->piso_id,
                'tipo_espacio' => $request->tipo_espacio,
                'estado' => $request->estado,
                'puestos_disponibles' => $request->puestos_disponibles,
            ]);

            return redirect()->route('espacios.index')->with('success', 'Espacio actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('espacios.index')->with('error', 'Error al actualizar el espacio: ' . $e->getMessage());
        }
    }

    public function destroy(string $id_espacio)
    {
        // dd($id_espacio); // Verifica que el valor recibido es válido
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->delete();

            return redirect()->route('espacios.index')->with('success', 'Espacio eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('espacios.index')->with('error', 'Error al eliminar el espacio: ' . $e->getMessage());
        }
    }


    // Obtener pisos por facultad (para uso en filtrados dinámicos)
    public function getPisosByFacultad($facultadId)
    {
        $pisos = Piso::where('id_facultad', $facultadId)->get();

        if ($pisos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron pisos para esta facultad.'], 404);
        }

        return response()->json(['pisos' => $pisos]);
    }
}