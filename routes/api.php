<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiReservaController;
use App\Models\User;
use App\Http\Controllers\EspacioController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HorarioController;
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

// Route for key return notifications moved to web.php to use session authentication

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
        \Log::error('Error al verificar espacio:', [
            'error' => $e->getMessage(),
            'profesorId' => $profesorId,
            'espacioId' => $espacioId,
            'dia' => $dia,
            'hora' => $hora
        ]);

        return response()->json([
            'esValido' => false,
            'mensaje' => 'Error al verificar el espacio: ' . $e->getMessage()
        ], 500);
    }
});

// Rutas para reservas
Route::get('/verificar-espacio/{userId}/{espacioId}', [ApiReservaController::class, 'verificarEspacio']);
Route::post('/registrar-ingreso-clase', [ApiReservaController::class, 'registrarIngresoClase']);
Route::post('/registrar-salida-clase', [ApiReservaController::class, 'registrarSalidaClase']);
Route::post('/registrar-reserva-espontanea', [ApiReservaController::class, 'registrarReservaEspontanea']);
Route::post('/registrar-entrada-clase', [ApiReservaController::class, 'registrarIngresoClase']);
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
        \Log::error('Error al buscar espacio: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
});

// Endpoint para consultar módulos disponibles para reserva en un espacio
Route::get('/espacio/{espacio}/modulos-disponibles', [EspacioController::class, 'modulosDisponibles']);

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
        \Log::error('Error al verificar planificación: ' . $e->getMessage());
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
        \Log::error('Error al verificar planificación múltiple: ' . $e->getMessage());
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
        \Log::error('Error al obtener pisos:', [
            'error' => $e->getMessage(),
            'sede' => 'TH',
            'facultad' => 'IT_TH'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los pisos: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/verificar-horario/{run}', [HorarioController::class, 'verificarHorario']);

Route::get('/verificar-usuario/{run}', [HorarioController::class, 'verificarUsuario']);
Route::get('/verificar-espacio/{idEspacio}', [HorarioController::class, 'verificarEspacio']);
Route::post('/crear-reserva', [HorarioController::class, 'crearReserva']);
Route::post('/devolver-llaves', [HorarioController::class, 'devolverLlaves']);

Route::get('/espacios/estados', [PlanoDigitalController::class, 'estadosEspacios']);

// Ruta para devolver llaves
Route::post('/reserva/devolver', [ApiReservaController::class, 'devolverLlaves']);

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
        \Log::error('Error al verificar programación: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al verificar la programación: ' . $e->getMessage()
        ], 500);
    }
});

