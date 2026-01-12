<?php

namespace App\Http\Controllers;

use App\Models\Solicitante;
use App\Models\Visitante;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Controlador para manejar operaciones de solicitantes
 *
 * Los solicitantes son usuarios externos que pueden reservar espacios
 * pero no son profesores registrados en el sistema.
 */
class SolicitanteController extends Controller
{
    /**
     * Verificar si un solicitante existe en la base de datos
     */
    public function verificarSolicitante($run)
    {
        try {
            $solicitante = Solicitante::where('run_solicitante', $run)
                ->where('activo', true)
                ->first();

            if ($solicitante) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'solicitante_registrado',
                    'solicitante' => [
                        'run_solicitante' => $solicitante->run_solicitante,
                        'nombre' => $solicitante->nombre,
                        'correo' => $solicitante->correo,
                        'telefono' => $solicitante->telefono,
                        'tipo_solicitante' => $solicitante->tipo_solicitante
                    ],
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // Si no está registrado, retornar información para registro
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Solicitante no registrado. Se requiere registro previo.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar solicitante: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar solicitante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar un nuevo solicitante
     */
    public function registrarSolicitante(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'run_solicitante' => 'required|string|unique:solicitantes,run_solicitante',
                'nombre' => 'required|string|max:255',
                'correo' => 'required|email|unique:solicitantes,correo',
                'telefono' => 'required|string|max:20',
                'tipo_solicitante' => 'required|in:estudiante,personal,visitante,otro'
            ]);

            $solicitante = new Solicitante();
            $solicitante->run_solicitante = $request->run_solicitante;
            $solicitante->nombre = $request->nombre;
            $solicitante->correo = $request->correo;
            $solicitante->telefono = $request->telefono;
            $solicitante->tipo_solicitante = $request->tipo_solicitante;
            $solicitante->activo = true;
            $solicitante->fecha_registro = now();
            $solicitante->save();
            
            // Registrar también como visitante para que aparezca en el mantenedor
            try {
                \App\Models\Visitante::create([
                    'run_solicitante' => $request->run_solicitante,
                    'nombre' => $request->nombre,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'tipo_solicitante' => $request->tipo_solicitante,
                    'activo' => true,
                    'fecha_registro' => now(),
                ]);
            } catch (\Exception $e) {
                // Si falla al crear el visitante, continuamos igual porque ya tenemos el solicitante
                Log::warning('No se pudo registrar como visitante: ' . $e->getMessage());
            }

            Log::info('Solicitante registrado exitosamente', [
                'run_solicitante' => $solicitante->run_solicitante,
                'nombre' => $solicitante->nombre
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Solicitante registrado exitosamente',
                'solicitante' => [
                    'run_solicitante' => $solicitante->run_solicitante,
                    'nombre' => $solicitante->nombre,
                    'correo' => $solicitante->correo,
                    'telefono' => $solicitante->telefono,
                    'tipo_solicitante' => $solicitante->tipo_solicitante,
                    'institucion_origen' => $solicitante->institucion_origen
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación: ' . $e->getMessage(),
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al registrar solicitante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar solicitante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una reserva para un solicitante
     */
    public function crearReservaSolicitante(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('=== INICIO CREAR RESERVA SOLICITANTE ===');
            Log::info('Datos recibidos:', $request->all());

            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            // Validar datos básicos (sin usar exists: para evitar búsqueda en BD central)
            $request->validate([
                'run_solicitante' => 'required|string',
                'id_espacio' => 'required|string',
                'modulos' => 'required|integer|min:1|max:2'
            ]);

            $ahora = Carbon::now();
            $horaActual = $ahora->format('H:i:s');
            $fechaActual = $ahora->toDateString();

            // Verificar que el solicitante existe y está activo (búsqueda en BD central)
            $solicitante = Solicitante::where('run_solicitante', $request->run_solicitante)
                ->where('activo', true)
                ->first();

            if (!$solicitante) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Solicitante no encontrado o inactivo'
                ], 404);
            }

            // Verificar que el espacio existe y está disponible (búsqueda en BD tenant)
            $espacio = Espacio::find($request->id_espacio);
            if (!$espacio) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            if ($espacio->estado === 'Ocupado') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio está ocupado actualmente'
                ], 400);
            }

            // Verificar que el solicitante no tenga reservas activas sin hora_salida
            $reservaActiva = Reserva::where('run_solicitante', $request->run_solicitante)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaActiva) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa. Debes finalizarla antes de solicitar una nueva.',
                    'tipo' => 'reserva_activa'
                ], 400);
            }

            // Validar módulos consecutivos disponibles
            $modulosSolicitados = $request->modulos;
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $diasSemana[$ahora->dayOfWeek];

            $codigosDias = [
                'lunes' => 'LU', 'martes' => 'MA', 'miercoles' => 'MI',
                'jueves' => 'JU', 'viernes' => 'VI', 'sabado' => 'SA', 'domingo' => 'DO'
            ];

            $codigoDia = $codigosDias[$diaActual] ?? 'LU';

            // Determinar módulo actual
            $moduloActual = $this->determinarModuloActual($horaActual, $diaActual);

            if (!$moduloActual) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay módulo actual disponible. El horario académico es de 08:10 a 23:00.'
                ], 400);
            }

            // Verificar módulos consecutivos disponibles (incluyendo reservas activas)
            $planificaciones = Planificacion_Asignatura::where('id_espacio', $request->id_espacio)
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->pluck('id_modulo')
                ->toArray();

            // Obtener reservas activas para este espacio en este día
            $reservasActivas = Reserva::where('id_espacio', $request->id_espacio)
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

            $modulosDisponibles = 0;
            $modulosDisponiblesList = [];
            $proximaClase = null;

            for ($i = $moduloActual; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;

                if (in_array($moduloCodigo, $modulosOcupados)) {
                    // Encontrar información de la próxima clase si es una planificación
                    if (in_array($moduloCodigo, $planificaciones)) {
                        $proximaClase = $this->obtenerInfoProximaClase($moduloCodigo, $request->id_espacio);
                    }
                    break;
                }

                $modulosDisponiblesList[] = $i;
                $modulosDisponibles++;

                if ($modulosDisponibles >= $modulosSolicitados) {
                    break;
                }
            }

            if ($modulosDisponibles < $modulosSolicitados) {
                DB::rollBack();
                $mensaje = 'No hay suficientes módulos consecutivos disponibles.';

                if ($proximaClase) {
                    $mensaje .= " Próxima clase: {$proximaClase['asignatura']} (Módulo {$proximaClase['modulo']})";
                }

                return response()->json([
                    'success' => false,
                    'mensaje' => $mensaje,
                    'detalles' => [
                        'modulos_disponibles' => $modulosDisponibles,
                        'modulos_solicitados' => $modulosSolicitados,
                        'proxima_clase' => $proximaClase,
                        'planificaciones_encontradas' => count($planificaciones),
                        'reservas_activas' => count($reservasActivas)
                    ]
                ], 400);
            }

            // Obtener horarios desde la tabla Modulo usando id_modulo
            // Construir id_modulo (ej: "JU.7")
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $indexDia = array_search($diaActual, $diasArray);
            $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
            
            $idModuloInicio = $prefijo . '.' . $moduloActual;
            $idModuloFin = $prefijo . '.' . ($moduloActual + $modulosSolicitados - 1);
            
            $moduloInicio = \App\Models\Modulo::where('id_modulo', $idModuloInicio)->first();
            $moduloFin = \App\Models\Modulo::where('id_modulo', $idModuloFin)->first();

            $horaInicio = $moduloInicio ? $moduloInicio->hora_inicio : $horaActual;
            $horaFin = $moduloFin ? $moduloFin->hora_termino : null;

            // Verificar que no haya reservas simultáneas en el tiempo
            $reservasSimultaneas = Reserva::where('run_solicitante', $request->run_solicitante)
                ->where('estado', 'activa')
                ->where(function($query) use ($horaInicio, $horaFin) {
                    // Verificar si hay solapamiento de horarios
                    $query->where(function($q) use ($horaInicio, $horaFin) {
                        // La nueva reserva empieza antes y termina durante una reserva existente
                        $q->where('hora', '<=', $horaInicio)
                          ->where('hora_salida', '>', $horaInicio);
                    })->orWhere(function($q) use ($horaInicio, $horaFin) {
                        // La nueva reserva empieza durante una reserva existente
                        $q->where('hora', '>=', $horaInicio)
                          ->where('hora', '<', $horaFin);
                    })->orWhere(function($q) use ($horaInicio, $horaFin) {
                        // La nueva reserva contiene completamente una reserva existente
                        $q->where('hora', '>=', $horaInicio)
                          ->where('hora_salida', '<=', $horaFin);
                    });
                })
                ->first();

            if ($reservasSimultaneas) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en ese horario. Debes finalizarla antes de solicitar una nueva.',
                    'tipo' => 'reserva_simultanea'
                ], 400);
            }

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->hora = $horaInicio;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run_solicitante = $request->run_solicitante;
            $reserva->run_profesor = null; // No es profesor
            $reserva->tipo_reserva = 'espontanea';
            $reserva->estado = 'activa';
            $reserva->hora_salida = $horaFin;
            $reserva->save();

            // Actualizar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            DB::commit();

            Log::info('Reserva de solicitante creada exitosamente', [
                'id_reserva' => $reserva->id_reserva,
                'run_solicitante' => $reserva->run_solicitante,
                'espacio' => $espacio->nombre_espacio,
                'modulos' => $modulosSolicitados
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente',
                'modulos' => $modulosSolicitados,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio' => $espacio->nombre_espacio,
                    'solicitante' => $solicitante->nombre,
                    'modulos_reservados' => $modulosSolicitados
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación al crear reserva de solicitante: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reserva de solicitante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determinar el módulo actual según la hora
     */
    private function determinarModuloActual($horaActual, $diaActual)
    {
        // Prefijo del día
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        
        // Buscar un módulo que contenga la hora actual
        // Probar módulos del 1 al 15
        for ($i = 1; $i <= 15; $i++) {
            $idModulo = $prefijo . '.' . $i;
            $modulo = \App\Models\Modulo::where('id_modulo', $idModulo)->first();
            
            if ($modulo && $horaActual >= $modulo->hora_inicio && $horaActual < $modulo->hora_termino) {
                return $i;
            }
        }
        
        // Si no hay módulo activo, buscar el siguiente disponible
        for ($i = 1; $i <= 15; $i++) {
            $idModulo = $prefijo . '.' . $i;
            $modulo = \App\Models\Modulo::where('id_modulo', $idModulo)->first();
            
            if ($modulo && $horaActual < $modulo->hora_inicio) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Obtiene el horario de un módulo específico
     */
    private function obtenerHorarioModulo($modulo, $diaActual)
    {
        // Prefijo del día
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        
        $idModulo = $prefijo . '.' . $modulo;
        $moduloData = \App\Models\Modulo::where('id_modulo', $idModulo)->first();

        if (!$moduloData) {
            return null;
        }

        return [
            'inicio' => $moduloData->hora_inicio,
            'fin' => $moduloData->hora_termino
        ];
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
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'profesor' => $planificacion->asignatura->profesor->name ?? 'No especificado',
                'hora_inicio' => $planificacion->modulo->hora_inicio ?? '',
                'hora_termino' => $planificacion->modulo->hora_termino ?? ''
            ];
        }

        return null;
    }

    /**
     * Verificar si un RUN existe en la tabla solicitantes
     */
    public function verificarExistencia($run)
    {
        try {
            $runLimpio = preg_replace('/[^0-9]/', '', $run);
            
            $existe = Solicitante::where('run_solicitante', $run)
                ->orWhere('run_solicitante', $runLimpio)
                ->exists();

            return response()->json([
                'existe' => $existe
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'existe' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Establece el contexto del tenant desde el request
     */
    private function establecerContextoTenant()
    {
        $tenantId = tenant_id() ?? null;
        
        if (!$tenantId) {
            $host = request()->getHost();
            $tenant = Tenant::where('domain', $host)
                ->orWhere('domain', 'LIKE', '%' . $host . '%')
                ->first();
            
            if ($tenant) {
                Config::set('database.connections.tenant.database', $tenant->database);
                DB::purge('tenant');
                Log::info('Tenant establecido en SolicitanteController', [
                    'tenant' => $tenant->name,
                    'database' => $tenant->database,
                    'host' => $host
                ]);
            }
        }
    }
}

