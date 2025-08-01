<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HorarioController extends Controller
{
    /**
     * Verificar si un usuario tiene una reserva activa en un espacio específico
     */
    public function verificarReservaActiva(Request $request)
    {
        try {
            $request->validate([
                'run_usuario' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runUsuario = $request->input('run_usuario');
            $idEspacio = $request->input('id_espacio');

            // Buscar reserva activa para este usuario en este espacio
            $reservaActiva = Reserva::where(function($query) use ($runUsuario) {
                    $query->where('run_profesor', $runUsuario)
                          ->orWhere('run_solicitante', $runUsuario);
                })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            return response()->json([
                'success' => true,
                'tiene_reserva_activa' => $reservaActiva ? true : false,
                'reserva' => $reservaActiva ? [
                    'id_reserva' => $reservaActiva->id_reserva,
                    'hora_inicio' => $reservaActiva->hora,
                    'hora_fin' => $reservaActiva->hora_salida,
                    'fecha' => $reservaActiva->fecha_reserva
                ] : null
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al verificar reserva activa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'tiene_reserva_activa' => false,
                'mensaje' => 'Error al verificar reserva activa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar usuario (profesor, solicitante o usuario no registrado)
     * 
     * Esta función determina el tipo de usuario que está escaneando el QR:
     * 1. Profesor registrado
     * 2. Solicitante registrado
     * 3. Usuario no registrado (requiere registro)
     */
    public function verificarUsuario($run)
    {
        try {
            // ========================================
            // CASO 1: Verificar si es profesor registrado
            // ========================================
            $profesor = \App\Models\Profesor::select('run_profesor', 'name', 'email', 'celular', 'tipo_profesor')
                ->where('run_profesor', $run)
                ->first();

            if ($profesor) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'profesor',
                    'usuario' => [
                        'run' => $profesor->run_profesor,
                        'nombre' => $profesor->name,
                        'email' => $profesor->email,
                        'telefono' => $profesor->celular,
                        'tipo_profesor' => $profesor->tipo_profesor
                    ],
                    'mensaje' => 'Profesor verificado correctamente'
                ]);
            }

            // ========================================
            // CASO 2: Verificar si es solicitante registrado
            // ========================================
            $solicitante = \App\Models\Solicitante::where('run_solicitante', $run)
                ->where('activo', true)
                ->first();

            if ($solicitante) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'solicitante_registrado',
                    'usuario' => [
                        'run' => $solicitante->run_solicitante,
                        'nombre' => $solicitante->nombre,
                        'email' => $solicitante->correo,
                        'telefono' => $solicitante->telefono,
                        'tipo_solicitante' => $solicitante->tipo_solicitante,
                        'institucion_origen' => $solicitante->institucion_origen
                    ],
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // ========================================
            // CASO 3: Verificar si es usuario no registrado (legacy)
            // ========================================
            $usuarioNoRegistrado = \App\Models\UsuarioNoRegistrado::where('run', $run)->first();

            if ($usuarioNoRegistrado) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'usuario_no_registrado',
                    'usuario' => [
                        'run' => $usuarioNoRegistrado->run,
                        'nombre' => $usuarioNoRegistrado->nombre,
                        'email' => $usuarioNoRegistrado->email,
                        'telefono' => $usuarioNoRegistrado->telefono,
                        'modulos_utilizacion' => $usuarioNoRegistrado->modulos_utilizacion
                    ],
                    'mensaje' => 'Usuario no registrado verificado correctamente'
                ]);
            }

            // ========================================
            // CASO 4: Usuario completamente nuevo
            // ========================================
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado en la base de datos. Se requiere registro previo.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar usuario: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarProfesor($run)
    {
        try {
            // Verificar en la tabla de profesores
            $profesor = \App\Models\Profesor::select('run_profesor', 'name', 'email', 'celular', 'tipo_profesor')->where('run_profesor', $run)->first();

            if ($profesor) {
                return response()->json([
                    'verificado' => true,
                    'profesor' => [
                        'run_profesor' => $profesor->run_profesor,
                        'name' => $profesor->name,
                        'email' => $profesor->email,
                        'telefono' => $profesor->celular,
                        'tipo_profesor' => $profesor->tipo_profesor
                    ],
                    'mensaje' => 'Profesor verificado correctamente'
                ]);
            }

            // Si no está en la tabla de profesores
            return response()->json([
                'verificado' => false,
                'run_escaneado' => $run,
                'mensaje' => 'Profesor no encontrado en la base de datos.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar profesor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarEspacio($idEspacio)
    {
        try {
            $espacio = Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'estado')->find($idEspacio);

            if (!$espacio) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            if ($espacio->estado === 'Ocupado') {
                return response()->json([
                    'verificado' => true,
                    'disponible' => false,
                    'mensaje' => 'El espacio está ocupado',
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'tipo' => $espacio->tipo_espacio
                    ]
                ]);
            }

            $ahora = Carbon::now();
            $diaActual = $ahora->dayOfWeek;
            $horaActual = $ahora->format('H:i:s');

            // Determinar el período actual
            $mesActual = date('n');
            $anioActual = date('Y');
            $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
            $periodo = $anioActual . '-' . $semestre;
            
            $planificacionActual = Planificacion_Asignatura::select('id_asignatura', 'id_modulo')
                ->where('id_espacio', $idEspacio)
                ->whereHas('horario', function($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->whereHas('modulo', function($query) use ($diaActual, $horaActual) {
                    $query->where('dia', $diaActual)
                          ->where('hora_inicio', '<=', $horaActual)
                          ->where('hora_termino', '>=', $horaActual);
                })
                ->with(['modulo:id_modulo,hora_inicio,hora_termino,dia', 'asignatura:id_asignatura,nombre_asignatura'])
                ->first();

            return response()->json([
                'verificado' => true,
                'disponible' => true,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio
                ],
                'planificacion' => $planificacionActual ? [
                    'asignatura' => $planificacionActual->asignatura->nombre_asignatura ?? null,
                    'horario' => ($planificacionActual->modulo->hora_inicio ?? '') . ' - ' . ($planificacionActual->modulo->hora_termino ?? '')
                ] : null,
                'mensaje' => 'Espacio disponible'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva para profesor o solicitante
     * 
     * Esta función maneja la creación de reservas para:
     * 1. Profesores con clases programadas
     * 2. Profesores sin clases (reserva espontánea)
     * 3. Solicitantes registrados
     */
    public function crearReserva(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('=== INICIO CREAR RESERVA ===');
            Log::info('Datos recibidos:', $request->all());
            
            // ========================================
            // DEFINIR VARIABLES DE FECHA Y HORA AL INICIO
            // ========================================
            $ahora = Carbon::now();
            $horaActual = $ahora->format('H:i:s');
            $fechaActual = $ahora->toDateString();
            
            Log::info('Variables de tiempo definidas:', [
                'hora_actual' => $horaActual,
                'fecha_actual' => $fechaActual
            ]);
            
            // ========================================
            // DETERMINAR TIPO DE USUARIO
            // ========================================
            $tipoUsuario = $request->input('tipo_usuario', 'profesor'); // 'profesor' o 'solicitante'
            $runUsuario = $request->input('run');
            
            Log::info('Tipo de usuario detectado:', [
                'tipo_usuario' => $tipoUsuario,
                'run_usuario' => $runUsuario
            ]);
            
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            if (!$espacio) {
                DB::rollBack();
                Log::error('Espacio no encontrado:', ['id_espacio' => $request->id_espacio]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            Log::info('Espacio encontrado:', [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'estado_actual' => $espacio->estado
            ]);

            if ($espacio->estado === 'Ocupado') {
                DB::rollBack();
                Log::warning('Espacio ya está ocupado');
                return response()->json([
                    'success' => false,
                    'mensaje' => '¿Desea devolver las llaves?',
                    'tipo' => 'devolucion',
                    'espacio' => $espacio->nombre_espacio
                ]);
            }

            // ========================================
            // LÓGICA ESPECÍFICA POR TIPO DE USUARIO
            // ========================================
            
            if ($tipoUsuario === 'profesor') {
                return $this->crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora);
            } elseif ($tipoUsuario === 'solicitante') {
                return $this->crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Tipo de usuario no válido'
                ], 400);
            }

            // ========================================
            // VALIDACIÓN DE CLASES PROGRAMADAS
            // ========================================
            $clasesProgramadas = $this->verificarClasesProgramadas($request->run, $horaActual, $ahora->dayOfWeek);
            
            Log::info('Resultado de verificación de clases programadas:', $clasesProgramadas);
            
            // Si el usuario tiene clases programadas, verificar que esté solicitando el espacio correcto
            if ($clasesProgramadas['tiene_clases']) {
                $espacioProgramado = $clasesProgramadas['proxima_clase']['espacio']['id'];
                
                // Si está solicitando un espacio diferente al programado, mostrar advertencia
                if ($espacioProgramado !== $request->id_espacio) {
                    DB::rollBack();
                    Log::warning('Usuario solicita espacio diferente al programado:', [
                        'run' => $request->run,
                        'espacio_solicitado' => $request->id_espacio,
                        'espacio_programado' => $espacioProgramado,
                        'clase_programada' => $clasesProgramadas['proxima_clase']
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'mensaje' => "Hola {$clasesProgramadas['profesor']['nombre']}, tienes clase de '{$clasesProgramadas['proxima_clase']['asignatura']}' programada en el espacio '{$clasesProgramadas['proxima_clase']['espacio']['nombre']}' a las {$clasesProgramadas['proxima_clase']['hora_inicio']}. ¿Desea solicitar las llaves de su espacio programado?",
                        'tipo' => 'clase_programada',
                        'clase_programada' => $clasesProgramadas['proxima_clase'],
                        'profesor' => $clasesProgramadas['profesor'],
                        'espacio_correcto' => $espacioProgramado
                    ]);
                } else {
                    // Está solicitando el espacio correcto - continuar normalmente
                    Log::info('Usuario solicita su espacio programado correctamente:', [
                        'run' => $request->run,
                        'espacio' => $request->id_espacio,
                        'clase' => $clasesProgramadas['proxima_clase']['asignatura']
                    ]);
                }
            }

            // ========================================
            // VALIDACIONES DE USUARIO Y RESERVAS
            // ========================================
            
            // 1. Verificar si el usuario ya tiene una reserva activa en cualquier sala
            $reservaActivaUsuario = Reserva::where('run_profesor', $request->run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaActivaUsuario) {
                DB::rollBack();
                \Log::warning('Profesor ya tiene reserva activa:', [
                    'run_profesor' => $request->run,
                    'reserva_id' => $reservaActivaUsuario->id_reserva,
                    'espacio_actual' => $reservaActivaUsuario->id_espacio,
                    'fecha_reserva' => $reservaActivaUsuario->fecha_reserva
                ]);
                
                // Obtener información del espacio donde tiene la reserva activa
                $espacioReservaActiva = Espacio::select('nombre_espacio')->find($reservaActivaUsuario->id_espacio);
                $nombreEspacioActivo = $espacioReservaActiva ? $espacioReservaActiva->nombre_espacio : 'Desconocido';
                
                return response()->json([
                    'success' => false,
                    'mensaje' => "Ya tienes una reserva activa en el espacio '{$nombreEspacioActivo}'. Debes finalizarla antes de solicitar una nueva.",
                    'tipo' => 'reserva_activa',
                    'reserva_activa' => [
                        'id_reserva' => $reservaActivaUsuario->id_reserva,
                        'espacio' => $nombreEspacioActivo,
                        'fecha' => $reservaActivaUsuario->fecha_reserva,
                        'hora_inicio' => $reservaActivaUsuario->hora
                    ]
                ]);
            }

            // 2. Verificar si el usuario ya tiene una reserva pendiente para el mismo día
            $reservaPendienteHoy = Reserva::where('run_profesor', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->exists();

            if ($reservaPendienteHoy) {
                DB::rollBack();
                \Log::warning('Profesor ya tiene reserva pendiente para hoy:', ['run_profesor' => $request->run, 'fecha' => $fechaActual]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva pendiente para hoy. Solo puedes tener una reserva por día.',
                    'tipo' => 'reserva_diaria'
                ]);
            }

            // 3. Verificar si el usuario ha excedido el límite de reservas diarias (opcional)
            $reservasHoy = Reserva::where('run_profesor', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->count();

            $limiteReservasDiarias = 3; // Puedes ajustar este límite según tus necesidades
            if ($reservasHoy >= $limiteReservasDiarias) {
                DB::rollBack();
                \Log::warning('Profesor ha excedido el límite de reservas diarias:', [
                    'run_profesor' => $request->run, 
                    'reservas_hoy' => $reservasHoy,
                    'limite' => $limiteReservasDiarias
                ]);
                return response()->json([
                    'success' => false,
                    'mensaje' => "Has excedido el límite de {$limiteReservasDiarias} reservas por día. Intenta mañana.",
                    'tipo' => 'limite_excedido',
                    'reservas_hoy' => $reservasHoy,
                    'limite' => $limiteReservasDiarias
                ]);
            }

            \Log::info('Validaciones de usuario completadas exitosamente:', [
                'run' => $request->run,
                'reservas_hoy' => $reservasHoy,
                'fecha' => $fechaActual
            ]);

            // 4. Verificar que el usuario no esté intentando reservar el mismo espacio que ya tiene reservado
            $reservaMismoEspacio = Reserva::where('run_profesor', $request->run)
                ->where('id_espacio', $request->id_espacio)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->exists();

            if ($reservaMismoEspacio) {
                DB::rollBack();
                \Log::warning('Usuario intenta reservar el mismo espacio:', [
                    'run' => $request->run,
                    'id_espacio' => $request->id_espacio,
                    'fecha' => $fechaActual
                ]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa para este espacio hoy.',
                    'tipo' => 'mismo_espacio'
                ]);
            }

            // Validar módulos consecutivos usando la nueva lógica simplificada
            $modulosSolicitados = $request->input('modulos', 1);
            
            // Obtener día actual en formato string
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $diasSemana[$ahora->dayOfWeek];
            
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
            
            \Log::info('Información de validación:', [
                'dia_actual' => $diaActual,
                'codigo_dia' => $codigoDia,
                'modulo_actual' => $moduloActual,
                'modulos_solicitados' => $modulosSolicitados,
                'hora_actual' => $horaActual
            ]);
            
            if (!$moduloActual) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay módulo actual disponible.'
                ]);
            }
            
            // Obtener todas las planificaciones para este espacio en este día
            $planificaciones = Planificacion_Asignatura::where('id_espacio', $request->id_espacio)
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->pluck('id_modulo')
                ->toArray();
            
            \Log::info('Planificaciones encontradas:', $planificaciones);
            
            // Contar módulos consecutivos disponibles desde el módulo actual
            $modulosDisponibles = 0;
            for ($i = $moduloActual; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;
                
                // Si existe planificación para este módulo, terminar
                if (in_array($moduloCodigo, $planificaciones)) {
                    \Log::info("Módulo {$moduloCodigo} tiene planificación - terminando conteo");
                    break;
                }
                
                $modulosDisponibles++;
                \Log::info("Módulo {$moduloCodigo} disponible (conteo: {$modulosDisponibles})");
                
                // Si ya tenemos suficientes módulos, terminar
                if ($modulosDisponibles >= $modulosSolicitados) {
                    break;
                }
            }
            
            \Log::info('Resultado de validación:', [
                'modulos_disponibles' => $modulosDisponibles,
                'modulos_solicitados' => $modulosSolicitados
            ]);
            
            if ($modulosDisponibles < $modulosSolicitados) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay suficientes módulos consecutivos disponibles para la cantidad solicitada.'
                ]);
            }
            
            // Definir hora de inicio y fin de la reserva usando las constantes de horarios
            $horariosModulos = [
                'lunes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'martes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'miercoles' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'jueves' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'viernes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']]
            ];
            
            $horariosDia = $horariosModulos[$diaActual] ?? null;
            $horaInicio = $horariosDia[$moduloActual]['inicio'] ?? $horaActual;
            $horaFin = $horariosDia[$moduloActual + $modulosSolicitados - 1]['fin'] ?? null;

            $lastReserva = Reserva::select('id_reserva')->orderByDesc('id_reserva')->first();
            $newIdNumber = $lastReserva ? 
                str_pad(intval(substr($lastReserva->id_reserva, 1)) + 1, 3, '0', STR_PAD_LEFT) : 
                '001';
            $newId = 'R' . $newIdNumber;

            \Log::info('Creando nueva reserva:', ['id_reserva' => $newId]);

            $reserva = new Reserva();
            $reserva->id_reserva = $newId;
            $reserva->hora = $horaInicio;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run_profesor = $request->run;
            $reserva->tipo_reserva = 'directa';
            $reserva->estado = 'activa';
            $reserva->hora_salida = $horaFin;
            $reserva->save();

            \Log::info('Reserva creada exitosamente:', [
                'id_reserva' => $reserva->id_reserva,
                'espacio_id' => $reserva->id_espacio,
                'run_profesor' => $reserva->run_profesor
            ]);

            \Log::info('Estado del espacio antes de actualizar:', ['estado' => $espacio->estado]);
            $espacio->estado = 'Ocupado';
            $espacio->save();
            \Log::info('Estado del espacio después de actualizar:', ['estado' => $espacio->estado]);

            // Verificar que el cambio se guardó correctamente
            $espacioVerificado = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            \Log::info('Verificación del estado del espacio:', [
                'id_espacio' => $espacioVerificado->id_espacio,
                'estado_verificado' => $espacioVerificado->estado
            ]);

            DB::commit();
            \Log::info('=== RESERVA CREADA EXITOSAMENTE ===');
            
            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ],
                'mensaje' => 'Reserva creada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reserva:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva específica para profesores
     */
    private function crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        try {
            // ========================================
            // VALIDACIÓN DE CLASES PROGRAMADAS
            // ========================================
            $clasesProgramadas = $this->verificarClasesProgramadas($request->run, $horaActual, $ahora->dayOfWeek);
            
            Log::info('Resultado de verificación de clases programadas:', $clasesProgramadas);
            
            // Si el usuario tiene clases programadas, verificar que esté solicitando el espacio correcto
            if ($clasesProgramadas['tiene_clases']) {
                $espacioProgramado = $clasesProgramadas['proxima_clase']['espacio']['id'];
                
                // Si está solicitando un espacio diferente al programado, mostrar advertencia
                if ($espacioProgramado !== $request->id_espacio) {
                    DB::rollBack();
                    Log::warning('Usuario solicita espacio diferente al programado:', [
                        'run' => $request->run,
                        'espacio_solicitado' => $request->id_espacio,
                        'espacio_programado' => $espacioProgramado,
                        'clase_programada' => $clasesProgramadas['proxima_clase']
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'mensaje' => "Hola {$clasesProgramadas['profesor']['nombre']}, tienes clase de '{$clasesProgramadas['proxima_clase']['asignatura']}' programada en el espacio '{$clasesProgramadas['proxima_clase']['espacio']['nombre']}' a las {$clasesProgramadas['proxima_clase']['hora_inicio']}. ¿Desea solicitar las llaves de su espacio programado?",
                        'tipo' => 'clase_programada',
                        'clase_programada' => $clasesProgramadas['proxima_clase'],
                        'profesor' => $clasesProgramadas['profesor'],
                        'espacio_correcto' => $espacioProgramado
                    ]);
                } else {
                    // Está solicitando el espacio correcto - continuar normalmente
                    Log::info('Usuario solicita su espacio programado correctamente:', [
                        'run' => $request->run,
                        'espacio' => $request->id_espacio,
                        'clase' => $clasesProgramadas['proxima_clase']['asignatura']
                    ]);
                }
            }

            // ========================================
            // VALIDACIONES DE USUARIO Y RESERVAS
            // ========================================
            
            // 1. Verificar si el usuario ya tiene una reserva activa en cualquier sala
            $reservaActivaUsuario = Reserva::where('run_profesor', $request->run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaActivaUsuario) {
                DB::rollBack();
                Log::warning('Profesor ya tiene reserva activa:', [
                    'run_profesor' => $request->run,
                    'reserva_id' => $reservaActivaUsuario->id_reserva,
                    'espacio_actual' => $reservaActivaUsuario->id_espacio,
                    'fecha_reserva' => $reservaActivaUsuario->fecha_reserva
                ]);
                
                // Obtener información del espacio donde tiene la reserva activa
                $espacioReservaActiva = Espacio::select('nombre_espacio')->find($reservaActivaUsuario->id_espacio);
                $nombreEspacioActivo = $espacioReservaActiva ? $espacioReservaActiva->nombre_espacio : 'Desconocido';
                
                return response()->json([
                    'success' => false,
                    'mensaje' => "Ya tienes una reserva activa en el espacio '{$nombreEspacioActivo}'. Debes finalizarla antes de solicitar una nueva.",
                    'tipo' => 'reserva_activa',
                    'reserva_activa' => [
                        'id_reserva' => $reservaActivaUsuario->id_reserva,
                        'espacio' => $nombreEspacioActivo,
                        'fecha' => $reservaActivaUsuario->fecha_reserva,
                        'hora_inicio' => $reservaActivaUsuario->hora
                    ]
                ]);
            }

            // 2. Verificar si el usuario ya tiene una reserva pendiente para el mismo día
            $reservaPendienteHoy = Reserva::where('run_profesor', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->exists();

            if ($reservaPendienteHoy) {
                DB::rollBack();
                Log::warning('Profesor ya tiene reserva pendiente para hoy:', ['run_profesor' => $request->run, 'fecha' => $fechaActual]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva pendiente para hoy. Solo puedes tener una reserva por día.',
                    'tipo' => 'reserva_diaria'
                ]);
            }

            // 3. Verificar si el usuario ha excedido el límite de reservas diarias (opcional)
            $reservasHoy = Reserva::where('run_profesor', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->count();

            $limiteReservasDiarias = 3; // Puedes ajustar este límite según tus necesidades
            if ($reservasHoy >= $limiteReservasDiarias) {
                DB::rollBack();
                Log::warning('Profesor ha excedido el límite de reservas diarias:', [
                    'run_profesor' => $request->run, 
                    'reservas_hoy' => $reservasHoy,
                    'limite' => $limiteReservasDiarias
                ]);
                return response()->json([
                    'success' => false,
                    'mensaje' => "Has excedido el límite de {$limiteReservasDiarias} reservas por día. Intenta mañana.",
                    'tipo' => 'limite_excedido',
                    'reservas_hoy' => $reservasHoy,
                    'limite' => $limiteReservasDiarias
                ]);
            }

            Log::info('Validaciones de usuario completadas exitosamente:', [
                'run' => $request->run,
                'reservas_hoy' => $reservasHoy,
                'fecha' => $fechaActual
            ]);

            // 4. Verificar que el usuario no esté intentando reservar el mismo espacio que ya tiene reservado
            $reservaMismoEspacio = Reserva::where('run_profesor', $request->run)
                ->where('id_espacio', $request->id_espacio)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->exists();

            if ($reservaMismoEspacio) {
                DB::rollBack();
                Log::warning('Usuario intenta reservar el mismo espacio:', [
                    'run' => $request->run,
                    'id_espacio' => $request->id_espacio,
                    'fecha' => $fechaActual
                ]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa para este espacio hoy.',
                    'tipo' => 'mismo_espacio'
                ]);
            }

            // Validar módulos consecutivos usando la nueva lógica simplificada
            $modulosSolicitados = $request->input('modulos', 1);
            
            // Obtener día actual en formato string
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $diasSemana[$ahora->dayOfWeek];
            
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
            
            Log::info('Información de validación:', [
                'dia_actual' => $diaActual,
                'codigo_dia' => $codigoDia,
                'modulo_actual' => $moduloActual,
                'modulos_solicitados' => $modulosSolicitados,
                'hora_actual' => $horaActual
            ]);
            
            if (!$moduloActual) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay módulo actual disponible.'
                ]);
            }
            
            // Obtener todas las planificaciones para este espacio en este día
            $planificaciones = Planificacion_Asignatura::where('id_espacio', $request->id_espacio)
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->pluck('id_modulo')
                ->toArray();
            
            Log::info('Planificaciones encontradas:', $planificaciones);
            
            // Contar módulos consecutivos disponibles desde el módulo actual
            $modulosDisponibles = 0;
            for ($i = $moduloActual; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;
                
                // Si existe planificación para este módulo, terminar
                if (in_array($moduloCodigo, $planificaciones)) {
                    Log::info("Módulo {$moduloCodigo} tiene planificación - terminando conteo");
                    break;
                }
                
                $modulosDisponibles++;
                Log::info("Módulo {$moduloCodigo} disponible (conteo: {$modulosDisponibles})");
                
                // Si ya tenemos suficientes módulos, terminar
                if ($modulosDisponibles >= $modulosSolicitados) {
                    break;
                }
            }
            
            Log::info('Resultado de validación:', [
                'modulos_disponibles' => $modulosDisponibles,
                'modulos_solicitados' => $modulosSolicitados
            ]);
            
            if ($modulosDisponibles < $modulosSolicitados) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay suficientes módulos consecutivos disponibles para la cantidad solicitada.'
                ]);
            }
            
            // Definir hora de inicio y fin de la reserva usando las constantes de horarios
            $horariosModulos = [
                'lunes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'martes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'miercoles' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'jueves' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'viernes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']]
            ];
            
            $horariosDia = $horariosModulos[$diaActual] ?? null;
            $horaInicio = $horariosDia[$moduloActual]['inicio'] ?? $horaActual;
            $horaFin = $horariosDia[$moduloActual + $modulosSolicitados - 1]['fin'] ?? null;

            $lastReserva = Reserva::select('id_reserva')->orderByDesc('id_reserva')->first();
            $newIdNumber = $lastReserva ? 
                str_pad(intval(substr($lastReserva->id_reserva, 1)) + 1, 3, '0', STR_PAD_LEFT) : 
                '001';
            $newId = 'R' . $newIdNumber;

            Log::info('Creando nueva reserva:', ['id_reserva' => $newId]);

            $reserva = new Reserva();
            $reserva->id_reserva = $newId;
            $reserva->hora = $horaInicio;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run_profesor = $request->run;
            $reserva->run_solicitante = null; // No es solicitante
            $reserva->tipo_reserva = $clasesProgramadas['tiene_clases'] ? 'clase' : 'espontanea';
            $reserva->estado = 'activa';
            $reserva->hora_salida = $horaFin;
            $reserva->save();

            Log::info('Reserva creada exitosamente:', [
                'id_reserva' => $reserva->id_reserva,
                'espacio_id' => $reserva->id_espacio,
                'run_profesor' => $reserva->run_profesor
            ]);

            Log::info('Estado del espacio antes de actualizar:', ['estado' => $espacio->estado]);
            $espacio->estado = 'Ocupado';
            $espacio->save();
            Log::info('Estado del espacio después de actualizar:', ['estado' => $espacio->estado]);

            // Verificar que el cambio se guardó correctamente
            $espacioVerificado = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            Log::info('Verificación del estado del espacio:', [
                'id_espacio' => $espacioVerificado->id_espacio,
                'estado_verificado' => $espacioVerificado->estado
            ]);

            DB::commit();
            Log::info('=== RESERVA DE PROFESOR CREADA EXITOSAMENTE ===');
            
            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado,
                    'tipo' => $reserva->tipo_reserva
                ],
                'mensaje' => 'Reserva creada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva específica para solicitantes
     */
    private function crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        try {
            // Verificar que el solicitante existe y está activo
            $solicitante = \App\Models\Solicitante::where('run_solicitante', $request->run)
                ->where('activo', true)
                ->first();

            if (!$solicitante) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Solicitante no encontrado o inactivo'
                ], 404);
            }

            // Verificar que el solicitante no tenga reservas activas
            $reservaActiva = Reserva::where('run_solicitante', $request->run)
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

            // Verificar límite de reservas diarias (máximo 2)
            $reservasHoy = Reserva::where('run_solicitante', $request->run)
                ->whereDate('fecha_reserva', $fechaActual)
                ->count();

            if ($reservasHoy >= 2) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Has alcanzado el límite de 2 reservas por día.',
                    'tipo' => 'limite_excedido'
                ], 400);
            }

            // Validar módulos consecutivos disponibles (máximo 2 para solicitantes)
            $modulosSolicitados = min($request->input('modulos', 1), 2);
            
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
                    'mensaje' => 'No hay módulo actual disponible.'
                ], 400);
            }

            // Verificar módulos consecutivos disponibles
            $planificaciones = Planificacion_Asignatura::where('id_espacio', $request->id_espacio)
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->pluck('id_modulo')
                ->toArray();

            $modulosDisponibles = 0;
            for ($i = $moduloActual; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;
                
                if (in_array($moduloCodigo, $planificaciones)) {
                    break;
                }
                
                $modulosDisponibles++;
                
                if ($modulosDisponibles >= $modulosSolicitados) {
                    break;
                }
            }

            if ($modulosDisponibles < $modulosSolicitados) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay suficientes módulos consecutivos disponibles.'
                ], 400);
            }

            // Calcular horarios
            $horariosModulos = [
                'lunes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'martes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'miercoles' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'jueves' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']],
                'viernes' => [1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'], 2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'], 3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'], 4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'], 5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'], 6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'], 7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'], 8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'], 9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'], 10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'], 11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'], 12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'], 13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'], 14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'], 15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']]
            ];

            $horariosDia = $horariosModulos[$diaActual] ?? null;
            $horaInicio = $horariosDia[$moduloActual]['inicio'] ?? $horaActual;
            $horaFin = $horariosDia[$moduloActual + $modulosSolicitados - 1]['fin'] ?? null;

            // Generar ID de reserva
            $lastReserva = Reserva::select('id_reserva')->orderByDesc('id_reserva')->first();
            $newIdNumber = $lastReserva ? 
                str_pad(intval(substr($lastReserva->id_reserva, 1)) + 1, 3, '0', STR_PAD_LEFT) : 
                '001';
            $newId = 'R' . $newIdNumber;

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = $newId;
            $reserva->hora = $horaInicio;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run_solicitante = $request->run;
            $reserva->run_profesor = null; // No es profesor
            $reserva->tipo_reserva = 'solicitante';
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
                'espacio' => $espacio->nombre_espacio
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio' => $espacio->nombre_espacio,
                    'solicitante' => $solicitante->nombre,
                    'tipo' => $reserva->tipo_reserva
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reserva de solicitante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarEstadoEspacioYReserva(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'run' => 'required',
            'id_espacio' => 'required|exists:espacios,id_espacio'
        ]);

        // Log para debugging
        Log::info('=== INICIO VERIFICACIÓN ESTADO ESPACIO Y RESERVA ===');
        Log::info('Datos recibidos:', $request->all());
        
        // Debug: Verificar si el espacio existe
        $espacioExiste = Espacio::find($request->id_espacio);
        Log::info('Espacio encontrado:', ['espacio' => $espacioExiste]);

        try {
            // 1. Verificar si el usuario tiene una reserva activa en este espacio específico
            $hoy = Carbon::today();
            Log::info('Fecha de hoy para comparación:', ['hoy' => $hoy->toDateString()]);
            
            $reservaEnEsteEspacio = Reserva::where('id_espacio', $request->id_espacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', $hoy)
                ->where(function($query) use ($request) {
                    $query->where('run_profesor', $request->run)
                          ->orWhere('run_solicitante', $request->run);
                })
                ->first();

            Log::info('Reserva en este espacio:', ['reserva' => $reservaEnEsteEspacio]);

            // Debug: Buscar todas las reservas para este usuario y espacio (sin filtros de fecha/estado)
            $todasLasReservas = Reserva::where('id_espacio', $request->id_espacio)
                ->where(function($query) use ($request) {
                    $query->where('run_profesor', $request->run)
                          ->orWhere('run_solicitante', $request->run);
                })
                ->get();
            Log::info('Todas las reservas para este usuario y espacio:', ['reservas' => $todasLasReservas]);

            if ($reservaEnEsteEspacio) {
                Log::info('Usuario tiene reserva activa en este espacio - proceder con devolución');
                // El usuario tiene una reserva activa en este espacio - puede devolver
                return response()->json([
                    'tipo' => 'devolucion',
                    'mensaje' => 'Puede proceder con la devolución de llaves'
                ]);
            }

            // 2. Verificar si el usuario tiene reservas activas en otros espacios
            $reservasActivasUsuario = Reserva::where('estado', 'activa')
                ->where('fecha_reserva', $hoy)
                ->where(function($query) use ($request) {
                    $query->where('run_profesor', $request->run)
                          ->orWhere('run_solicitante', $request->run);
                })
                ->get();

            Log::info('Reservas activas del usuario en otros espacios:', ['reservas' => $reservasActivasUsuario]);

            if ($reservasActivasUsuario->count() > 0) {
                Log::info('Usuario tiene reservas activas en otros espacios');
                // El usuario tiene reservas activas en otros espacios
                return response()->json([
                    'tipo' => 'reserva_existente',
                    'mensaje' => 'Ya tiene una reserva activa en otro espacio. No puede agendar de nuevo.'
                ]);
            }

            // 3. Verificar si el espacio está ocupado por otro usuario
            $espacio = Espacio::find($request->id_espacio);
            Log::info('Estado del espacio:', ['espacio' => $espacio]);

            if ($espacio->estado === 'Ocupado') {
                Log::info('Espacio está ocupado por otro usuario');
                return response()->json([
                    'tipo' => 'espacio_ocupado',
                    'mensaje' => 'El espacio está ocupado por otro usuario'
                ]);
            }

            // 4. El espacio está disponible y el usuario no tiene reservas activas
            Log::info('Espacio está disponible y usuario no tiene reservas activas');
            return response()->json([
                'tipo' => 'disponible',
                'mensaje' => 'El espacio está disponible para reservar'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar estado del espacio y reserva: ' . $e->getMessage());
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error al verificar el estado del espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function devolverLlaves(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'run' => 'required',
            'id_espacio' => 'required|exists:espacios,id_espacio'
        ]);

        // Log para debugging
        Log::info('=== INICIO DEVOLUCIÓN DE LLAVES ===');
        Log::info('Datos recibidos:', $request->all());

        DB::beginTransaction();
        try {
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            Log::info('Espacio encontrado:', ['espacio' => $espacio]);
            
            if (!$espacio) {
                Log::info('Espacio no encontrado');
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ]);
            }

            // Verificar si el espacio está ocupado
            Log::info('Estado del espacio:', ['estado' => $espacio->estado]);
            if ($espacio->estado !== 'Ocupado') {
                Log::info('El espacio no está ocupado');
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está ocupado'
                ]);
            }

            // Buscar la reserva activa para este espacio y usuario (profesor o solicitante)
            $hoy = Carbon::today();
            $reservaActiva = Reserva::select('id_reserva', 'estado', 'hora_salida', 'id_espacio', 'run_profesor', 'run_solicitante')
                ->where('id_espacio', $request->id_espacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', $hoy)
                ->where(function($query) use ($request) {
                    $query->where('run_profesor', $request->run)
                          ->orWhere('run_solicitante', $request->run);
                })
                ->first();

            Log::info('Reserva activa encontrada:', ['reserva' => $reservaActiva]);

            if (!$reservaActiva) {
                Log::info('No se encontró reserva activa');
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una reserva activa para este usuario y espacio'
                ]);
            }

            // Obtener información del usuario (profesor o solicitante)
            $nombreUsuario = '';
            
            if ($reservaActiva->run_profesor) {
                $usuario = User::where('run', $reservaActiva->run_profesor)->first();
                $nombreUsuario = $usuario ? $usuario->name : 'Profesor no encontrado';
            } elseif ($reservaActiva->run_solicitante) {
                $solicitante = \App\Models\Solicitante::where('run_solicitante', $reservaActiva->run_solicitante)->first();
                $nombreUsuario = $solicitante ? $solicitante->nombre : 'Solicitante no encontrado';
            }

            $reservaActiva->estado = 'finalizada';
            $reservaActiva->hora_salida = Carbon::now()->format('H:i:s');
            $reservaActiva->save();

            $espacio->estado = 'Disponible';
            $espacio->save();

            DB::commit();
            Log::info('Devolución completada exitosamente');
            return response()->json([
                'success' => true,
                'message' => 'Devolución completada',
                'reserva' => [
                    'id' => $reservaActiva->id_reserva,
                    'hora_salida' => $reservaActiva->hora_salida,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado,
                    'usuario' => $nombreUsuario
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarUsuarioNoRegistrado(Request $request)
    {
        try {
            $request->validate([
                'run' => 'required|string|unique:usuarios_no_registrados,run',
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios_no_registrados,email',
                'telefono' => 'required|string|max:20',
                'modulos_utilizacion' => 'required|integer|min:1|max:15'
            ]);

            // Crear el nuevo usuario no registrado
            $usuarioNoRegistrado = new \App\Models\UsuarioNoRegistrado();
            $usuarioNoRegistrado->run = $request->run;
            $usuarioNoRegistrado->nombre = $request->nombre;
            $usuarioNoRegistrado->email = $request->email;
            $usuarioNoRegistrado->telefono = $request->telefono;
            $usuarioNoRegistrado->modulos_utilizacion = $request->modulos_utilizacion;
            $usuarioNoRegistrado->save();

            return response()->json([
                'success' => true,
                'mensaje' => 'Usuario no registrado guardado exitosamente',
                'usuario' => [
                    'run' => $usuarioNoRegistrado->run,
                    'nombre' => $usuarioNoRegistrado->nombre,
                    'email' => $usuarioNoRegistrado->email,
                    'telefono' => $usuarioNoRegistrado->telefono,
                    'modulos_utilizacion' => $usuarioNoRegistrado->modulos_utilizacion,
                    'tipo' => 'no_registrado'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarUsuarioNoRegistrado($run)
    {
        try {
            $usuarioNoRegistrado = \App\Models\UsuarioNoRegistrado::where('run', $run)->first();

            if (!$usuarioNoRegistrado) {
                return response()->json([
                    'encontrado' => false,
                    'mensaje' => 'Usuario no registrado no encontrado'
                ]);
            }

            return response()->json([
                'encontrado' => true,
                'usuario' => [
                    'run' => $usuarioNoRegistrado->run,
                    'nombre' => $usuarioNoRegistrado->nombre,
                    'email' => $usuarioNoRegistrado->email,
                    'telefono' => $usuarioNoRegistrado->telefono,
                    'modulos_utilizacion' => $usuarioNoRegistrado->modulos_utilizacion,
                    'convertido_a_usuario' => $usuarioNoRegistrado->convertido_a_usuario
                ],
                'mensaje' => 'Usuario no registrado encontrado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'Error al verificar usuario no registrado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertirUsuarioNoRegistrado(Request $request)
    {
        try {
            $request->validate([
                'run' => 'required|string|exists:usuarios_no_registrados,run'
            ]);

            $usuarioNoRegistrado = \App\Models\UsuarioNoRegistrado::where('run', $request->run)->first();

            if (!$usuarioNoRegistrado) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Usuario no registrado no encontrado'
                ], 404);
            }

            if ($usuarioNoRegistrado->convertido_a_usuario) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El usuario ya fue convertido anteriormente'
                ], 400);
            }

            // Convertir a usuario registrado
            $usuarioRegistrado = $usuarioNoRegistrado->convertirAUsuarioRegistrado();

            return response()->json([
                'success' => true,
                'mensaje' => 'Usuario convertido exitosamente',
                'usuario' => [
                    'run' => $usuarioRegistrado->run,
                    'nombre' => $usuarioRegistrado->name,
                    'email' => $usuarioRegistrado->email,
                    'telefono' => $usuarioRegistrado->celular
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al convertir usuario: ' . $e->getMessage()
            ], 500);
        }
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
     * Verificar si el usuario tiene clases programadas y obtener información del horario
     */
    public function verificarClasesProgramadas($run, $horaActual, $diaActual)
    {
        try {
            \Log::info('Verificando clases programadas para usuario:', [
                'run' => $run,
                'hora_actual' => $horaActual,
                'dia_actual' => $diaActual
            ]);

            // Obtener día actual en formato string
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActualString = $diasSemana[$diaActual] ?? 'lunes';
            
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
            
            $codigoDia = $codigosDias[$diaActualString] ?? 'LU';
            
            // Determinar el módulo actual según la hora
            $moduloActual = $this->determinarModuloActual($horaActual, $diaActualString);
            
            if (!$moduloActual) {
                \Log::info('No hay módulo actual disponible');
                return [
                    'tiene_clases' => false,
                    'mensaje' => 'No hay módulo actual disponible'
                ];
            }

            // Buscar clases programadas para este usuario en el módulo actual o siguiente
            $clasesProgramadas = Planificacion_Asignatura::whereHas('asignatura', function($query) use ($run) {
                    $query->where('run_profesor', $run);
                })
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->whereRaw('CAST(SUBSTRING(id_modulo, 4) AS UNSIGNED) >= ?', [$moduloActual])
                ->whereRaw('CAST(SUBSTRING(id_modulo, 4) AS UNSIGNED) <= ?', [$moduloActual + 2]) // Buscar en módulo actual y siguientes 2
                ->with(['espacio:id_espacio,nombre_espacio,estado', 'asignatura.profesor'])
                ->get();

            \Log::info('Clases programadas encontradas:', [
                'total_clases' => $clasesProgramadas->count(),
                'clases' => $clasesProgramadas->toArray()
            ]);

            if ($clasesProgramadas->isEmpty()) {
                return [
                    'tiene_clases' => false,
                    'mensaje' => 'No tienes clases programadas en este horario'
                ];
            }

            // Obtener información del profesor
            $profesor = \App\Models\Profesor::where('run_profesor', $run)->first();
            
            $nombreProfesor = $profesor ? $profesor->name : 'Profesor';

            // Buscar la próxima clase (módulo más cercano)
            $proximaClase = $clasesProgramadas->sortBy(function($clase) {
                return (int) substr($clase->id_modulo, 4);
            })->first();

            \Log::info('Próxima clase encontrada:', [
                'clase' => $proximaClase->toArray()
            ]);

            return [
                'tiene_clases' => true,
                'profesor' => [
                    'run_profesor' => $run,
                    'nombre' => $nombreProfesor,
                    'tipo' => 'profesor'
                ],
                'proxima_clase' => [
                    'modulo' => $proximaClase->id_modulo,
                    'asignatura' => $proximaClase->asignatura,
                    'espacio' => [
                        'id' => $proximaClase->espacio->id_espacio,
                        'nombre' => $proximaClase->espacio->nombre_espacio,
                        'estado' => $proximaClase->espacio->estado
                    ],
                    'hora_inicio' => $this->obtenerHoraInicioModulo($proximaClase->id_modulo, $diaActualString),
                    'hora_fin' => $this->obtenerHoraFinModulo($proximaClase->id_modulo, $diaActualString)
                ],
                'todas_las_clases' => $clasesProgramadas->map(function($clase) use ($diaActualString) {
                    return [
                        'modulo' => $clase->id_modulo,
                        'asignatura' => $clase->asignatura,
                        'espacio' => [
                            'id' => $clase->espacio->id_espacio,
                            'nombre' => $clase->espacio->nombre_espacio,
                            'estado' => $clase->espacio->estado
                        ],
                        'hora_inicio' => $this->obtenerHoraInicioModulo($clase->id_modulo, $diaActualString),
                        'hora_fin' => $this->obtenerHoraFinModulo($clase->id_modulo, $diaActualString)
                    ];
                })->toArray()
            ];

        } catch (\Exception $e) {
            \Log::error('Error al verificar clases programadas:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'tiene_clases' => false,
                'mensaje' => 'Error al verificar clases programadas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener hora de inicio de un módulo específico
     */
    private function obtenerHoraInicioModulo($idModulo, $diaActual)
    {
        $horariosModulos = [
            'lunes' => [1 => '08:10:00', 2 => '09:10:00', 3 => '10:10:00', 4 => '11:10:00', 5 => '12:10:00', 6 => '13:10:00', 7 => '14:10:00', 8 => '15:10:00', 9 => '16:10:00', 10 => '17:10:00', 11 => '18:10:00', 12 => '19:10:00', 13 => '20:10:00', 14 => '21:10:00', 15 => '22:10:00'],
            'martes' => [1 => '08:10:00', 2 => '09:10:00', 3 => '10:10:00', 4 => '11:10:00', 5 => '12:10:00', 6 => '13:10:00', 7 => '14:10:00', 8 => '15:10:00', 9 => '16:10:00', 10 => '17:10:00', 11 => '18:10:00', 12 => '19:10:00', 13 => '20:10:00', 14 => '21:10:00', 15 => '22:10:00'],
            'miercoles' => [1 => '08:10:00', 2 => '09:10:00', 3 => '10:10:00', 4 => '11:10:00', 5 => '12:10:00', 6 => '13:10:00', 7 => '14:10:00', 8 => '15:10:00', 9 => '16:10:00', 10 => '17:10:00', 11 => '18:10:00', 12 => '19:10:00', 13 => '20:10:00', 14 => '21:10:00', 15 => '22:10:00'],
            'jueves' => [1 => '08:10:00', 2 => '09:10:00', 3 => '10:10:00', 4 => '11:10:00', 5 => '12:10:00', 6 => '13:10:00', 7 => '14:10:00', 8 => '15:10:00', 9 => '16:10:00', 10 => '17:10:00', 11 => '18:10:00', 12 => '19:10:00', 13 => '20:10:00', 14 => '21:10:00', 15 => '22:10:00'],
            'viernes' => [1 => '08:10:00', 2 => '09:10:00', 3 => '10:10:00', 4 => '11:10:00', 5 => '12:10:00', 6 => '13:10:00', 7 => '14:10:00', 8 => '15:10:00', 9 => '16:10:00', 10 => '17:10:00', 11 => '18:10:00', 12 => '19:10:00', 13 => '20:10:00', 14 => '21:10:00', 15 => '22:10:00']
        ];

        $numeroModulo = (int) substr($idModulo, 4);
        return $horariosModulos[$diaActual][$numeroModulo] ?? '08:10:00';
    }

    /**
     * Obtener hora de fin de un módulo específico
     */
    private function obtenerHoraFinModulo($idModulo, $diaActual)
    {
        $horariosModulos = [
            'lunes' => [1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00', 5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00', 9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00', 13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00'],
            'martes' => [1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00', 5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00', 9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00', 13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00'],
            'miercoles' => [1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00', 5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00', 9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00', 13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00'],
            'jueves' => [1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00', 5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00', 9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00', 13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00'],
            'viernes' => [1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00', 5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00', 9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00', 13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00']
        ];

        $numeroModulo = (int) substr($idModulo, 4);
        return $horariosModulos[$diaActual][$numeroModulo] ?? '09:00:00';
    }
} 