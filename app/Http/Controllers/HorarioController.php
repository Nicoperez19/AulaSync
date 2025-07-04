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

class HorarioController extends Controller
{
    public function verificarUsuario($run)
    {
        try {
            // Primero verificar en la tabla de usuarios registrados
            $usuario = User::select('run', 'name', 'email', 'celular')->where('run', $run)->first();

            if ($usuario) {
                return response()->json([
                    'verificado' => true,
                    'usuario_no_registrado' => false,
                    'tipo_usuario' => 'registrado',
                    'usuario' => [
                        'run' => $usuario->run,
                        'nombre' => $usuario->name,
                        'email' => $usuario->email,
                        'telefono' => $usuario->celular
                    ],
                    'mensaje' => 'Usuario registrado verificado correctamente'
                ]);
            }

            // Si no está en usuarios registrados, verificar en usuarios no registrados
            $usuarioNoRegistrado = \App\Models\UsuarioNoRegistrado::where('run', $run)->first();

            if ($usuarioNoRegistrado) {
                return response()->json([
                    'verificado' => true,
                    'usuario_no_registrado' => true,
                    'tipo_usuario' => 'no_registrado',
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

            // Si no está en ninguna tabla
            return response()->json([
                'verificado' => false,
                'usuario_no_registrado' => true,
                'tipo_usuario' => 'nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado en la base de datos. Se requiere registro previo.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'verificado' => false,
                'usuario_no_registrado' => false,
                'mensaje' => 'Error al verificar usuario: ' . $e->getMessage()
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

    public function crearReserva(Request $request)
    {
        DB::beginTransaction();
        try {
            \Log::info('=== INICIO CREAR RESERVA ===');
            \Log::info('Datos recibidos:', $request->all());
            
            // ========================================
            // DEFINIR VARIABLES DE FECHA Y HORA AL INICIO
            // ========================================
            $ahora = Carbon::now();
            $horaActual = $ahora->format('H:i:s');
            $fechaActual = $ahora->toDateString();
            
            \Log::info('Variables de tiempo definidas:', [
                'hora_actual' => $horaActual,
                'fecha_actual' => $fechaActual
            ]);
            
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            if (!$espacio) {
                DB::rollBack();
                \Log::error('Espacio no encontrado:', ['id_espacio' => $request->id_espacio]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            \Log::info('Espacio encontrado:', [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'estado_actual' => $espacio->estado
            ]);

            if ($espacio->estado === 'Ocupado') {
                DB::rollBack();
                \Log::warning('Espacio ya está ocupado');
                return response()->json([
                    'success' => false,
                    'mensaje' => '¿Desea devolver las llaves?',
                    'tipo' => 'devolucion',
                    'espacio' => $espacio->nombre_espacio
                ]);
            }

            // ========================================
            // VALIDACIONES DE USUARIO Y RESERVAS
            // ========================================
            
            // 1. Verificar si el usuario ya tiene una reserva activa en cualquier sala
            $reservaActivaUsuario = Reserva::where('run', $request->run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaActivaUsuario) {
                DB::rollBack();
                \Log::warning('Usuario ya tiene reserva activa:', [
                    'run' => $request->run,
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
            $reservaPendienteHoy = Reserva::where('run', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->exists();

            if ($reservaPendienteHoy) {
                DB::rollBack();
                \Log::warning('Usuario ya tiene reserva pendiente para hoy:', ['run' => $request->run, 'fecha' => $fechaActual]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva pendiente para hoy. Solo puedes tener una reserva por día.',
                    'tipo' => 'reserva_diaria'
                ]);
            }

            // 3. Verificar si el usuario ha excedido el límite de reservas diarias (opcional)
            $reservasHoy = Reserva::where('run', $request->run)
                ->where('fecha_reserva', $fechaActual)
                ->count();

            $limiteReservasDiarias = 3; // Puedes ajustar este límite según tus necesidades
            if ($reservasHoy >= $limiteReservasDiarias) {
                DB::rollBack();
                \Log::warning('Usuario ha excedido el límite de reservas diarias:', [
                    'run' => $request->run, 
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
            $reservaMismoEspacio = Reserva::where('run', $request->run)
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
            $reserva->run = $request->run;
            $reserva->tipo_reserva = 'directa';
            $reserva->estado = 'activa';
            $reserva->hora_salida = $horaFin;
            $reserva->save();

            \Log::info('Reserva creada exitosamente:', [
                'id_reserva' => $reserva->id_reserva,
                'espacio_id' => $reserva->id_espacio,
                'run' => $reserva->run
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
            \Log::error('Error al crear reserva:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function devolverLlaves(Request $request)
    {
        DB::beginTransaction();
        try {
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            if (!$espacio) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            $reservaActiva = Reserva::select('id_reserva', 'estado', 'hora_salida', 'id_espacio')
                ->where('id_espacio', $request->id_espacio)
                ->where('estado', 'activa')
                ->first();

            if (!$reservaActiva) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay una reserva activa para este espacio'
                ]);
            }

            $reservaActiva->estado = 'finalizada';
            $reservaActiva->hora_salida = Carbon::now()->format('H:i:s');
            $reservaActiva->save();

            $espacio->estado = 'Disponible';
            $espacio->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'mensaje' => 'Devolución de llaves registrada exitosamente',
                'reserva' => [
                    'id' => $reservaActiva->id_reserva,
                    'hora_salida' => $reservaActiva->hora_salida,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar devolución: ' . $e->getMessage()
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

            // Generar QR para el usuario no registrado
            $usuarioNoRegistrado->generateQR();

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
} 