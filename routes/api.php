<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiReservaController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ProgramacionSemanalController;
use App\Http\Controllers\ProfesorColaboradorController;
use App\Models\User;
use App\Http\Controllers\EspacioController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PlanoDigitalController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rutas de Asistencia (Attendance API)
|--------------------------------------------------------------------------
*/

// Registrar asistencia de estudiante
Route::post('/attendance', [AttendanceController::class, 'store']);

// Obtener listado de asistencias por reserva
Route::get('/attendance/reservation/{reservationId}', [AttendanceController::class, 'show']);

// Route for key return notifications moved to web.php to use session authentication

// API para profesores colaboradores
Route::get('/clases-temporales/horarios-ocupados', [ProfesorColaboradorController::class, 'getHorariosOcupados']);
Route::post('/clases-temporales/salas-disponibles', [ProfesorColaboradorController::class, 'getSalasDisponibles']);

// Ruta para verificar si un profesor tiene asignado un espacio
Route::get('/verificar-espacio/{profesorId}/{espacioId}', function ($profesorId, $espacioId, Request $request) {
    try {
        $dia = $request->query('dia');
        $hora = $request->query('hora');

        // Verificar si el profesor tiene clases en este espacio en la hora actual
        $planificacion = \App\Models\Planificacion_Asignatura::with(['asignatura', 'modulo'])
            ->where('id_espacio', $espacioId)
            ->whereHas('asignatura', function ($query) use ($profesorId) {
                $query->where('run', $profesorId);
            })
            ->whereHas('modulo', function ($query) use ($dia, $hora) {
                $query->where('dia', $dia)
                    ->where('hora_inicio', '<=', $hora)
                    ->where('hora_termino', '>=', $hora);
            })
            ->first();

        if ($planificacion) {
            return response()->json([
                'esValido' => true,
                'mensaje' => 'El profesor tiene clase asignada en este espacio',
                'detalles' => [
                    'asignatura' => $planificacion->asignatura->nombre_asignatura,
                    'horario' => [
                        'inicio' => $planificacion->modulo->hora_inicio,
                        'termino' => $planificacion->modulo->hora_termino
                    ]
                ]
            ]);
        }

        return response()->json([
            'esValido' => false,
            'mensaje' => 'El profesor no tiene clases asignadas en este espacio en el horario actual'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'esValido' => false,
            'mensaje' => 'Error al verificar el espacio: ' . $e->getMessage()
        ], 500);
    }
});

// Rutas para reservas
Route::get('/verificar-espacio/{userId}/{espacioId}', [ApiReservaController::class, 'verificarEspacio']);
Route::post('/registrar-uso-espacio', [ApiReservaController::class, 'registrarUsoEspacio']);
Route::post('/registrar-salida-clase', [ApiReservaController::class, 'registrarSalidaClase']);
Route::post('/registrar-reserva-espontanea', [ApiReservaController::class, 'registrarReservaEspontanea']);
Route::post('/registrar-entrada-clase', [ApiReservaController::class, 'registrarUsoEspacio']);
Route::get('/reserva-activa/{id}', [App\Http\Controllers\Api\ApiReservaController::class, 'getReservaActiva']);

Route::get('/user/{run}', function ($run) {
    try {
        // Limpiar el run recibido (quitar guion y dígito verificador si existen)
        $runLimpio = preg_replace('/[^0-9]/', '', $run);

        // Buscar por run exacto o por run sin dígito verificador
        $user = User::where('run', $run)
            ->orWhere('run', $runLimpio)
            ->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->run,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'El profesor no se encuentra registrado, contáctese con soporte.'
            ], 404);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al buscar el profesor, intente nuevamente.'
        ], 500);
    }
});

Route::get('/verificar-clase-usuario', function ($request) {
    $run = $request->query('run');
    $espacioId = $request->query('espacio');
    $dia = $request->query('dia');
    $hora = $request->query('hora');

    $clase = \DB::table('planificacion_asignaturas as pa')
        ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
        ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
        ->join('espacios as e', 'pa.id_espacio', '=', 'e.id_espacio')
        ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
        ->join('users as u', 'h.run', '=', 'u.run')
        ->where(function ($q) use ($espacioId) {
            $q->where('pa.id_espacio', $espacioId)
                ->orWhere('e.nombre_espacio', $espacioId);
        })
        ->where('h.run', $run)
        ->where('m.dia', $dia)
        ->where('m.hora_inicio', '<=', $hora)
        ->where('m.hora_termino', '>=', $hora)
        ->select('a.nombre_asignatura', 'm.dia', 'm.hora_inicio', 'm.hora_termino')
        ->first();

    if ($clase) {
        return response()->json([
            'success' => true,
            'tiene_clase' => true,
            'mensaje' => 'Usted tiene una clase programada en este espacio.',
            'clase' => $clase
        ]);
    } else {
        return response()->json([
            'success' => true,
            'tiene_clase' => false,
            'mensaje' => 'No tiene clases asociadas en este espacio en este horario.'
        ]);
    }
});

Route::get('/espacio/{id}', function ($id) {
    try {
        $espacio = \DB::table('espacios')
            ->where('id_espacio', $id)
            ->orWhere('nombre_espacio', $id)
            ->first();
        
        if ($espacio) {
            return response()->json([
                'success' => true,
                'espacio' => $espacio
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Espacio no encontrado'
            ], 404);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
});

// Endpoint para consultar módulos disponibles para reserva en un espacio
Route::get('/espacio/{espacio}/modulos-disponibles', [EspacioController::class, 'modulosDisponibles']);

// Endpoint para obtener asignaturas de un profesor en el día actual
Route::get('/profesor/{run}/asignaturas-hoy', [EspacioController::class, 'getAsignaturasProfesorHoy']);
Route::get('/espacio/{id}/informacion-detallada', [EspacioController::class, 'getInformacionDetalladaEspacio']);

// Rutas para manejo de caché
Route::prefix('cache')->group(function () {
    Route::get('/health', [App\Http\Controllers\CacheHealthController::class, 'healthCheck']);
    Route::post('/clear', [App\Http\Controllers\CacheHealthController::class, 'clearCache']);
    Route::post('/create-structure', [App\Http\Controllers\CacheHealthController::class, 'createCacheStructure']);
    Route::get('/stats', [App\Http\Controllers\CacheHealthController::class, 'stats']);
});

// Ruta para verificar si un espacio está ocupado en un módulo específico
Route::get('/verificar-planificacion/{id_espacio}/{id_modulo}', function ($id_espacio, $id_modulo) {
    try {
        // Extraer el día y número de módulo del id_modulo (ejemplo: "MI.5")
        list($dia, $numeroModulo) = explode('.', $id_modulo);

        // Verificar si existe planificación para este espacio y módulo
        $tienePlanificacion = DB::table('planificacion_asignaturas')
            ->where('id_espacio', $id_espacio)
            ->where('id_modulo', $id_modulo)
            ->exists();

        return response()->json([
            'ocupado' => $tienePlanificacion
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al verificar la planificación',
            'ocupado' => false
        ], 500);
    }
});

// Ruta para verificar la planificación de múltiples espacios
Route::get('/verificar-planificacion-multiple', function (Request $request) {
    try {
        $id_modulo = $request->query('id_modulo');
        $espacios = explode(',', $request->query('espacios'));

        if (!$id_modulo || empty($espacios)) {
            return response()->json([
                'error' => 'Se requieren id_modulo y espacios'
            ], 400);
        }

        // Verificar planificación para todos los espacios
        $planificaciones = DB::table('planificacion_asignaturas')
            ->whereIn('id_espacio', $espacios)
            ->where('id_modulo', $id_modulo)
            ->pluck('id_espacio')
            ->toArray();

        return response()->json([
            'espacios_ocupados' => $planificaciones
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al verificar la planificación',
            'espacios_ocupados' => []
        ], 500);
    }
});


// Ruta para obtener pisos de la sede TH y facultad IT_TH
Route::get('/pisos/th/it', function () {
    try {
        $pisos = \App\Models\Piso::with(['facultad.sede'])
            ->whereHas('facultad', function ($query) {
                $query->where('id_facultad', 'IT_TH');
            })
            ->whereHas('facultad.sede', function ($query) {
                $query->where('id_sede', 'TH');
            })
            ->orderBy('numero_piso')
            ->get();

        return response()->json([
            'success' => true,
            'pisos' => $pisos
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los pisos: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/verificar-horario/{run}', [PlanoDigitalController::class, 'verificarHorario']);

Route::get('/verificar-usuario/{run}', [PlanoDigitalController::class, 'verificarUsuario']);
Route::get('/verificar-profesor/{run}', [PlanoDigitalController::class, 'verificarProfesor']);

Route::get('/verificar-espacio/{idEspacio}', [PlanoDigitalController::class, 'verificarEspacio']);
Route::post('/crear-reserva-profesor', [App\Http\Controllers\ProfesorController::class, 'crearReservaProfesor']);
Route::get('/profesor/{run}/asignaturas', [App\Http\Controllers\ProfesorController::class, 'getAsignaturasProfesor']);
Route::post('/verificar-estado-espacio-reserva', [PlanoDigitalController::class, 'verificarEstadoEspacioYReserva']);
Route::post('/devolver-llaves', [PlanoDigitalController::class, 'devolverLlaves']);
Route::post('/verificar-reserva-activa', [PlanoDigitalController::class, 'verificarReservaActiva']);

// Rutas para solicitantes
Route::get('/verificar-solicitante/{run}', [App\Http\Controllers\SolicitanteController::class, 'verificarSolicitante']);
Route::post('/registrar-solicitante', [App\Http\Controllers\SolicitanteController::class, 'registrarSolicitante']);
Route::post('/crear-reserva-solicitante', [App\Http\Controllers\SolicitanteController::class, 'crearReservaSolicitante']);

Route::get('/espacios/estados', [PlanoDigitalController::class, 'estadosEspacios']);

// Ruta para obtener la información del espacio actual (para desocupar - desde servidor)
Route::post('/obtener-info-espacio-desocupar', [PlanoDigitalController::class, 'obtenerInfoEspacioParaDesocupar']);

// Ruta para devolver espacios
Route::post('/devolver-espacio', [PlanoDigitalController::class, 'devolverEspacio']);

// ========================================
// RUTAS PARA QR PERSONAL Y LIBERACIÓN FORZADA
// ========================================
use App\Http\Controllers\QrPersonalController;

// Verificar si un QR escaneado es un QR personal válido
Route::post('/qr-personal/verificar-escaneado', [QrPersonalController::class, 'verificarQrPersonalEscaneado']);

// Liberar sala forzadamente (requiere QR personal con permiso)
Route::post('/qr-personal/liberar-sala', [QrPersonalController::class, 'liberarSalaForzadamente']);

// Ruta para registrar si hubo asistentes en una clase (devolución anticipada)
Route::post('/registrar-asistencia-clase', [PlanoDigitalController::class, 'registrarAsistenciaClase']);

// Ruta para devolver llaves (duplicada - removida)
// Route::post('/reserva/devolver', [ApiReservaController::class, 'devolverLlaves']);

// Ruta para verificar la programación de un usuario en un espacio específico
Route::get('/verificar-programacion/{espacio}/{usuario}', function ($espacio, $usuario) {
    try {
        // Obtener la hora actual
        $horaActual = \Carbon\Carbon::now();
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');

        // Verificar si el usuario tiene clase programada en este espacio
        $tieneProgramacion = DB::table('planificacion_asignaturas as pa')
            ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
            ->where('pa.id_espacio', $espacio)
            ->where('h.run', $usuario)
            ->where('m.dia', $diaActual)
            ->where(function($query) use ($horaActualStr) {
                $query->where('m.hora_inicio', '<=', $horaActualStr)
                      ->where('m.hora_termino', '>=', $horaActualStr);
            })
            ->exists();

        return response()->json([
            'success' => true,
            'tieneProgramacion' => $tieneProgramacion
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al verificar la programación: ' . $e->getMessage()
        ], 500);
    }
});

// Endpoint para verificar clases programadas de un usuario
Route::get('/verificar-clases-programadas/{run}', function ($run) {
    try {
        $horaActual = now()->format('H:i:s');
        $diaActual = now()->dayOfWeek; // 0 = domingo, 1 = lunes, etc.
        
        $controller = new PlanoDigitalController();
        $resultado = $controller->verificarClasesProgramadas($run, $horaActual, $diaActual);
        
        return response()->json($resultado);
    } catch (\Exception $e) {
        return response()->json([
            'tiene_clases' => false,
            'mensaje' => 'Error al verificar clases programadas: ' . $e->getMessage()
        ], 500);
    }
});

// ========================================
// RUTAS DEL PANEL DE ADMINISTRACIÓN
// ========================================

use App\Http\Controllers\AdminPanelController;

// Búsqueda de usuarios
Route::get('/buscar-usuario/{run}', [AdminPanelController::class, 'buscarUsuario']);

// Gestión de espacios
Route::get('/espacios/disponibles', [AdminPanelController::class, 'getEspaciosDisponibles']);
Route::get('/admin/espacios', [AdminPanelController::class, 'getEspacios']);
Route::put('/admin/espacio/{codigo}/estado', [AdminPanelController::class, 'cambiarEstadoEspacio']);

// Gestión de reservas
Route::post('/admin/crear-reserva', [AdminPanelController::class, 'crearReserva']);
Route::get('/admin/reservas', [AdminPanelController::class, 'getReservas']);
Route::put('/admin/reserva/{id}/estado', [AdminPanelController::class, 'cambiarEstadoReserva']);

// Operaciones masivas
Route::post('/admin/vaciar-reservas', [AdminPanelController::class, 'vaciarReservas']);

// ========================================
// RUTAS DE PROGRAMACIÓN SEMANAL Y ASISTENCIA
// ========================================

// Consultar programación semanal por sala (GET)
Route::get('/programacion-semanal/{id_espacio}', [ProgramacionSemanalController::class, 'obtenerProgramacionSemanal']);

// Registrar asistencia (POST)
Route::post('/asistencia', [ProgramacionSemanalController::class, 'registrarAsistencia']);

// Obtener reserva activa de un espacio (GET)
Route::get('/reservas/activa/{id_espacio}', [ProgramacionSemanalController::class, 'obtenerReservaActiva']);

// ========================================
// RUTAS DE ESPACIOS Y TIPOS DE ESPACIOS
// ========================================

use App\Http\Controllers\Api\EspacioApiController;

// Listar todos los espacios (con filtros opcionales: tipo_espacio, estado, piso_id)
Route::get('/espacios', [EspacioApiController::class, 'listarEspacios']);

// Listar todos los tipos de espacios
Route::get('/tipos-espacios', [EspacioApiController::class, 'listarTiposEspacios']);

// Obtener resumen de espacios agrupados por tipo y estado
Route::get('/espacios/resumen', [EspacioApiController::class, 'resumenEspacios']);

// ========================================
// RUTAS DE SALAS DE ESTUDIO
// ========================================

use App\Http\Controllers\SalaEstudioController;

// Registrar acceso a sala de estudio
Route::post('/sala-estudio/registrar-acceso', [SalaEstudioController::class, 'registrarAcceso']);

// Obtener alumnos registrados en sala de estudio
Route::get('/sala-estudio/{id_espacio}/alumnos-registrados', [SalaEstudioController::class, 'obtenerAlumnosRegistrados']);

// Gestión de vetos
Route::post('/sala-estudio/vetar-individual', [SalaEstudioController::class, 'vetarIndividual']);
Route::post('/sala-estudio/vetar-grupo', [SalaEstudioController::class, 'vetarGrupo']);
Route::put('/sala-estudio/veto/{id}/liberar', [SalaEstudioController::class, 'liberarVeto']);
Route::put('/sala-estudio/veto/{id}', [SalaEstudioController::class, 'actualizarVeto']);
Route::get('/sala-estudio/vetos', [SalaEstudioController::class, 'listarVetados']);
Route::get('/sala-estudio/vetos/export', [SalaEstudioController::class, 'exportarVetos']);

// Verificar si un RUN existe en solicitantes
Route::get('/verificar-solicitante/{run}', [App\Http\Controllers\SolicitanteController::class, 'verificarExistencia']);

// Obtener todos los módulos del sistema
Route::get('/modulos', function () {
    $modulos = \App\Models\Modulo::distinct()
        ->select('id_modulo', 'hora_inicio', 'hora_termino')
        ->orderBy('hora_inicio')
        ->get()
        ->map(function ($modulo) {
            // Extraer el número del módulo del id (ej: "JU.1" -> 1)
            $partes = explode('.', $modulo->id_modulo);
            $numeroModulo = isset($partes[1]) ? $partes[1] : 1;
            
            return [
                'id_modulo' => $numeroModulo,
                'hora_inicio' => substr($modulo->hora_inicio, 0, 5),
                'hora_termino' => substr($modulo->hora_termino, 0, 5)
            ];
        })
        ->unique('id_modulo')
        ->values()
        ->toArray();

    return response()->json($modulos);
});

// Obtener espacios disponibles para una fecha y rango de módulos
Route::get('/espacios-disponibles/{fecha}/{moduloInicio}/{moduloFin}', function ($fecha, $moduloInicio, $moduloFin) {
    try {
        // Obtener todos los espacios disponibles (excluyendo salas de estudio, talleres y laboratorios excepto computación)
        $espacios = \App\Models\Espacio::where('estado', 'Disponible')
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala de estudio%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala estudio%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%taller%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%laboratorio%' OR LOWER(tipo_espacio) LIKE '%laboratorio de computacion%' OR LOWER(tipo_espacio) LIKE '%laboratorio computacion%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%estudio%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%taller%'")
            ->select('id_espacio', 'nombre_espacio', 'tipo_espacio')
            ->orderBy('nombre_espacio')
            ->get();

        // Convertir fecha a día de la semana
        $fechaParsed = \Carbon\Carbon::parse($fecha);
        $nombreDia = strtolower($fechaParsed->format('l'));
        
        // Mapear nombre del día en inglés a prefijo en BD (mayúsculas)
        $diasMap = [
            'monday' => 'LU',
            'tuesday' => 'MA',
            'wednesday' => 'MI',
            'thursday' => 'JU',
            'friday' => 'VI',
            'saturday' => 'SA',
            'sunday' => 'DO'
        ];
        $prefijoDia = $diasMap[$nombreDia] ?? 'LU';

        // Crear array de módulos a verificar
        $modulosAVerificar = range((int)$moduloInicio, (int)$moduloFin);
        $modulosIds = array_map(function($num) use ($prefijoDia) {
            return "{$prefijoDia}.{$num}";
        }, $modulosAVerificar);

        // Obtener horarios del primer y último módulo
        $primerModuloObj = \App\Models\Modulo::find($modulosIds[0]);
        $ultimoModuloObj = \App\Models\Modulo::find(end($modulosIds));
        
        if (!$primerModuloObj || !$ultimoModuloObj) {
            return response()->json([
                'error' => 'Módulo no encontrado',
                'debug' => [
                    'modulos_construidos' => $modulosIds
                ]
            ], 404);
        }

        $horaInicio = substr($primerModuloObj->hora_inicio, 0, 5);
        $horaFin = substr($ultimoModuloObj->hora_termino, 0, 5);

        // Filtrar espacios que NO tengan reserva o planificación en NINGUNO de los módulos del rango
        $espaciosDisponibles = $espacios->filter(function ($espacio) use ($fecha, $modulosIds, $modulosAVerificar) {
            // Verificar si hay PLANIFICACIÓN (clases programadas) en este espacio para CUALQUIERA de los módulos
            foreach ($modulosIds as $moduloId) {
                $planificado = \App\Models\Planificacion_Asignatura::where('id_espacio', $espacio->id_espacio)
                    ->where('id_modulo', $moduloId)
                    ->exists();
                
                if ($planificado) {
                    return false; // Si tiene clase planificada en cualquier módulo, no está disponible
                }
            }
            
            // Verificar si hay RESERVA en este espacio para esta fecha en CUALQUIERA de los módulos
            foreach ($modulosAVerificar as $numModulo) {
                $reservado = \App\Models\Reserva::where('id_espacio', $espacio->id_espacio)
                    ->whereDate('fecha_reserva', $fecha)
                    ->whereRaw("modulos LIKE ?", ["%{$numModulo}%"])
                    ->whereIn('estado', ['confirmada', 'en_curso', 'activa'])
                    ->exists();
                
                if ($reservado) {
                    return false; // Si está reservado en cualquier módulo, no está disponible
                }
            }
            
            return true; // Solo está disponible si está libre en TODOS los módulos (sin planificación ni reserva)
        })->values();

        return response()->json([
            'espacios' => $espaciosDisponibles->map(function($espacio) {
                return [
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'tipo_espacio' => $espacio->tipo_espacio,
                    'display_name' => "{$espacio->id_espacio} - {$espacio->nombre_espacio}"
                ];
            }),
            'modulos_verificados' => $modulosIds,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'fecha' => $fecha,
            'total_disponibles' => count($espaciosDisponibles),
            'total_espacios_base' => $espacios->count()
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

