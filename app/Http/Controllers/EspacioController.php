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
        $espacios = Espacio::with('piso.facultad.universidad')->get();
    
        return view('layouts.spaces.spaces_index', compact('espacios', 'universidades'));
    }


    public function store(Request $request)
    {
        \Log::info('Datos recibidos:', $request->all());
    
        try {
            $validated = $request->validate([
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Aula,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'required|integer|min:1',
            ]);
    
            $espacio = Espacio::create([
                'id_espacio' => 'ESP-'.uniqid(),
                'piso_id' => $validated['piso_id'], // Asegúrate de incluir esto
                'tipo_espacio' => $validated['tipo_espacio'],
                'estado' => $validated['estado'],
                'puestos_disponibles' => $validated['puestos_disponibles'],
            ]);
    
            return redirect()->route('spaces_index')
                   ->with('success', 'Espacio creado exitosamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error completo:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                   ->withInput()
                   ->with('error', 'Error al crear el espacio: '.$e->getMessage());
        }
    }


    public function edit(string $id_espacio)
    {
        $espacio = Espacio::with('piso.facultad.universidad')->where('id_espacio', $id_espacio)->firstOrFail();

        $universidades = Universidad::all();
        $facultades = Facultad::where('id_universidad', $espacio->piso->facultad->id_universidad)->get();
        $pisos = Piso::where('id_facultad', $espacio->piso->id_facultad)->get();


        return view('layouts.spaces.spaces_edit', compact('espacio', 'universidades', 'facultades', 'pisos'));
    }

    public function update(Request $request, string $id_espacio)
    {
        try {
            $request->validate([
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Aula,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:0',
            ]);
    
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->update([
                'piso_id' => $request->piso_id,
                'tipo_espacio' => $request->tipo_espacio,
                'estado' => $request->estado,
                'puestos_disponibles' => $request->puestos_disponibles,
            ]);
    
            return redirect()->route('spaces_index')->with('success', 'Espacio actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('spaces_index')->with('error', 'Error al actualizar el espacio: ' . $e->getMessage());
        }
    }

    public function destroy(string $id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->delete();

            return redirect()->route('spaces_index')->with('success', 'Espacio eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('spaces_index')->with('error', 'Error al eliminar el espacio: ' . $e->getMessage());
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
        return Espacio::where('piso_id', $pisoId)->get();
    }

}