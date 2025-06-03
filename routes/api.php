<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservaController;
use App\Models\User;

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
Route::get('/verificar-espacio/{userId}/{espacioId}', [ReservaController::class, 'verificarEspacio']);
Route::post('/registrar-ingreso-clase', [ReservaController::class, 'registrarIngresoClase']);
Route::post('/registrar-reserva-espontanea', [ReservaController::class, 'registrarReservaEspontanea']);

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

Route::get('/verificar-clase-usuario', function (\Illuminate\Http\Request $request) {
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
        ->where(function($q) use ($espacioId) {
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
});
