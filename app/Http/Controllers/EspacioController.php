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
use App\Services\QRService;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\Sede;

class EspacioController extends Controller
{
    /**
     * Muestra el listado de espacios
     */
    public function index(Request $request)
    {
        $espacios = Espacio::with('piso.facultad')->get();
        $universidades = Universidad::all();
    
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
                'id_espacio' => 'required|string|max:50|unique:espacios,id_espacio',
                'nombre_espacio' => 'required|string|max:255',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Sala de Clases,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:1',
            ]);
    
            $espacio = Espacio::create([
                'id_espacio' => $validated['id_espacio'],
                'nombre_espacio' => $validated['nombre_espacio'],
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
        $espacio = Espacio::with('piso.facultad.sede.universidad')
            ->where('id_espacio', $id_espacio)
            ->firstOrFail();

        $universidades = Universidad::all();
        $sedes = Sede::where('id_universidad', $espacio->piso->facultad->sede->id_universidad)->get();
        $facultades = Facultad::where('id_sede', $espacio->piso->facultad->id_sede)->get();
        $pisos = Piso::where('id_facultad', $espacio->piso->id_facultad)->get();

        return view('layouts.spaces.spaces_edit', compact('espacio', 'universidades', 'sedes', 'facultades', 'pisos'));
    }

    /**
     * Actualiza un espacio existente
     */
    public function update(Request $request, string $id_espacio)
    {
        try {
            $validated = $request->validate([
                'id_espacio' => 'required|string|max:50|unique:espacios,id_espacio,' . $id_espacio . ',id_espacio',
                'nombre_espacio' => 'required|string|max:255',
                'id_universidad' => 'required|exists:universidades,id_universidad',
                'id_facultad' => 'required|exists:facultades,id_facultad',
                'piso_id' => 'required|exists:pisos,id',
                'tipo_espacio' => 'required|in:Sala de Clases,Laboratorio,Biblioteca,Sala de Reuniones,Oficinas',
                'estado' => 'required|in:Disponible,Ocupado,Reservado',
                'puestos_disponibles' => 'nullable|integer|min:1',
            ]);
    
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $espacio->update([
                'id_espacio' => $validated['id_espacio'],
                'nombre_espacio' => $validated['nombre_espacio'],
                'piso_id' => $validated['piso_id'],
                'tipo_espacio' => $validated['tipo_espacio'],
                'estado' => $validated['estado'],
                'puestos_disponibles' => $validated['puestos_disponibles'],
            ]);
    
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
     * Obtiene las sedes de una universidad
     */
    public function getSedes($universidadId)
    {
        try {
            $sedes = Sede::where('id_universidad', $universidadId)->get();
            return response()->json($sedes);
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes:', [
                'universidad_id' => $universidadId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener las sedes'], 500);
        }
    }

    /**
     * Obtiene las facultades de una sede
     */
    public function getFacultadesPorSede($sedeId)
    {
        try {
            $facultades = Facultad::where('id_sede', $sedeId)->get();
            return response()->json($facultades);
        } catch (\Exception $e) {
            Log::error('Error al obtener facultades:', [
                'sede_id' => $sedeId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener las facultades'], 500);
        }
    }

    /**
     * Devuelve los módulos disponibles para reservar en un espacio
     * Solo revisa la tabla planificacion_asignaturas usando el formato id_modulo (ej: "VI.4")
     */
    public function modulosDisponibles(Request $request, $espacioId)
    {
        // Obtener día y módulo actual
        $horaActual = $request->input('hora_actual', now()->format('H:i:s'));
        $diaActual = $request->input('dia_actual', strtolower(now()->locale('es')->isoFormat('dddd')));
        
        // Mapeo de días a códigos
        $codigosDias = [
            'lunes' => 'LU',
            'martes' => 'MA', 
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
            'domingo' => 'DO'
        ];
        
        $codigoDia = $codigosDias[$diaActual] ?? 'LU';
        
        // Determinar el módulo actual según la hora
        $moduloActual = $this->determinarModuloActual($horaActual, $diaActual);
        
        if (!$moduloActual) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No hay módulo actual disponible.',
                'max_modulos' => 0,
                'detalles' => [
                    'razon' => 'fuera_horario',
                    'descripcion' => 'El sistema de reservas solo está disponible durante el horario de clases (08:10 - 23:00)'
                ]
            ]);
        }
        
        // Obtener todas las planificaciones para este espacio en este día
        $planificaciones = Planificacion_Asignatura::where('id_espacio', $espacioId)
            ->where('id_modulo', 'like', $codigoDia . '.%')
            ->pluck('id_modulo')
            ->toArray();
        
        // Obtener reservas activas para este espacio en este día
        $fechaActual = now()->toDateString();
        $reservasActivas = \App\Models\Reserva::where('id_espacio', $espacioId)
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->get();
        
        // Crear array de módulos ocupados por reservas
        $modulosOcupadosPorReservas = [];
        foreach ($reservasActivas as $reserva) {
            $horaInicio = $reserva->hora;
            $horaFin = $reserva->hora_salida;
            
            // Determinar qué módulos cubre esta reserva
            for ($i = 1; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;
                $horarioModulo = $this->obtenerHorarioModulo($i, $diaActual);
                
                if ($horarioModulo && 
                    $horaInicio <= $horarioModulo['fin'] && 
                    $horaFin >= $horarioModulo['inicio']) {
                    $modulosOcupadosPorReservas[] = $moduloCodigo;
                }
            }
        }
        
        // Combinar planificaciones y reservas activas
        $modulosOcupados = array_merge($planificaciones, $modulosOcupadosPorReservas);
        $modulosOcupados = array_unique($modulosOcupados);
        
        // Contar módulos consecutivos disponibles desde el módulo actual
        $maxModulos = 0;
        $modulosDisponibles = [];
        $proximaClase = null;
        
        for ($i = $moduloActual; $i <= 15; $i++) {
            $moduloCodigo = $codigoDia . '.' . $i;
            
            // Si existe planificación o reserva para este módulo, terminar
            if (in_array($moduloCodigo, $modulosOcupados)) {
                // Encontrar información de la próxima clase
                if (in_array($moduloCodigo, $planificaciones)) {
                    $proximaClase = $this->obtenerInfoProximaClase($moduloCodigo, $espacioId);
                }
                break;
            }
            
            $modulosDisponibles[] = $i;
            $maxModulos++;
        }
        
        // Verificar si hay clases próximas (dentro de 2 módulos)
        $clasesProximas = [];
        for ($i = $moduloActual + $maxModulos; $i <= min(15, $moduloActual + $maxModulos + 2); $i++) {
            $moduloCodigo = $codigoDia . '.' . $i;
            if (in_array($moduloCodigo, $planificaciones)) {
                $clasesProximas[] = $this->obtenerInfoProximaClase($moduloCodigo, $espacioId);
            }
        }
        
        return response()->json([
            'success' => true,
            'max_modulos' => $maxModulos,
            'modulo_actual' => $moduloActual,
            'codigo_dia' => $codigoDia,
            'modulos_disponibles' => $modulosDisponibles,
            'proxima_clase' => $proximaClase,
            'clases_proximas' => $clasesProximas,
            'detalles' => [
                'planificaciones_encontradas' => count($planificaciones),
                'reservas_activas' => count($reservasActivas),
                'modulos_ocupados' => count($modulosOcupados)
            ]
        ]);
    }
    
    /**
     * Determina el módulo actual según la hora y día
     */
    private function determinarModuloActual($horaActual, $diaActual)
    {
        // Definir horarios de módulos (mismo formato que en el frontend)
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];
        
        $horariosDia = $horariosModulos[$diaActual] ?? null;
        
        if (!$horariosDia) {
            return null;
        }
        
        // Buscar en qué módulo estamos según la hora actual
        foreach ($horariosDia as $modulo => $horario) {
            if ($horaActual >= $horario['inicio'] && $horaActual < $horario['fin']) {
                return $modulo;
            }
        }
        
        return null;
    }
    
    /**
     * Obtiene el horario de un módulo específico
     */
    private function obtenerHorarioModulo($modulo, $diaActual)
    {
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];
        
        return $horariosModulos[$diaActual][$modulo] ?? null;
    }
    
    /**
     * Obtiene información de la próxima clase programada
     */
    private function obtenerInfoProximaClase($moduloCodigo, $espacioId)
    {
        $planificacion = Planificacion_Asignatura::with(['asignatura', 'modulo', 'profesor'])
            ->where('id_espacio', $espacioId)
            ->where('id_modulo', $moduloCodigo)
            ->first();
            
        if ($planificacion) {
            return [
                'modulo' => $moduloCodigo,
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'No especificada',
                'profesor' => $planificacion->profesor->name ?? 'No especificado',
                'hora_inicio' => $planificacion->modulo->hora_inicio ?? '',
                'hora_termino' => $planificacion->modulo->hora_termino ?? ''
            ];
        }
        
        return null;
    }

    /**
     * Descarga el código QR de un espacio individual
     */
    public function downloadQR($id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $qrService = new QRService();
            
            // Generar el código QR
            $qrPath = $qrService->generateQRForEspacio($espacio->id_espacio);
            
            // Verificar si el archivo existe
            if (!Storage::disk('public')->exists($qrPath)) {
                return redirect()->back()->with('error', 'No se pudo generar el código QR.');
            }
            
            // Descargar el archivo
            return Storage::disk('public')->download($qrPath, 'QR_' . $espacio->id_espacio . '.png');
            
        } catch (\Exception $e) {
            Log::error('Error al descargar QR individual:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);
            
            return redirect()->back()->with('error', 'Error al descargar el código QR: ' . $e->getMessage());
        }
    }

    /**
     * Descarga todos los códigos QR en un archivo ZIP
     */
    public function downloadAllQR()
    {
        try {
            $espacios = Espacio::all();
            $qrService = new QRService();
            
            // Crear archivo ZIP temporal
            $zipName = 'QRs_Espacios_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipName);
            
            // Crear directorio temporal si no existe
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP.');
            }
            
            foreach ($espacios as $espacio) {
                // Generar QR para cada espacio
                $qrPath = $qrService->generateQRForEspacio($espacio->id_espacio);
                
                // Verificar si el archivo existe
                if (Storage::disk('public')->exists($qrPath)) {
                    $qrContent = Storage::disk('public')->get($qrPath);
                    $zip->addFromString('QR_' . $espacio->id_espacio . '.png', $qrContent);
                }
            }
            
            $zip->close();
            
            // Descargar el archivo ZIP
            return response()->download($zipPath, $zipName)->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            Log::error('Error al descargar QRs en ZIP:', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error al generar el archivo ZIP: ' . $e->getMessage());
        }
    }
}