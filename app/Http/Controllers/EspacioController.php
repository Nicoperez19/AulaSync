<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Universidad;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EspacioController extends Controller
{
    /**
     * Muestra el listado de espacios
     */
    public function index(Request $request)
    {
        $universidades = Universidad::all();
        $espacios = Espacio::with('piso.facultad.universidad')->get();
    
        return view('layouts.spaces.spaces_index', compact('espacios', 'universidades'));
    }

    /**
     * Almacena un nuevo espacio
     */
    public function store(Request $request)
    {
        Log::info('Datos recibidos:', $request->all());
    
        try {
            $validated = $request->validate([
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Sala de Clases,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'required|integer|min:1',
            ]);
    
            $espacio = Espacio::create([
                'id_espacio' => 'ESP-'.uniqid(),
                'piso_id' => $validated['piso_id'],
                'tipo_espacio' => $validated['tipo_espacio'],
                'estado' => $validated['estado'],
                'puestos_disponibles' => $validated['puestos_disponibles'],
            ]);
    
            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio creado exitosamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al crear espacio:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el espacio: '.$e->getMessage());
        }
    }

    /**
     * Muestra el formulario de edición de un espacio
     */
    public function edit(string $id_espacio)
    {
        $espacio = Espacio::with('piso.facultad.universidad')
            ->where('id_espacio', $id_espacio)
            ->firstOrFail();

        $universidades = Universidad::all();
        $facultades = Facultad::where('id_universidad', $espacio->piso->facultad->id_universidad)->get();
        $pisos = Piso::where('id_facultad', $espacio->piso->id_facultad)->get();

        return view('layouts.spaces.spaces_edit', compact('espacio', 'universidades', 'facultades', 'pisos'));
    }

    /**
     * Actualiza un espacio existente
     */
    public function update(Request $request, string $id_espacio)
    {
        try {
            $validated = $request->validate([
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Sala de Clases,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:0',
            ]);
    
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->update($validated);
    
            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio actualizado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar espacio:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);
            
            return redirect()
                ->route('spaces_index')
                ->with('error', 'Error al actualizar el espacio: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un espacio
     */
    public function destroy(string $id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->delete();

            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio eliminado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar espacio:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);
            
            return redirect()
                ->route('spaces_index')
                ->with('error', 'Error al eliminar el espacio: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las facultades de una universidad
     */
    public function getFacultades($universidadId)
    {
        return Facultad::where('id_universidad', $universidadId)->get();
    }

    /**
     * Obtiene los pisos de una facultad
     */
    public function getPisos($facultadId)
    {
        return Piso::where('id_facultad', $facultadId)->get();
    }

    /**
     * Obtiene los espacios de un piso
     */
    public function getEspacios($pisoId)
    {
        return Espacio::where('piso_id', $pisoId)->get();
    }

    /**
     * Devuelve los módulos disponibles para reservar en un espacio
     * Solo devuelve el primer bloque de módulos consecutivos libres
     * También evalúa si hay clase en el siguiente módulo para marcarlo como próximo
     */
    public function modulosDisponibles(Request $request, $espacioId)
    {
        $horaActual = $request->input('hora_actual');
        $diaActual = $request->input('dia_actual');

        // Buscar módulos futuros del día, ordenados por hora de inicio
        $modulos = Modulo::where('dia', $diaActual)
            ->where('hora_termino', '>', $horaActual)
            ->orderBy('hora_inicio')
            ->get();

        $modulosDisponibles = [];
        $enRacha = false;

        // Buscar el primer bloque de módulos consecutivos libres
        foreach ($modulos as $modulo) {
            $hayClase = Planificacion_Asignatura::where('id_espacio', $espacioId)
                ->where('id_modulo', $modulo->id_modulo)
                ->exists();
            if (!$hayClase) {
                $modulosDisponibles[] = [
                    'id_modulo' => $modulo->id_modulo,
                    'numero' => explode('.', $modulo->id_modulo)[1] ?? $modulo->id_modulo,
                    'hora_inicio' => $modulo->hora_inicio,
                    'hora_termino' => $modulo->hora_termino
                ];
                $enRacha = true;
            } else {
                if ($enRacha) break;
            }
        }

        // Si hay módulos disponibles, devolverlos
        if (!empty($modulosDisponibles)) {
            return response()->json([
                'success' => true,
                'modulos' => $modulosDisponibles,
                'es_proximo' => false,
                'siguiente_modulo' => null
            ]);
        }

        // Si no hay módulos disponibles, buscar el siguiente módulo y ver si tiene clase programada
        $siguienteModulo = $modulos->first();
        $esProximo = false;
        $siguienteModuloData = null;
        if ($siguienteModulo) {
            $hayClaseEnSiguiente = Planificacion_Asignatura::where('id_espacio', $espacioId)
                ->where('id_modulo', $siguienteModulo->id_modulo)
                ->exists();
            if ($hayClaseEnSiguiente) {
                $esProximo = true;
                $siguienteModuloData = [
                    'id_modulo' => $siguienteModulo->id_modulo,
                    'hora_inicio' => $siguienteModulo->hora_inicio,
                    'hora_termino' => $siguienteModulo->hora_termino
                ];
            }
        }

        return response()->json([
            'success' => false,
            'mensaje' => 'No hay módulos disponibles para reservar.',
            'es_proximo' => $esProximo,
            'siguiente_modulo' => $siguienteModuloData
        ]);
    }
}