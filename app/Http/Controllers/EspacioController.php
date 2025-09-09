<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Universidad;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Solicitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\QRService;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\Sede;
use App\Models\User;
use App\Models\Profesor;
use App\Models\Reserva;

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

        // Log para debugging
        Log::info('modulosDisponibles - Parámetros recibidos:', [
            'espacioId' => $espacioId,
            'horaActual' => $horaActual,
            'diaActual' => $diaActual
        ]);

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

        // Log para debugging del módulo actual
        Log::info('modulosDisponibles - Módulo actual determinado:', [
            'moduloActual' => $moduloActual,
            'horaActual' => $horaActual,
            'diaActual' => $diaActual
        ]);

        if (!$moduloActual) {
            Log::warning('modulosDisponibles - No se pudo determinar el módulo actual', [
                'horaActual' => $horaActual,
                'diaActual' => $diaActual
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No hay módulo actual disponible.',
                'max_modulos' => 0,
                'modulo_actual' => null,
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
        $reservasActivas = Reserva::where('id_espacio', $espacioId)
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

        // Construir detalle por módulo con horario inicio/fin
        $modulosDetalle = [];
        foreach ($modulosDisponibles as $m) {
            $horario = $this->obtenerHorarioModulo($m, $diaActual);
            $modulosDetalle[] = [
                'modulo' => $m,
                'inicio' => $horario['inicio'] ?? null,
                'fin' => $horario['fin'] ?? null
            ];
        }

        return response()->json([
            'success' => true,
            'max_modulos' => $maxModulos,
            'modulo_actual' => $moduloActual,
            'codigo_dia' => $codigoDia,
            'modulos_disponibles' => $modulosDisponibles,
            'modulos_detalle' => $modulosDetalle,
            'proxima_clase' => $proximaClase,
            'clases_proximas' => $clasesProximas,
            'detalles' => [
                'planificaciones_encontradas' => count($planificaciones),
                'reservas_activas' => count($reservasActivas),
                'modulos_ocupados' => count($modulosOcupados)
            ]
        ]);

        // Log final para debugging
        Log::info('modulosDisponibles - Respuesta enviada:', [
            'success' => true,
            'max_modulos' => $maxModulos,
            'modulo_actual' => $moduloActual,
            'codigo_dia' => $codigoDia,
            'modulos_disponibles_count' => count($modulosDisponibles)
        ]);
    }

    /**
     * Determina el módulo actual según la hora y día
     */
    private function determinarModuloActual($horaActual, $diaActual)
    {
        // Log para debugging
        Log::info('determinarModuloActual - Iniciando:', [
            'horaActual' => $horaActual,
            'diaActual' => $diaActual
        ]);

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

        // Log para debugging de horarios del día
        Log::info('determinarModuloActual - Horarios del día:', [
            'diaActual' => $diaActual,
            'horariosDia' => $horariosDia ? 'encontrado' : 'no encontrado'
        ]);

        if (!$horariosDia) {
            Log::warning('determinarModuloActual - No se encontraron horarios para el día:', [
                'diaActual' => $diaActual
            ]);
            return null;
        }

        // Buscar en qué módulo estamos según la hora actual
        foreach ($horariosDia as $modulo => $horario) {
            if ($horaActual >= $horario['inicio'] && $horaActual < $horario['fin']) {
                Log::info('determinarModuloActual - Módulo encontrado:', [
                    'modulo' => $modulo,
                    'horaActual' => $horaActual,
                    'horarioInicio' => $horario['inicio'],
                    'horarioFin' => $horario['fin']
                ]);
                return $modulo;
            }
        }

        // Si no estamos en ningún módulo (break), buscar el siguiente módulo disponible
        // Esto permite hacer reservas durante los breaks
        foreach ($horariosDia as $modulo => $horario) {
            if ($horaActual < $horario['inicio']) {
                Log::info('determinarModuloActual - Módulo encontrado durante break:', [
                    'modulo' => $modulo,
                    'horaActual' => $horaActual,
                    'proximoHorarioInicio' => $horario['inicio']
                ]);
                return $modulo; // Retornar el siguiente módulo
            }
        }

        Log::warning('determinarModuloActual - No se encontró módulo para la hora:', [
            'horaActual' => $horaActual,
            'diaActual' => $diaActual
        ]);

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
    $planificacion = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
            ->where('id_espacio', $espacioId)
            ->where('id_modulo', $moduloCodigo)
            ->first();

        if ($planificacion) {
            return [
                'modulo' => $moduloCodigo,
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'No especificada',
                // acceder al profesor a través de la asignatura (asignatura->profesor)
                'profesor' => $planificacion->asignatura->profesor->name ?? 'No especificado',
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

            // Descargar el archivo usando response()->download con la ruta completa
            // Asumir storage/app/public como disco 'public'
            $fullPath = storage_path('app/public/' . ltrim($qrPath, '/'));
            if (file_exists($fullPath)) {
                return response()->download($fullPath, 'QR_' . $espacio->id_espacio . '.png');
            }
            return redirect()->back()->with('error', 'No se pudo encontrar el archivo QR generado.');

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

    /**
     * Obtiene las asignaturas de un profesor para el día actual
     */
    public function getAsignaturasProfesorHoy(Request $request, $runProfesor)
    {
        try {
            $codigoDia = $request->input('codigo_dia', 'LU');

            // Obtener las asignaturas del profesor para el día especificado
            $asignaturas = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                ->whereHas('asignatura', function($query) use ($runProfesor) {
                    $query->where('run_profesor', $runProfesor);
                })
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->get()
                ->map(function($planificacion) {
                    return [
                        'nombre_asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'No especificada',
                        'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'No especificado',
                        'modulo' => $planificacion->modulo->id_modulo ?? 'No especificado',
                        'hora_inicio' => $planificacion->modulo->hora_inicio ?? '',
                        'hora_termino' => $planificacion->modulo->hora_termino ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'asignaturas' => $asignaturas,
                'total' => $asignaturas->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener asignaturas del profesor:', [
                'run_profesor' => $runProfesor,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener las asignaturas del profesor',
                'asignaturas' => []
            ], 500);
        }
    }

    public function getInformacionDetalladaEspacio($idEspacio)
    {
        try {
            // Cache key para este espacio
            $cacheKey = "espacio_info_{$idEspacio}";

            // Verificar cache (válido por 30 segundos)
            if (cache()->has($cacheKey)) {
                $cachedData = cache()->get($cacheKey);
                $cacheTime = cache()->get("{$cacheKey}_time", 0);

                if ((time() - $cacheTime) < 30) {
                    Log::info("Retornando información desde cache para espacio: {$idEspacio}");
                    return response()->json($cachedData);
                }
            }

            Log::info("Obteniendo información detallada para espacio: {$idEspacio}");

            // Buscar el espacio con eager loading
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                Log::warning("Espacio no encontrado: {$idEspacio}");
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');

            // Preparar respuesta base
            $response = [
                'success' => true,
                'tipo_ocupacion' => 'libre',
                'nombre' => null,
                'asignatura' => null,
                'hora_inicio' => null,
                'hora_salida' => null,
                'tipo_reserva' => null,
                'detalles' => null,
                'proxima_clase' => null
            ];

            // Si el espacio está ocupado, revisar tabla Reservas con consulta optimizada
            if (in_array($espacio->estado, ['Ocupado', 'ocupado', '#FF0000'])) {
                Log::info("Espacio ocupado, revisando tabla Reservas");

                // Primero buscar reserva activa dentro del horario
                $reservaActiva = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'estado', 'tipo_reserva')
                    ->where('id_espacio', $idEspacio)
                    ->where('fecha_reserva', $fechaActual)
                    ->where('hora', '<=', $horaActual)
                    ->where(function($query) use ($horaActual) {
                        $query->where('hora_salida', '>', $horaActual)
                              ->orWhereNull('hora_salida');
                    })
                    ->where('estado', 'activa')
                    ->first();

                // Si no hay reserva activa, buscar reservas vencidas que aún están marcadas como activas
                if (!$reservaActiva) {
                    $reservaVencida = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'estado', 'tipo_reserva')
                        ->where('id_espacio', $idEspacio)
                        ->where('fecha_reserva', $fechaActual)
                        ->where('estado', 'activa')
                        ->whereNotNull('hora_salida')
                        ->where('hora_salida', '<=', $horaActual)
                        ->first();
                    
                    if ($reservaVencida) {
                        $reservaActiva = $reservaVencida;
                        Log::info("Reserva vencida encontrada que necesita finalización", ['reserva_id' => $reservaVencida->id_reserva]);
                    }
                }

                if ($reservaActiva) {
                    Log::info("Reserva activa encontrada", ['reserva_id' => $reservaActiva->id_reserva]);

                    // Determinar tipo de usuario y obtener información
                    if ($reservaActiva->run_profesor) {
                        $response = $this->obtenerInformacionProfesor($reservaActiva, $horaActual);
                    } elseif ($reservaActiva->run_solicitante) {
                        $response = $this->obtenerInformacionSolicitante($reservaActiva);
                    } else {
                        $response = [
                            'success' => true,
                            'tipo_ocupacion' => 'ocupado_sin_info',
                            'nombre' => 'No especificado',
                            'tipo_reserva' => 'Reserva sin usuario',
                            'asignatura' => null,
                            'hora_inicio' => $reservaActiva->hora,
                            'hora_salida' => $reservaActiva->hora_salida,
                            // Agregar identificador para permitir desocupación forzosa
                            'run_profesor' => null,
                            'run_solicitante' => null,
                            'id_reserva' => $reservaActiva->id_reserva
                        ];
                    }
                } else {
                    // Buscar cualquier reserva para el día actual
                    $reservaCualquiera = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'estado', 'tipo_reserva')
                        ->where('id_espacio', $idEspacio)
                        ->where('fecha_reserva', $fechaActual)
                        ->first();

                    if ($reservaCualquiera) {
                        $response = [
                            'success' => true,
                            'tipo_ocupacion' => 'ocupado_sin_info',
                            'nombre' => 'No especificado',
                            'tipo_reserva' => $reservaCualquiera->tipo_reserva,
                            'asignatura' => null,
                            'hora_inicio' => $reservaCualquiera->hora,
                            'hora_salida' => $reservaCualquiera->hora_salida,
                            'detalles' => 'Reserva no activa',
                            'estado_reserva' => $reservaCualquiera->estado,
                            // Incluir RUNs para permitir desocupación aunque estén null
                            'run_profesor' => $reservaCualquiera->run_profesor,
                            'run_solicitante' => $reservaCualquiera->run_solicitante,
                            'id_reserva' => $reservaCualquiera->id_reserva
                        ];
                    }
                }
            } else {
                // Espacio libre, buscar próxima clase
                $response['proxima_clase'] = $this->obtenerProximaClase($idEspacio, $horaActual);
            }

            // Guardar en cache
            cache()->put($cacheKey, $response, 30);
            cache()->put("{$cacheKey}_time", time(), 30);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error("Error al obtener información del espacio {$idEspacio}:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtiene información de un profesor
     */
    private function obtenerInformacionProfesor($reserva, $horaActual)
    {
        $runProfesor = $reserva->run_profesor;

        // Consulta optimizada para profesor desde la tabla profesors
        $profesor = Profesor::select('name', 'run_profesor')
            ->where('run_profesor', $runProfesor)
            ->first();

        if (!$profesor) {
            return [
                'success' => true,
                'tipo_ocupacion' => 'ocupado_sin_info',
                'nombre' => 'Profesor no encontrado',
                'tipo_reserva' => $reserva->tipo_reserva,
                'asignatura' => null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida
            ];
        }

        // Buscar planificación actual utilizando la relación 'modulo'
        $diaActual = strtolower(now()->format('l'));
        $codigosDias = [
            'monday' => 'LU', 'tuesday' => 'MA', 'wednesday' => 'MI',
            'thursday' => 'JU', 'friday' => 'VI', 'saturday' => 'SA', 'sunday' => 'DO'
        ];
        $codigoDia = $codigosDias[$diaActual] ?? 'LU';

        // Cargar planificaciones del profesor para el día (usando la relación con horario)
        $planificaciones = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario'])
            ->whereHas('horario', function($query) use ($runProfesor) {
                $query->where('run_profesor', $runProfesor);
            })
            ->where('id_modulo', 'like', $codigoDia . '.%')
            ->get();

        // Filtrar en memoria por los módulos cuyo horario contiene la hora actual
        $planificacion = $planificaciones->first(function ($p) use ($horaActual) {
            return isset($p->modulo->hora_inicio, $p->modulo->hora_termino)
                && $p->modulo->hora_inicio <= $horaActual
                && $p->modulo->hora_termino > $horaActual;
        });

        $asignatura = $planificacion ? $planificacion->asignatura->nombre_asignatura : null;

        return [
            'success' => true,
            'tipo_ocupacion' => 'profesor',
            'nombre' => $profesor->name,
            'run_profesor' => $runProfesor,
            'asignatura' => $asignatura,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
            'tipo_reserva' => $reserva->tipo_reserva
        ];
    }

    /**
     * Obtiene información de un solicitante con cache optimizado
     */
    private function obtenerInformacionSolicitante($reserva)
    {
        $runSolicitante = $reserva->run_solicitante;

        // Usar el método optimizado del modelo Solicitante
        $solicitante = Solicitante::buscarActivoPorRun($runSolicitante);

        if (!$solicitante) {
            return [
                'success' => true,
                'tipo_ocupacion' => 'ocupado_sin_info',
                'nombre' => 'Solicitante no encontrado',
                'tipo_reserva' => $reserva->tipo_reserva,
                'asignatura' => null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida
            ];
        }

        return $this->construirRespuestaSolicitante($solicitante, $reserva);
    }

    /**
     * Construye la respuesta para solicitantes
     */
    private function construirRespuestaSolicitante($solicitante, $reserva)
    {
        return [
            'success' => true,
            'tipo_ocupacion' => 'solicitante',
            'nombre' => $solicitante->nombre ?? 'No especificado',
            'run_solicitante' => $solicitante->run_solicitante ?? 'No especificado',
            'correo' => $solicitante->correo ?? 'No especificado',
            'telefono' => $solicitante->telefono ?? 'No especificado',
            'tipo_solicitante' => $solicitante->tipo_solicitante ?? 'No especificado',
            'activo' => $solicitante->activo ?? false,
            'fecha_registro' => $solicitante->fecha_registro ?? null,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
            'tipo_reserva' => $reserva->tipo_reserva
        ];
    }

    /**
     * Obtiene información de la próxima clase
     */
    private function obtenerProximaClase($idEspacio, $horaActual)
    {
        $diaActual = strtolower(now()->format('l'));
        $codigosDias = [
            'monday' => 'LU', 'tuesday' => 'MA', 'wednesday' => 'MI',
            'thursday' => 'JU', 'friday' => 'VI', 'saturday' => 'SA', 'sunday' => 'DO'
        ];
        $codigoDia = $codigosDias[$diaActual] ?? 'LU';

    // Cargar planificaciones del espacio para el día (filtrando por id_modulo que comienza con el código de día)
    // Incluir relación profesor en la asignatura para poder mostrar nombre y run correctamente
    $planificaciones = Planificacion_Asignatura::with(['modulo', 'asignatura.profesor'])
            ->where('id_espacio', $idEspacio)
            ->where('id_modulo', 'like', $codigoDia . '.%')
            ->get();

        if ($planificaciones->isEmpty()) {
            return null;
        }

        // Filtrar por módulo cuya hora de inicio sea posterior a la hora actual
        $candidatas = $planificaciones->filter(function ($p) use ($horaActual) {
            return isset($p->modulo->hora_inicio) && $p->modulo->hora_inicio > $horaActual;
        });

        if ($candidatas->isEmpty()) {
            return null;
        }

        // Ordenar por hora de inicio y tomar la primera
        $proxima = $candidatas->sortBy(function ($p) {
            return $p->modulo->hora_inicio ?? '99:99:99';
        })->first();

        return [
            'asignatura' => $proxima->asignatura->nombre_asignatura ?? 'No especificada',
            'profesor' => $proxima->asignatura->profesor->name ?? 'No especificado',
            'profesor_run' => $proxima->asignatura->run_profesor ?? null,
            'hora_inicio' => $proxima->modulo->hora_inicio ?? null,
            'hora_termino' => $proxima->modulo->hora_termino ?? null
        ];
    }
}
