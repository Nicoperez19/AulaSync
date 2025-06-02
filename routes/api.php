<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservaController;

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
