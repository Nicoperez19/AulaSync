<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Universidad;

use Illuminate\Http\Request;

class EspacioController extends Controller
{
    public function index(Request $request)
    {
        $universidades = Universidad::all();
        $espacios = Espacio::with('piso.facultad.universidad');

        return view('layouts.spaces.spaces_index', compact('espacios', 'universidades'));
    }


    public function store(Request $request)
    {
        try {
            // Validación
            $request->validate([
                'id' => 'required|exists:pisos,id',  // Verifica que el id del piso exista
                'tipo_espacio' => 'required|in:Aula,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:0',
            ]);

            // Generar un id único para el espacio
            $id_espacio = strtoupper(uniqid('ESP-', true));  // Este es un ejemplo, puedes personalizarlo

            // Crear el espacio
            Espacio::create([
                'id_espacio' => $id_espacio,  // Usamos el id generado
                'id' => $request->id,  // Relación con el piso
                'tipo_espacio' => $request->tipo_espacio,
                'estado' => $request->estado,
                'puestos_disponibles' => $request->puestos_disponibles,
            ]);

            return redirect()->route('layouts.spaces.spaces_index')->with('success', 'Espacio creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('layouts.spaces.spaces_index')->with('error', 'Error al crear el espacio: ' . $e->getMessage());
        }
    }


    public function edit(string $id_espacio)
    {
        $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();

        // Fetch all universities
        $universidades = Universidad::all();

        // Fetch faculties based on the university of the space
        $facultades = Facultad::where('id_universidad', $espacio->piso->facultad->universidad->id)->get();

        // Fetch all pisos
        $pisos = Piso::all();

        return view('layouts.spaces.spaces_edit', compact('espacio', 'pisos', 'universidades', 'facultades'));
    }

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
                'id' => $request->id,
                'tipo_espacio' => $request->tipo_espacio,
                'estado' => $request->estado,
                'puestos_disponibles' => $request->puestos_disponibles,
            ]);

            return redirect()->route('layouts.spaces.spaces_index')->with('success', 'Espacio actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('layouts.spaces.spaces_index')->with('error', 'Error al actualizar el espacio: ' . $e->getMessage());
        }
    }

    public function destroy(string $id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->delete();

            return redirect()->route('layouts.spaces.spaces_index')->with('success', 'Espacio eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('layouts.spaces.spaces_index')->with('error', 'Error al eliminar el espacio: ' . $e->getMessage());
        }
    }


    public function getFacultades($universidadId)
    {
        return Facultad::where('id_universidad', $universidadId)->get();
    }

    public function getPisos($facultadId)
    {
        return Piso::where('id_facultad', $facultadId)->get();
    }
    public function getEspacios($pisoId)
    {
        return Espacio::where('id', $pisoId)->get();
    }

}