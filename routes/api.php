<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiReservaController;
use App\Http\Controllers\Api\ProgramacionSemanalController;
use App\Http\Controllers\ProfesorColaboradorController;
use App\Models\User;
use App\Http\Controllers\EspacioController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PlanoDigitalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Docente\AsistenciaController;
use App\Http\Controllers\SolicitanteController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\CacheHealthController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\Api\EspacioApiController;
use App\Http\Controllers\SalaEstudioController;

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
| Rutas de Asistencia de Estudiantes (Agente Lector QR USB en segundo plano)
|--------------------------------------------------------------------------
*/
Route::post('/docente/asistencia/registrar-qr', [AsistenciaController::class, 'registrarPorQr']);

/*
|--------------------------------------------------------------------------
| Profesores Colaboradores y Clases Temporales
|--------------------------------------------------------------------------
*/
Route::get('/clases-temporales/horarios-ocupados', [ProfesorColaboradorController::class, 'getHorariosOcupados']);
Route::post('/clases-temporales/salas-disponibles', [ProfesorColaboradorController::class, 'getSalasDisponibles']);

/*
|--------------------------------------------------------------------------
| Rutas para Reservas y Uso de Espacios
|--------------------------------------------------------------------------
*/
Route::get('/verificar-espacio/{userId}/{espacioId}', [ApiReservaController::class, 'verificarEspacio']);
Route::post('/registrar-uso-espacio', [ApiReservaController::class, 'registrarUsoEspacio']);
Route::post('/registrar-salida-clase', [ApiReservaController::class, 'registrarSalidaClase']);
Route::post('/registrar-reserva-espontanea', [ApiReservaController::class, 'registrarReservaEspontanea']);
Route::get('/reserva-activa/{id}', [ApiReservaController::class, 'getReservaActiva']);
// 🔑 Liberar sala bloqueada + registrar nuevo uso (cuando profesor anterior no devolvió llave)
Route::post('/liberar-y-registrar-uso', [ApiReservaController::class, 'liberarYRegistrarUso']);

Route::get('/user/{run}', function ($run) {
    try {
        $runLimpio = preg_replace('/[^0-9]/', '', $run);

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

Route::get('/espacio/{id}', function ($id) {
    try {
        $espacio = DB::table('espacios')
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

/*
|--------------------------------------------------------------------------
| Manejo de Caché
|--------------------------------------------------------------------------
*/
Route::prefix('cache')->group(function () {
    Route::get('/health', [CacheHealthController::class, 'healthCheck']);
    Route::post('/clear', [CacheHealthController::class, 'clearCache']);
    Route::post('/create-structure', [CacheHealthController::class, 'createCacheStructure']);
    Route::get('/stats', [CacheHealthController::class, 'stats']);
});

/*
|--------------------------------------------------------------------------
| Rutas del Plano Digital / Tótem
|--------------------------------------------------------------------------
*/
Route::get('/procesar-primera-lectura/{run}', [PlanoDigitalController::class, 'procesarPrimeraLectura']);
Route::get('/verificar-usuario/{run}', [PlanoDigitalController::class, 'verificarUsuario']);
Route::get('/verificar-espacio/{idEspacio}', [PlanoDigitalController::class, 'verificarEspacio']);
Route::post('/crear-reserva-profesor', [ProfesorController::class, 'crearReservaProfesor']);
Route::post('/crear-reserva-profesor-automatica', [ProfesorController::class, 'crearReservaProfesorAutomatica']);
Route::get('/profesor/{run}/asignaturas', [ProfesorController::class, 'getAsignaturasProfesor']);
Route::post('/verificar-estado-espacio-reserva', [PlanoDigitalController::class, 'verificarEstadoEspacioYReserva']);
Route::post('/devolver-llaves', [PlanoDigitalController::class, 'devolverLlaves']);
Route::get('/espacios/estados', [PlanoDigitalController::class, 'estadosEspacios']);
Route::post('/obtener-info-espacio-desocupar', [PlanoDigitalController::class, 'obtenerInfoEspacioParaDesocupar']);
Route::post('/devolver-espacio', [PlanoDigitalController::class, 'devolverEspacio']);
Route::post('/registrar-asistencia-clase', [PlanoDigitalController::class, 'registrarAsistenciaClase']);

// Ruta de Keep-Alive / Heartbeat y refresco de CSRF
Route::get('/keep-alive', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'csrf_token' => csrf_token()
    ]);
});

// Ruta para verificar la programación de un usuario en un espacio específico
Route::get('/verificar-programacion/{espacio}/{usuario}', function ($espacio, $usuario) {
    try {
        if (!empty($espacio)) {
            $espacio = strtoupper(trim(str_replace(' ', '', $espacio)));
            $tenant = \App\Models\Tenant::current() ?? \App\Models\Tenant::find(tenant_id());
            if ($tenant) {
                $prefix = strtoupper($tenant->prefijo_espacios ?: $tenant->domain);
                $normalizedInput = str_replace('-', '', $espacio);
                if (strpos($normalizedInput, $prefix) === 0) {
                    $normalizedInput = substr($normalizedInput, strlen($prefix));
                }
                $espacio = $prefix . '-' . $normalizedInput;
            }
        }

        $horaActual = \Carbon\Carbon::now();
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $diaLimpio = \App\Helpers\ModulosHelper::normalizarDia($diaActual);
        $diasPosibles = array_unique([$diaActual, $diaLimpio, 'lunes', 'martes', 'miércoles', 'miercoles', 'jueves', 'viernes', 'sábado', 'sabado']);
        $horaActualStr = $horaActual->format('H:i:s');
        
        $horaConAnticipacion = $horaActual->copy()->addMinutes(30)->format('H:i:s');

        $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $usuario));
        $runSinDv = strlen($runLimpio) > 1 ? substr($runLimpio, 0, -1) : $runLimpio;

        $espacioLimpio = str_replace(['-', ' '], '', $espacio);
        $programacion = \App\Models\Planificacion_Asignatura::with(['modulo', 'asignatura'])
            ->where(function ($eq) use ($espacio, $espacioLimpio) {
                $eq->where('id_espacio', $espacio)
                   ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
            })
            ->where(function ($qPrincipal) use ($usuario, $runLimpio, $runSinDv) {
                $qPrincipal->whereHas('asignatura', function ($q) use ($usuario, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $usuario)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })->orWhereHas('horario', function ($q) use ($usuario, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $usuario)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                });
            })
            ->whereHas('modulo', function ($q) use ($diasPosibles, $horaActualStr, $horaConAnticipacion) {
                $q->whereIn('dia', $diasPosibles)
                  ->where(function ($subQ) use ($horaActualStr, $horaConAnticipacion) {
                      $subQ->where(function ($sq1) use ($horaActualStr) {
                          $sq1->where('hora_inicio', '<=', $horaActualStr)
                              ->where('hora_termino', '>=', $horaActualStr);
                      })->orWhere(function ($sq2) use ($horaActualStr, $horaConAnticipacion) {
                          $sq2->where('hora_inicio', '>', $horaActualStr)
                              ->where('hora_inicio', '<=', $horaConAnticipacion);
                      });
                  });
            })
            ->first();

        $tieneProgramacion = $programacion !== null;
        $modulosInfo = null;

        if ($tieneProgramacion && $programacion && $programacion->modulo) {
            $modulosInfo = [
                'id_modulo' => $programacion->id_modulo,
                'hora_inicio' => $programacion->modulo->hora_inicio,
                'hora_termino' => $programacion->modulo->hora_termino,
                'id_asignatura' => $programacion->id_asignatura,
                'es_temporal' => false
            ];
        } else {
            $hoyFecha = \Carbon\Carbon::today()->toDateString();
            $planColaborador = \App\Models\PlanificacionProfesorColaborador::with(['modulo', 'profesorColaborador.asignatura'])
                ->whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio])
                ->whereHas('profesorColaborador', function ($q) use ($usuario, $runLimpio, $runSinDv, $hoyFecha) {
                    $q->where('estado', 'activo')
                      ->where('fecha_inicio', '<=', $hoyFecha)
                      ->where('fecha_termino', '>=', $hoyFecha)
                      ->where(function ($subQ) use ($usuario, $runLimpio, $runSinDv) {
                          $subQ->where('run_profesor_colaborador', $usuario)
                               ->orWhere('run_profesor_colaborador', $runLimpio)
                               ->orWhere('run_profesor_colaborador', $runSinDv)
                               ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                               ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                      });
                })
                ->whereHas('modulo', function ($q) use ($diasPosibles, $horaActualStr, $horaConAnticipacion) {
                    $q->whereIn('dia', $diasPosibles)
                      ->where(function ($subQ) use ($horaActualStr, $horaConAnticipacion) {
                          $subQ->where(function ($sq1) use ($horaActualStr) {
                              $sq1->where('hora_inicio', '<=', $horaActualStr)
                                  ->where('hora_termino', '>=', $horaActualStr);
                          })->orWhere(function ($sq2) use ($horaActualStr, $horaConAnticipacion) {
                              $sq2->where('hora_inicio', '>', $horaActualStr)
                                  ->where('hora_inicio', '<=', $horaConAnticipacion);
                          });
                      });
                })
                ->first();

            if ($planColaborador && $planColaborador->modulo) {
                $tieneProgramacion = true;
                $modulosInfo = [
                    'id_modulo' => $planColaborador->id_modulo,
                    'hora_inicio' => $planColaborador->modulo->hora_inicio,
                    'hora_termino' => $planColaborador->modulo->hora_termino,
                    'id_asignatura' => $planColaborador->profesorColaborador->id_asignatura ?? null,
                    'es_temporal' => true,
                    'nombre_asignatura' => $planColaborador->profesorColaborador->nombre_asignatura_temporal ?? null,
                ];
            }
        }

        $claseYaFinalizada = false;
        $reservaFinalizadaInfo = null;

        if ($tieneProgramacion && $modulosInfo) {
            $hoyFecha = \Carbon\Carbon::today()->toDateString();
            $numModulo = \App\Helpers\ModulosHelper::getNumeroModulo($modulosInfo['id_modulo']);

            $reservaFinalizada = \App\Models\Reserva::where('fecha_reserva', $hoyFecha)
                ->where('estado', 'finalizada')
                ->where(function ($q) use ($espacio, $espacioLimpio) {
                    $q->where('id_espacio', $espacio)
                      ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                })
                ->where(function ($q) use ($usuario, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $usuario)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
                ->where(function ($q) use ($modulosInfo, $numModulo) {
                    if (!empty($modulosInfo['id_asignatura'])) {
                        $q->where('id_asignatura', $modulosInfo['id_asignatura']);
                    }
                    if ($numModulo !== null) {
                        $q->orWhere(function ($sub) use ($numModulo) {
                            $sub->where('modulo_inicio', '<=', $numModulo)
                                ->where('modulo_fin', '>=', $numModulo);
                        });
                    }
                })
                ->latest('updated_at')
                ->first();

            if ($reservaFinalizada) {
                $claseYaFinalizada = true;
                $tieneProgramacion = false; // Detener auto-inicio de clase ya concluida
                $reservaFinalizadaInfo = [
                    'id_reserva' => $reservaFinalizada->id_reserva,
                    'hora_salida' => $reservaFinalizada->hora_salida,
                    'anticipada' => (bool)$reservaFinalizada->clase_finalizada_anticipadamente,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'tieneProgramacion' => $tieneProgramacion,
            'tiene_clase' => $tieneProgramacion,
            'clase_ya_finalizada' => $claseYaFinalizada,
            'reserva_finalizada_info' => $reservaFinalizadaInfo,
            'modulosInfo' => $modulosInfo,
            'debug' => [
                'dia_actual' => $diaActual,
                'hora_actual' => $horaActualStr,
                'espacio_id' => $espacio,
                'usuario_original' => $usuario,
                'usuario_limpio' => $runLimpio
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Error en verificar-programacion: ' . $e->getMessage(), [
            'espacio' => $espacio,
            'usuario' => $usuario,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'tieneProgramacion' => false,
            'tiene_clase' => false,
            'message' => 'Error al verificar la programación: ' . $e->getMessage()
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Rutas de Solicitantes
|--------------------------------------------------------------------------
*/
Route::get('/verificar-solicitante/{run}', [SolicitanteController::class, 'verificarExistencia']);
Route::post('/registrar-solicitante', [SolicitanteController::class, 'registrarSolicitante']);
Route::post('/crear-reserva-solicitante', [SolicitanteController::class, 'crearReservaSolicitante']);

/*
|--------------------------------------------------------------------------
| Rutas del Panel de Administración (consumidas por admin-panel.js)
|--------------------------------------------------------------------------
*/
Route::get('/buscar-usuario/{run}', [AdminPanelController::class, 'buscarUsuario']);
Route::get('/espacios/disponibles', [AdminPanelController::class, 'getEspaciosDisponibles']);
Route::get('/admin/espacios', [AdminPanelController::class, 'getEspacios']);
Route::put('/admin/espacio/{codigo}/estado', [AdminPanelController::class, 'cambiarEstadoEspacio']);
Route::post('/admin/crear-reserva', [AdminPanelController::class, 'crearReserva']);
Route::get('/admin/reservas', [AdminPanelController::class, 'getReservas']);
Route::put('/admin/reserva/{id}/estado', [AdminPanelController::class, 'cambiarEstadoReserva']);
Route::post('/admin/vaciar-reservas', [AdminPanelController::class, 'vaciarReservas']);

/*
|--------------------------------------------------------------------------
| Programación Semanal y Asistencia por Espacio
|--------------------------------------------------------------------------
*/
Route::get('/programacion-semanal/{id_espacio}', [ProgramacionSemanalController::class, 'obtenerProgramacionSemanal']);
Route::post('/asistencia', [ProgramacionSemanalController::class, 'registrarAsistencia']);
Route::get('/reservas/activa/{id_espacio}', [ProgramacionSemanalController::class, 'obtenerReservaActiva']);

/*
|--------------------------------------------------------------------------
| Catálogo de Espacios y Resúmenes
|--------------------------------------------------------------------------
*/
Route::get('/espacios', [EspacioApiController::class, 'listarEspacios']);
Route::get('/tipos-espacios', [EspacioApiController::class, 'listarTiposEspacios']);
Route::get('/espacios/resumen', [EspacioApiController::class, 'resumenEspacios']);

/*
|--------------------------------------------------------------------------
| Salas de Estudio y Vetos
|--------------------------------------------------------------------------
*/
Route::post('/sala-estudio/registrar-acceso', [SalaEstudioController::class, 'registrarAcceso']);
Route::get('/sala-estudio/{id_espacio}/alumnos-registrados', [SalaEstudioController::class, 'obtenerAlumnosRegistrados']);
Route::post('/sala-estudio/vetar-individual', [SalaEstudioController::class, 'vetarIndividual']);
Route::post('/sala-estudio/vetar-grupo', [SalaEstudioController::class, 'vetarGrupo']);
Route::put('/sala-estudio/veto/{id}/liberar', [SalaEstudioController::class, 'liberarVeto']);
Route::put('/sala-estudio/veto/{id}', [SalaEstudioController::class, 'actualizarVeto']);
Route::get('/sala-estudio/vetos', [SalaEstudioController::class, 'listarVetados']);
Route::get('/sala-estudio/vetos/export', [SalaEstudioController::class, 'exportarVetos']);

/*
|--------------------------------------------------------------------------
| Módulos y Disponibilidad de Espacios
|--------------------------------------------------------------------------
*/
Route::get('/modulos', function () {
    $modulos = \App\Models\Modulo::all()
        ->sort(function ($a, $b) {
            $dias = ['LU' => 1, 'MA' => 2, 'MI' => 3, 'JU' => 4, 'VI' => 5, 'SA' => 6, 'DO' => 7];
            $partsA = explode('.', $a->id_modulo);
            $partsB = explode('.', $b->id_modulo);
            $diaA = $partsA[0];
            $numA = isset($partsA[1]) ? (int)$partsA[1] : 0;
            $diaB = $partsB[0];
            $numB = isset($partsB[1]) ? (int)$partsB[1] : 0;
            if ($diaA !== $diaB) {
                return ($dias[$diaA] ?? 99) <=> ($dias[$diaB] ?? 99);
            }
            return $numA <=> $numB;
        })
        ->map(function ($modulo) {
            return [
                'id_modulo' => $modulo->id_modulo,
                'hora_inicio' => substr($modulo->hora_inicio, 0, 5),
                'hora_termino' => substr($modulo->hora_termino, 0, 5)
            ];
        })
        ->values()
        ->toArray();

    return response()->json($modulos);
});

Route::get('/espacios-disponibles/{fecha}/{moduloInicio}/{moduloFin}', function ($fecha, $moduloInicio, $moduloFin) {
    try {
        $espacios = \App\Models\Espacio::whereNotIn('estado', ['Mantención', 'Mantenimiento', 'Inactivo', 'Deshabilitado', 'Baja'])
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala de estudio%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala estudio%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala de reuniones%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%sala reuniones%'")
            ->whereRaw("LOWER(tipo_espacio) NOT LIKE '%oficina%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%estudio%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%reunion%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%oficina%'")
            ->whereRaw("LOWER(nombre_espacio) NOT LIKE '%bodega%'")
            ->select('id_espacio', 'nombre_espacio', 'tipo_espacio')
            ->orderBy('nombre_espacio')
            ->get();

        $fechaParsed = \Carbon\Carbon::parse($fecha);
        $nombreDia = strtolower($fechaParsed->format('l'));
        
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

        // Extraer los números de inicio y fin (ej: de "MI.7" -> 7, o de "7" -> 7)
        $inicioNum = (int) (str_contains((string)$moduloInicio, '.') ? explode('.', (string)$moduloInicio)[1] : preg_replace('/[^0-9]/', '', (string)$moduloInicio));
        $finNum = (int) (str_contains((string)$moduloFin, '.') ? explode('.', (string)$moduloFin)[1] : preg_replace('/[^0-9]/', '', (string)$moduloFin));

        if ($inicioNum <= 0) {
            $inicioNum = 1;
        }
        if ($finNum < $inicioNum) {
            $finNum = $inicioNum;
        }

        $modulosAVerificar = range($inicioNum, $finNum);
        $modulosIds = array_map(function($num) use ($prefijoDia) {
            return "{$prefijoDia}.{$num}";
        }, $modulosAVerificar);

        $primerModuloObj = \App\Models\Modulo::withoutGlobalScopes()->where('id_modulo', $modulosIds[0])->first()
            ?? \App\Models\Modulo::find($modulosIds[0]);
        $ultimoModuloObj = \App\Models\Modulo::withoutGlobalScopes()->where('id_modulo', end($modulosIds))->first()
            ?? \App\Models\Modulo::find(end($modulosIds));

        // Horarios base como fallback en caso de que algún módulo no esté en BD
        $modulosBase = [
            1 => ['inicio' => '08:10', 'termino' => '09:00'],
            2 => ['inicio' => '09:10', 'termino' => '10:00'],
            3 => ['inicio' => '10:10', 'termino' => '11:00'],
            4 => ['inicio' => '11:10', 'termino' => '12:00'],
            5 => ['inicio' => '12:10', 'termino' => '13:00'],
            6 => ['inicio' => '13:10', 'termino' => '14:00'],
            7 => ['inicio' => '14:10', 'termino' => '15:00'],
            8 => ['inicio' => '15:10', 'termino' => '16:00'],
            9 => ['inicio' => '16:10', 'termino' => '17:00'],
            10 => ['inicio' => '17:10', 'termino' => '18:00'],
            11 => ['inicio' => '18:10', 'termino' => '19:00'],
            12 => ['inicio' => '19:10', 'termino' => '20:00'],
            13 => ['inicio' => '20:10', 'termino' => '21:00'],
            14 => ['inicio' => '21:10', 'termino' => '22:00'],
            15 => ['inicio' => '22:10', 'termino' => '23:00'],
        ];

        $horaInicio = $primerModuloObj ? substr($primerModuloObj->hora_inicio, 0, 5) : ($modulosBase[$inicioNum]['inicio'] ?? '08:10');
        $horaFin = $ultimoModuloObj ? substr($ultimoModuloObj->hora_termino, 0, 5) : ($modulosBase[$finNum]['termino'] ?? '20:00');

        $espaciosDisponibles = $espacios->filter(function ($espacio) use ($fecha, $modulosIds, $modulosAVerificar, $prefijoDia) {
            // 1. Verificar planificaciones normales
            foreach ($modulosIds as $moduloId) {
                $planificadoQuery = \App\Models\Planificacion_Asignatura::where('id_espacio', $espacio->id_espacio)
                    ->where('id_modulo', $moduloId);

                if (\App\Models\Planificacion_Asignatura::tieneColumnaVigencia()) {
                    $planificadoQuery = $planificadoQuery->vigentesEn($fecha);
                }

                if ($planificadoQuery->exists()) {
                    return false;
                }
            }

            // 2. Verificar planificaciones de profesores colaboradores
            try {
                if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('planificaciones_profesores_colaboradores')) {
                    foreach ($modulosIds as $moduloId) {
                        $planificadoColab = \Illuminate\Support\Facades\DB::connection('tenant')->table('planificaciones_profesores_colaboradores as ppc')
                            ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                            ->where('ppc.id_modulo', $moduloId)
                            ->where('ppc.id_espacio', $espacio->id_espacio)
                            ->where('pc.estado', 'activo')
                            ->where('pc.fecha_inicio', '<=', $fecha)
                            ->where('pc.fecha_termino', '>=', $fecha)
                            ->exists();

                        if ($planificadoColab) {
                            return false;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Si la tabla no existe o falla la conexión, continuar
            }

            // 3. Verificar reservas confirmadas / activas / programadas
            foreach ($modulosAVerificar as $numModulo) {
                $modInicio = $modulosBase[$numModulo]['inicio'] ?? '08:10';
                $modFin = $modulosBase[$numModulo]['termino'] ?? '20:00';
                $modInicioTime = strlen($modInicio) === 5 ? "{$modInicio}:00" : $modInicio;
                $modFinTime = strlen($modFin) === 5 ? "{$modFin}:00" : $modFin;

                $reservado = \App\Models\Reserva::where('id_espacio', $espacio->id_espacio)
                    ->whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['confirmada', 'en_curso', 'activa', 'programada'])
                    ->where(function ($query) use ($numModulo, $modInicioTime, $modFinTime) {
                        // Caso A: Tiene modulo_inicio / modulo_fin definidos
                        $query->where(function ($sub) use ($numModulo) {
                            $sub->whereNotNull('modulo_inicio')
                                ->where('modulo_inicio', '<=', $numModulo)
                                ->where(function ($sub2) use ($numModulo) {
                                    $sub2->whereNull('modulo_fin')
                                         ->where('modulo_inicio', '=', $numModulo)
                                         ->orWhere('modulo_fin', '>=', $numModulo);
                                });
                        })
                        // Caso B: Si modulo_inicio es null, verificar traslape por horario (hora y hora_salida)
                        ->orWhere(function ($sub) use ($modInicioTime, $modFinTime) {
                            $sub->whereNull('modulo_inicio')
                                ->whereNotNull('hora')
                                ->where('hora', '<', $modFinTime)
                                ->where(function ($sub2) use ($modInicioTime) {
                                    $sub2->whereNull('hora_salida')
                                         ->orWhere('hora_salida', '>', $modInicioTime);
                                });
                        });
                    })
                    ->exists();

                if ($reservado) {
                    return false;
                }
            }

            return true;
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
