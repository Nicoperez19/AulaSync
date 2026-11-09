<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Carrera;
use App\Models\Reserva;
use App\Models\Asignatura;
use App\Models\AreaAcademica;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Espacio;
use App\Traits\RunNormalizer;
use App\Helpers\SemesterHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProfesorController extends Controller
{
    use RunNormalizer;

    /**
     * Crear reserva para profesor
     */
    public function index()
    {
        try {
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();

            return view('profesores.index', compact('universidades', 'facultades', 'carreras', 'areasAcademicas'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los profesores.'])->withInput();
        }
    }
    /**
     * Crear reserva para profesor
     */
    public function crearReservaProfesor(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'run_profesor' => 'required',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $this->normalizeRun($request->input('run_profesor'));
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $request->input('run_profesor')));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $runLimpio;
            $idEspacio = $request->input('id_espacio');

            // Normalizar el ID del espacio usando la lógica del tenant actual
            $idEspacio = $this->normalizeEspacioId($idEspacio);

            // Verificar que el espacio existe (soportando alias y con/sin prefijo de sede)
            $espacioLimpio = str_replace(['-', ' '], '', $idEspacio);
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);
            $espacio = Espacio::withoutGlobalScopes()
                ->where(function ($q) use ($idEspacio, $espaciosEquiv, $espacioLimpio) {
                    $q->where('id_espacio', $idEspacio)
                      ->orWhereIn('id_espacio', $espaciosEquiv)
                      ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                })
                ->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el profesor existe (soportando RUN con o sin DV)
            $profesor = Profesor::withoutGlobalScopes()
                ->where(function ($q) use ($runProfesor, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $runProfesor)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
                ->first();
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Verificar que el espacio esté disponible (o auto-finalizar sesión previa del mismo docente / sesión expirada)
            if ($espacio->estado !== 'Disponible') {
                $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($espacio->id_espacio);
                $reservaActivaEnEspacio = Reserva::whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->latest('updated_at')
                    ->first();

                if (!$reservaActivaEnEspacio) {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                } else {
                    $runProfLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $profesor->run_profesor));
                    $runOcupanteLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $reservaActivaEnEspacio->run_profesor ?? ''));

                    $esMismoProfesor = ($runOcupanteLimpio !== '' && ($runProfLimpio === $runOcupanteLimpio || $runSinDv === $runOcupanteLimpio));
                    $horaActualStr = now()->format('H:i:s');
                    $fechaResStr = $reservaActivaEnEspacio->fecha_reserva instanceof \Carbon\Carbon
                        ? $reservaActivaEnEspacio->fecha_reserva->toDateString()
                        : substr((string) $reservaActivaEnEspacio->fecha_reserva, 0, 10);
                    $esReservaAntigua = ($fechaResStr && $fechaResStr < now()->toDateString())
                        || ($reservaActivaEnEspacio->hora_salida && $reservaActivaEnEspacio->hora_salida <= $horaActualStr);

                    if ($esMismoProfesor || $esReservaAntigua) {
                        $reservaActivaEnEspacio->estado = 'finalizada';
                        if (!$reservaActivaEnEspacio->hora_salida || $esReservaAntigua) {
                            $reservaActivaEnEspacio->hora_salida = $horaActualStr;
                        }
                        $reservaActivaEnEspacio->observaciones = trim(($reservaActivaEnEspacio->observaciones ?? '') . ' | Auto-finalizada al iniciar siguiente clase programada');
                        $reservaActivaEnEspacio->save();

                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    } else {
                        return response()->json([
                            'success' => false,
                            'mensaje' => 'El espacio no está disponible'
                        ], 400);
                    }
                }
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 7 * 60 + 30; // 07:30 (pero el mensaje dirá 08:10)
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $profesor->run_profesor)
                ->where('estado', 'activa')
                ->first();

            if ($reservaExistente) {
                $fechaResExistente = $reservaExistente->fecha_reserva instanceof \Carbon\Carbon
                    ? $reservaExistente->fecha_reserva->toDateString()
                    : substr((string) $reservaExistente->fecha_reserva, 0, 10);
                $esExpirada = ($fechaResExistente && $fechaResExistente < $fechaActual)
                    || ($reservaExistente->hora_salida && $reservaExistente->hora_salida <= $horaActual);

                if ($esExpirada || $reservaExistente->id_espacio === $espacio->id_espacio) {
                    $reservaExistente->estado = 'finalizada';
                    if (!$reservaExistente->hora_salida || $esExpirada) {
                        $reservaExistente->hora_salida = $horaActual;
                    }
                    $reservaExistente->observaciones = trim(($reservaExistente->observaciones ?? '') . ' | Auto-finalizada al registrar nueva reserva en ' . $espacio->id_espacio);
                    $reservaExistente->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                    ], 400);
                }
            }

            // Encontrar el módulo actual para asignar como inicio/fin (incluyendo soporte para breaks y modulo_solicitado)
            $diaSemanaStr = strtolower(\Carbon\Carbon::parse($fechaActual)->locale('es')->isoFormat('dddd'));
            $diaNormalizado = \App\Helpers\ModulosHelper::normalizarDia($diaSemanaStr);
            $moduloActual = \App\Helpers\ModulosHelper::obtenerModuloActual($horaActual, $diaNormalizado);
            $horarios = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];

            if (!$moduloActual && $request->filled('modulo_solicitado')) {
                $modSol = (int) $request->input('modulo_solicitado');
                if (isset($horarios[$modSol])) {
                    $moduloActual = $modSol;
                }
            }
            if (!$moduloActual && !empty($horarios)) {
                foreach ($horarios as $modNum => $hMod) {
                    if ($horaActual < $hMod['inicio']) {
                        $moduloActual = (int) $modNum;
                        break;
                    }
                }
            }

            // Verificar si el profesor tiene una recuperación de clase reagendada para este horario/espacio
            $infoRecEspontanea = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada(
                $espacio->id_espacio,
                $profesor->run_profesor,
                $fechaActual,
                $horaActual,
                30
            );

            // Crear la reserva (como recuperación si aplica, o espontánea)
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->run_profesor = $profesor->run_profesor;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->id_asignatura = $infoRecEspontanea['id_asignatura'] ?? null;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->hora = $horaActual;
            
            if ($infoRecEspontanea) {
                $reserva->modulo_inicio = $infoRecEspontanea['modulo_inicio'];
                $reserva->modulo_fin = $infoRecEspontanea['modulo_fin'];
                $reserva->hora_salida = $infoRecEspontanea['hora_termino'];
                $reserva->modulos = $infoRecEspontanea['cantidad_modulos'];
                $reserva->tipo_reserva = 'recuperacion';
            } elseif ($moduloActual) {
                $reserva->modulo_inicio = $moduloActual;
                $cantModsSol = max(1, (int) $request->input('modulos', 1)); $modFinCalc = $moduloActual; for ($mS = 1; $mS < $cantModsSol; $mS++) { if (isset($horarios[$moduloActual + $mS])) { $modFinCalc = $moduloActual + $mS; } } $reserva->modulo_fin = $modFinCalc;
                $reserva->hora_salida = $horarios[$modFinCalc]['fin'] ?? $horarios[$moduloActual]['fin'];
                $reserva->modulos = ($modFinCalc - $moduloActual) + 1;
                $reserva->tipo_reserva = 'espontanea';
            } else {
                $reserva->hora_salida = \Carbon\Carbon::parse($horaActual)->addHour()->format('H:i:s');
                $reserva->tipo_reserva = 'espontanea';
            }
            
            $reserva->estado = 'activa';
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Limpiar registros incorrectos de clases no realizadas (mover a atrasos)
            \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                $espacio->id_espacio,
                $fechaActual,
                $horaActual,
                $profesor->run_profesor
            );

            // Enviar comprobante de reserva por correo
            app(\App\Services\ReservaService::class)->enviarCorreoReserva($reserva, $profesor->email ?? null);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente para el profesor',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor' => $profesor->name,
                    'run_profesor' => $profesor->run_profesor,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $horaActual
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva de profesor: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva automáticamente para profesor con clase programada
     * Usa directamente los módulos de la programación del profesor
     */
    public function crearReservaProfesorAutomatica(Request $request)
    {
        try {
            $this->establecerContextoTenant();

            $request->validate([
                'run_profesor' => 'required',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $this->normalizeRun($request->input('run_profesor'));
            $idEspacio = $request->input('id_espacio');

            // Normalizar el ID del espacio usando la lógica del tenant actual
            $idEspacio = $this->normalizeEspacioId($idEspacio);

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');

            // Normalizar el RUN para búsqueda (preservando K/k)
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $request->input('run_profesor')));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $runLimpio;

            // Verificar que el espacio existe (soportando alias y con/sin prefijo de sede)
            $espacioLimpio = str_replace(['-', ' '], '', $idEspacio);
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);
            $espacio = Espacio::withoutGlobalScopes()
                ->where(function ($q) use ($idEspacio, $espaciosEquiv, $espacioLimpio) {
                    $q->where('id_espacio', $idEspacio)
                      ->orWhereIn('id_espacio', $espaciosEquiv)
                      ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                })
                ->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el profesor existe (soportando RUN con o sin DV)
            $profesor = Profesor::withoutGlobalScopes()
                ->where(function ($q) use ($runProfesor, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $runProfesor)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
                ->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Verificar que el espacio esté disponible (o auto-finalizar sesión previa del mismo docente / sesión expirada)
            if ($espacio->estado !== 'Disponible') {
                $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($espacio->id_espacio);
                $reservaActivaEnEspacio = Reserva::whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->latest('updated_at')
                    ->first();

                if (!$reservaActivaEnEspacio) {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                } else {
                    $runProfLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $profesor->run_profesor));
                    $runOcupanteLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $reservaActivaEnEspacio->run_profesor ?? ''));

                    $esMismoProfesor = ($runOcupanteLimpio !== '' && ($runProfLimpio === $runOcupanteLimpio || $runSinDv === $runOcupanteLimpio));
                    $horaActualStr = now()->format('H:i:s');
                    $fechaResStr = $reservaActivaEnEspacio->fecha_reserva instanceof \Carbon\Carbon
                        ? $reservaActivaEnEspacio->fecha_reserva->toDateString()
                        : substr((string) $reservaActivaEnEspacio->fecha_reserva, 0, 10);
                    $esReservaAntigua = ($fechaResStr && $fechaResStr < now()->toDateString())
                        || ($reservaActivaEnEspacio->hora_salida && $reservaActivaEnEspacio->hora_salida <= $horaActualStr);

                    if ($esMismoProfesor || $esReservaAntigua) {
                        $reservaActivaEnEspacio->estado = 'finalizada';
                        if (!$reservaActivaEnEspacio->hora_salida || $esReservaAntigua) {
                            $reservaActivaEnEspacio->hora_salida = $horaActualStr;
                        }
                        $reservaActivaEnEspacio->observaciones = trim(($reservaActivaEnEspacio->observaciones ?? '') . ' | Auto-finalizada al iniciar siguiente clase programada');
                        $reservaActivaEnEspacio->save();

                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    } else {
                        return response()->json([
                            'success' => false,
                            'mensaje' => 'El espacio no está disponible'
                        ], 400);
                    }
                }
            }

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 7 * 60 + 30; // 07:30 (pero el mensaje dirá 08:10)
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $profesor->run_profesor)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            // Si tiene una reserva activa en otro espacio cuya hora de término ya pasó, auto-finalizarla
            if ($reservaExistente && $reservaExistente->id_espacio != $espacio->id_espacio) {
                if ($reservaExistente->hora_salida && $reservaExistente->hora_salida < now()->format('H:i:s')) {
                    $reservaExistente->estado = 'finalizada';
                    $reservaExistente->observaciones = ($reservaExistente->observaciones ?? '') . ' | Auto-finalizada al iniciar clase en ' . $espacio->nombre_espacio;
                    $reservaExistente->save();
                    $reservaExistente = null;
                } else {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                    ], 400);
                }
            }

            // Obtener la programación del profesor para este espacio (usando Eloquent para respetar la conexión 'tenant')
            $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
            $diaNormalizado = \App\Helpers\ModulosHelper::normalizarDia($diaActual);
            $diasPosibles = array_values(array_unique([$diaActual, $diaNormalizado]));
            $horaConAnticipacion = now()->copy()->addMinutes(30)->format('H:i:s');
            $espacioLimpio = str_replace(['-', ' '], '', $espacio->id_espacio);
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($espacio->id_espacio);

            $reservaReactivable = null;

            // Helper para verificar si un bloque/asignatura específico ya fue finalizado hoy
            $verificarReservaFinalizadaProf = function ($idAsig, $idMod, $moduloObj = null) use ($fechaActual, $horaActual, $espacio, $espaciosEquiv, $espacioLimpio, $profesor, $runProfesor, $runLimpio, $runSinDv, &$reservaReactivable) {
                $numModulo = \App\Helpers\ModulosHelper::getNumeroModulo($idMod);
                $res = Reserva::where('fecha_reserva', $fechaActual)
                    ->where('estado', 'finalizada')
                    ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                        $eq->where('id_espacio', $espacio->id_espacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                    })
                    ->where(function ($q) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                        $q->where('run_profesor', $profesor->run_profesor)
                          ->orWhere('run_profesor', $runProfesor)
                          ->orWhere('run_profesor', $runLimpio)
                          ->orWhere('run_profesor', $runSinDv)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })
                    ->where(function ($q) use ($idAsig, $numModulo) {
                        if ($numModulo !== null) {
                            $q->where('modulo_inicio', '<=', $numModulo)
                              ->where('modulo_fin', '>=', $numModulo);
                        }
                        if (!empty($idAsig)) {
                            $q->where('id_asignatura', $idAsig);
                        }
                    })
                    ->latest('updated_at')
                    ->first();

                if ($res && $moduloObj) {
                    $horaInicioMod = $moduloObj->hora_inicio ?? null;
                    $horaTerminoMod = $moduloObj->hora_termino ?? null;
                    $cerradaAntesDeIniciar = ($horaInicioMod && $res->hora_salida && $res->hora_salida <= $horaInicioMod);
                    $duracionCortaEnCurso = false;
                    if ($horaTerminoMod && $horaActual < $horaTerminoMod && $res->hora && $res->hora_salida) {
                        $duracionSeg = abs(strtotime($res->hora_salida) - strtotime($res->hora));
                        if ($duracionSeg < 300) {
                            $duracionCortaEnCurso = true;
                        }
                    }
                    if ($cerradaAntesDeIniciar || $duracionCortaEnCurso) {
                        if ($numModulo !== null && (int) $res->modulo_inicio === (int) $numModulo) {
                            $reservaReactivable = $res;
                        }
                        return null;
                    }
                }

                return $res;
            };

            $candidatosRegulares = \App\Models\Planificacion_Asignatura::with(['modulo', 'asignatura'])
                ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                    $eq->where('id_espacio', $espacio->id_espacio)
                       ->orWhereIn('id_espacio', $espaciosEquiv)
                       ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                })
                ->where(function ($qPrincipal) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                    $qPrincipal->whereHas('asignatura', function ($q) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                        $q->where('run_profesor', $profesor->run_profesor)
                          ->orWhere('run_profesor', $runProfesor)
                          ->orWhere('run_profesor', $runLimpio)
                          ->orWhere('run_profesor', $runSinDv)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                          ->orWhere('run_profesor_reemplazo', $profesor->run_profesor)
                          ->orWhere('run_profesor_reemplazo', $runProfesor)
                          ->orWhere('run_profesor_reemplazo', $runLimpio)
                          ->orWhere('run_profesor_reemplazo', $runSinDv)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })->orWhereHas('horario', function ($q) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                        $q->where('run_profesor', $profesor->run_profesor)
                          ->orWhere('run_profesor', $runProfesor)
                          ->orWhere('run_profesor', $runLimpio)
                          ->orWhere('run_profesor', $runSinDv)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    });
                })
                ->whereHas('modulo', function ($q) use ($diasPosibles, $horaActual, $horaConAnticipacion) {
                    $q->whereIn('dia', $diasPosibles)
                      ->where(function ($subQ) use ($horaActual, $horaConAnticipacion) {
                          $subQ->where(function ($sq1) use ($horaActual) {
                              $sq1->where('hora_inicio', '<=', $horaActual)
                                  ->where('hora_termino', '>=', $horaActual);
                          })->orWhere(function ($sq2) use ($horaActual, $horaConAnticipacion) {
                              $sq2->where('hora_inicio', '>', $horaActual)
                                  ->where('hora_inicio', '<=', $horaConAnticipacion);
                          });
                      });
                })
                ->get();

            $programacion = null;
            $candidatoRegFinalizado = null;
            $reservaRegFinalizada = null;
            $reservaFinalizadaHoy = null;

            foreach ($candidatosRegulares as $candReg) {
                $resFin = $verificarReservaFinalizadaProf($candReg->id_asignatura, $candReg->id_modulo, $candReg->modulo);
                if (!$resFin) {
                    if (!$programacion) {
                        $programacion = $candReg;
                    } else {
                        $esActivoActual = $candReg->modulo && $candReg->modulo->hora_inicio <= $horaActual && $candReg->modulo->hora_termino >= $horaActual;
                        $esActivoPrevio = $programacion->modulo && $programacion->modulo->hora_inicio <= $horaActual && $programacion->modulo->hora_termino >= $horaActual;
                        if ($esActivoActual && !$esActivoPrevio) {
                            $programacion = $candReg;
                        }
                    }
                } else {
                    if (!$candidatoRegFinalizado) {
                        $candidatoRegFinalizado = $candReg;
                        $reservaRegFinalizada = $resFin;
                    }
                }
            }

            if (!$programacion && $candidatoRegFinalizado) {
                $programacion = $candidatoRegFinalizado;
                $reservaFinalizadaHoy = $reservaRegFinalizada;
            }

            $esTemporal = false;
            $planColaborador = null;

            if (!$programacion) {
                // Buscar si tiene una clase temporal activa (PlanificacionProfesorColaborador)
                $hoyFecha = $fechaActual;
                $candidatosColaborador = \App\Models\PlanificacionProfesorColaborador::with(['modulo', 'profesorColaborador.asignatura'])
                    ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                        $eq->where('id_espacio', $espacio->id_espacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                    })
                    ->whereHas('profesorColaborador', function ($q) use ($profesor, $runProfesor, $runLimpio, $runSinDv, $hoyFecha) {
                        $q->where('estado', 'activo')
                          ->where('fecha_inicio', '<=', $hoyFecha)
                          ->where('fecha_termino', '>=', $hoyFecha)
                          ->where(function ($subQ) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                              $subQ->where('run_profesor_colaborador', $profesor->run_profesor)
                                   ->orWhere('run_profesor_colaborador', $runProfesor)
                                   ->orWhere('run_profesor_colaborador', $runLimpio)
                                   ->orWhere('run_profesor_colaborador', $runSinDv)
                                   ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                                   ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                          });
                    })
                    ->whereHas('modulo', function ($q) use ($diasPosibles, $horaActual, $horaConAnticipacion) {
                        $q->whereIn('dia', $diasPosibles)
                          ->where(function ($subQ) use ($horaActual, $horaConAnticipacion) {
                              $subQ->where(function ($sq1) use ($horaActual) {
                                  $sq1->where('hora_inicio', '<=', $horaActual)
                                      ->where('hora_termino', '>=', $horaActual);
                              })->orWhere(function ($sq2) use ($horaActual, $horaConAnticipacion) {
                                  $sq2->where('hora_inicio', '>', $horaActual)
                                      ->where('hora_inicio', '<=', $horaConAnticipacion);
                              });
                          });
                    })
                    ->get();

                $candidatoColabFinalizado = null;
                $reservaColabFinalizada = null;

                foreach ($candidatosColaborador as $candColab) {
                    $resFinColab = $verificarReservaFinalizadaProf(
                        $candColab->profesorColaborador->id_asignatura ?? null,
                        $candColab->id_modulo,
                        $candColab->modulo
                    );
                    if (!$resFinColab) {
                        if (!$planColaborador) {
                            $planColaborador = $candColab;
                        }
                    } else {
                        if (!$candidatoColabFinalizado) {
                            $candidatoColabFinalizado = $candColab;
                            $reservaColabFinalizada = $resFinColab;
                        }
                    }
                }

                if (!$planColaborador && $candidatoColabFinalizado) {
                    $planColaborador = $candidatoColabFinalizado;
                    $reservaFinalizadaHoy = $reservaColabFinalizada;
                }

                if ($planColaborador) {
                    $esTemporal = true;
                }
            }

            // 1.c Fallback: Profesor colaborador activo asociado a la asignatura de una Planificacion_Asignatura en este espacio
            if (!$programacion && !$planColaborador) {
                $hoyFecha = $fechaActual;
                $asigIdsColab = \App\Models\ProfesorColaborador::where('estado', 'activo')
                    ->where('fecha_inicio', '<=', $hoyFecha)
                    ->where('fecha_termino', '>=', $hoyFecha)
                    ->whereNotNull('id_asignatura')
                    ->where(function ($subQ) use ($profesor, $runProfesor, $runLimpio, $runSinDv) {
                        $subQ->where('run_profesor_colaborador', $profesor->run_profesor)
                             ->orWhere('run_profesor_colaborador', $runProfesor)
                             ->orWhere('run_profesor_colaborador', $runLimpio)
                             ->orWhere('run_profesor_colaborador', $runSinDv)
                             ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                             ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })
                    ->pluck('id_asignatura')
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($asigIdsColab)) {
                    $candidatosAsigColab = \App\Models\Planificacion_Asignatura::with(['modulo', 'asignatura'])
                        ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                            $eq->where('id_espacio', $espacio->id_espacio)
                               ->orWhereIn('id_espacio', $espaciosEquiv)
                               ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                        })
                        ->whereIn('id_asignatura', $asigIdsColab)
                        ->whereHas('modulo', function ($q) use ($diasPosibles, $horaActual, $horaConAnticipacion) {
                            $q->whereIn('dia', $diasPosibles)
                              ->where(function ($subQ) use ($horaActual, $horaConAnticipacion) {
                                  $subQ->where(function ($sq1) use ($horaActual) {
                                      $sq1->where('hora_inicio', '<=', $horaActual)
                                          ->where('hora_termino', '>=', $horaActual);
                                  })->orWhere(function ($sq2) use ($horaActual, $horaConAnticipacion) {
                                      $sq2->where('hora_inicio', '>', $horaActual)
                                          ->where('hora_inicio', '<=', $horaConAnticipacion);
                                  });
                              });
                        })
                        ->get();

                    foreach ($candidatosAsigColab as $candAC) {
                        $resFinAC = $verificarReservaFinalizadaProf($candAC->id_asignatura, $candAC->id_modulo, $candAC->modulo);
                        if (!$resFinAC) {
                            if (!$programacion) {
                                $programacion = $candAC;
                            }
                        } elseif (!$candidatoRegFinalizado) {
                            $candidatoRegFinalizado = $candAC;
                            $reservaRegFinalizada = $resFinAC;
                        }
                    }

                    if (!$programacion && $candidatoRegFinalizado && !$reservaFinalizadaHoy) {
                        $programacion = $candidatoRegFinalizado;
                        $reservaFinalizadaHoy = $reservaRegFinalizada;
                    }
                }
            }

            $infoRecuperacion = null;
            if (!$programacion && !$planColaborador) {
                $infoRecuperacion = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada(
                    $espacio->id_espacio,
                    $profesor->run_profesor,
                    $fechaActual,
                    $horaActual,
                    60
                );
                if ($infoRecuperacion) {
                    $resFinRec = $verificarReservaFinalizadaProf(
                        $infoRecuperacion['id_asignatura'] ?? null,
                        $infoRecuperacion['id_modulo'] ?? null
                    );
                    if ($resFinRec) {
                        $reservaFinalizadaHoy = $resFinRec;
                    }
                }
            }

            if (!$programacion && !$planColaborador && !$infoRecuperacion) {
                // Si no encuentra programación regular, temporal ni recuperación, retornar error
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró programación para este profesor en este espacio'
                ], 400);
            }

            // Si el candidato seleccionado ya fue finalizado hoy, bloquear re-apertura
            if ($reservaFinalizadaHoy) {
                return response()->json([
                    'success' => false,
                    'clase_ya_finalizada' => true,
                    'mensaje' => 'Esta clase ya fue finalizada el día de hoy' . ($reservaFinalizadaHoy->hora_salida ? ' a las ' . substr($reservaFinalizadaHoy->hora_salida, 0, 5) : '') . '. No es posible iniciarla nuevamente en el mismo bloque horario.'
                ], 400);
            }

            // Encontrar bloques consecutivos de planificación
            $periodo = \App\Helpers\SemesterHelper::getCurrentPeriod();
            $idAsignatura = null;
            $moduloFinalObj = null;
            $moduloInicioObj = null;
            $nombreAsignaturaResp = '';

            if (!$esTemporal && $programacion) {
                $planificacionesMismoBloque = \App\Models\Planificacion_Asignatura::with('modulo')
                    ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                        $eq->where('id_espacio', $espacio->id_espacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                    })
                    ->where('id_asignatura', $programacion->id_asignatura)
                    ->where(function ($q) use ($periodo, $programacion) {
                        $q->where('id_horario', $programacion->id_horario)
                          ->orWhereHas('horario', function ($hq) use ($periodo) {
                              $hq->where('periodo', $periodo);
                          });
                    })
                    ->whereHas('modulo', function ($q) use ($diasPosibles) {
                        $q->whereIn('dia', $diasPosibles);
                    })
                    ->get();

                $numModuloActual = \App\Helpers\ModulosHelper::getNumeroModulo($programacion->id_modulo);
                $moduloFin = $numModuloActual;
                
                $modulosAsociados = [];
                foreach ($planificacionesMismoBloque as $planItem) {
                    if ($planItem->modulo) {
                        $num = \App\Helpers\ModulosHelper::getNumeroModulo($planItem->id_modulo);
                        $modulosAsociados[$num] = $planItem->modulo;
                    }
                }
                
                $cantidadModulos = 1;
                while (isset($modulosAsociados[$moduloFin + 1])) {
                    $moduloFin++;
                    $cantidadModulos++;
                }
                
                $moduloFinalObj = $modulosAsociados[$moduloFin] ?? $programacion->modulo;
                $moduloInicioObj = $programacion->modulo;
                $idAsignatura = $programacion->id_asignatura;
                $nombreAsignaturaResp = $programacion->asignatura->nombre_asignatura ?? 'Clase regular';
            } elseif ($planColaborador) {
                // Clases temporales (colaborador)
                $idColab = $planColaborador->id_profesor_colaborador;
                $planificacionesMismoBloque = \App\Models\PlanificacionProfesorColaborador::with('modulo')
                    ->where(function ($eq) use ($espacio, $espaciosEquiv, $espacioLimpio) {
                        $eq->where('id_espacio', $espacio->id_espacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpio]);
                    })
                    ->where('id_profesor_colaborador', $idColab)
                    ->whereHas('modulo', function ($q) use ($diasPosibles) {
                        $q->whereIn('dia', $diasPosibles);
                    })
                    ->get();

                $numModuloActual = \App\Helpers\ModulosHelper::getNumeroModulo($planColaborador->id_modulo);
                $moduloFin = $numModuloActual;
                
                $modulosAsociados = [];
                foreach ($planificacionesMismoBloque as $planItem) {
                    if ($planItem->modulo) {
                        $num = \App\Helpers\ModulosHelper::getNumeroModulo($planItem->id_modulo);
                        $modulosAsociados[$num] = $planItem->modulo;
                    }
                }
                
                $cantidadModulos = 1;
                while (isset($modulosAsociados[$moduloFin + 1])) {
                    $moduloFin++;
                    $cantidadModulos++;
                }
                
                $moduloFinalObj = $modulosAsociados[$moduloFin] ?? $planColaborador->modulo;
                $moduloInicioObj = $planColaborador->modulo;
                $idAsignatura = $planColaborador->profesorColaborador->id_asignatura ?? null;
                $nombreAsignaturaResp = $planColaborador->profesorColaborador->nombre_asignatura_temporal ?? ($planColaborador->profesorColaborador->asignatura->nombre_asignatura ?? 'Clase temporal');
            } elseif ($infoRecuperacion) {
                $numModuloActual = $infoRecuperacion['modulo_inicio'];
                $moduloFin = $infoRecuperacion['modulo_fin'];
                $cantidadModulos = $infoRecuperacion['cantidad_modulos'];
                $moduloInicioObj = (object) [
                    'id_modulo' => $infoRecuperacion['id_modulo'],
                    'hora_inicio' => $infoRecuperacion['hora_inicio'],
                    'hora_termino' => $infoRecuperacion['hora_termino'],
                ];
                $moduloFinalObj = (object) [
                    'id_modulo' => $infoRecuperacion['id_modulo_fin'] ?? $infoRecuperacion['id_modulo'],
                    'hora_inicio' => $infoRecuperacion['hora_inicio'],
                    'hora_termino' => $infoRecuperacion['hora_termino'],
                ];
                $idAsignatura = $infoRecuperacion['id_asignatura'];
                $nombreAsignaturaResp = $infoRecuperacion['nombre_asignatura'] ?? 'Recuperación de clase';
                if (!empty($infoRecuperacion['seccion'])) {
                    $nombreAsignaturaResp .= ' (Sec. ' . $infoRecuperacion['seccion'] . ')';
                }
            }

            // Si el docente ya tenía una reserva programada en este espacio hoy que cubra este horario,
            // la activamos y convertimos a clase oficial en lugar de duplicarla
            $reservaProgramadaExistente = Reserva::where('id_espacio', $espacio->id_espacio)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'programada')
                ->where(function ($q) use ($profesor, $runProfesor, $runLimpio) {
                    $q->where('run_profesor', $profesor->run_profesor)
                      ->orWhere('run_profesor', $runProfesor)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_solicitante', $profesor->run_profesor)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                })
                ->first();

            // Crear o actualizar la reserva con la programación encontrada
            $reservaPrevia = $reservaProgramadaExistente ?? $reservaReactivable;
            $reserva = $reservaPrevia ?? new Reserva();
            if (!$reservaPrevia) {
                $reserva->id_reserva = Reserva::generarIdUnico();
            }
            $reserva->run_profesor = $profesor->run_profesor;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->id_asignatura = $idAsignatura;
            $reserva->fecha_reserva = $fechaActual;
            // ✓ Hora real del escaneo (no la hora programada del módulo)
            $reserva->hora = now()->format('H:i:s');
            // ✓ Fin del último módulo consecutivo (para auto-finalización correcta)
            $reserva->hora_salida = $moduloFinalObj->hora_termino ?? null;
            $reserva->modulo_inicio = $numModuloActual;
            $reserva->modulo_fin = $moduloFin;
            $reserva->modulos = $cantidadModulos; // Calculado basado en la programación consecuente
            $reserva->estado = 'activa'; // Creada automáticamente pero activa
            $reserva->clase_finalizada_anticipadamente = false;
            $reserva->tipo_reserva = $infoRecuperacion ? 'recuperacion' : 'clase'; // Marcado como recuperación o clase programada
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Enviar correo de confirmación de uso de sala
            app(\App\Services\ReservaService::class)->enviarCorreoReserva($reserva, $profesor->email ?? null);

            // Si es una recuperación de clase, actualizar de inmediato los registros correspondientes
            if ($infoRecuperacion) {
                try {
                    $modulosRec = $infoRecuperacion['modulos_ids'] ?? [$infoRecuperacion['id_modulo']];
                    \App\Models\RecuperacionClase::whereDate('fecha_reagendada', $fechaActual)
                        ->where(function ($q) use ($profesor, $runProfesor, $runLimpio) {
                            $q->where('run_profesor', $profesor->run_profesor)
                              ->orWhere('run_profesor', $runProfesor)
                              ->orWhere('run_profesor', $runLimpio);
                        })
                        ->where(function ($q) use ($modulosRec, $idAsignatura) {
                            if (!empty($idAsignatura)) {
                                $q->where('id_asignatura', $idAsignatura);
                            }
                            $q->where(function ($sq) use ($modulosRec) {
                                foreach ($modulosRec as $mRec) {
                                    $sq->orWhere('id_modulo_reagendado', $mRec)
                                       ->orWhere('id_modulo_reagendado', 'LIKE', "%{$mRec}%");
                                }
                            });
                        })
                        ->whereIn('estado', ['reagendada', 'pendiente', 'confirmada', 'programada'])
                        ->update(['estado' => 'realizada']);

                    \App\Models\ClaseNoRealizada::whereDate('fecha_clase', $fechaActual)
                        ->where(function ($q) use ($profesor, $runProfesor, $runLimpio) {
                            $q->where('run_profesor', $profesor->run_profesor)
                              ->orWhere('run_profesor', $runProfesor)
                              ->orWhere('run_profesor', $runLimpio)
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                        })
                        ->where(function ($q) use ($modulosRec, $idAsignatura) {
                            if (!empty($idAsignatura)) {
                                $q->where('id_asignatura', $idAsignatura);
                            }
                            $q->where(function ($sq) use ($modulosRec) {
                                foreach ($modulosRec as $mRec) {
                                    $sq->orWhere('id_modulo', $mRec)
                                       ->orWhere('id_modulo', 'LIKE', "%{$mRec}%");
                                }
                            });
                        })
                        ->whereIn('estado', ['pendiente', 'programada'])
                        ->get()
                        ->each(function ($cnrItem) {
                            $horaScan = now()->format('H:i:s');
                            $obs = $cnrItem->observaciones ?? '';
                            $nota = "✓ CLASE RECUPERADA - Docente inició clase por código QR a las {$horaScan}";
                            $cnrItem->update([
                                'estado' => 'recuperada',
                                'observaciones' => !empty($obs) && !str_contains($obs, 'CLASE RECUPERADA') ? "{$obs} | {$nota}" : ($obs ?: $nota),
                            ]);
                        });
                } catch (\Throwable $e) {
                    Log::warning('Error actualizando estados de recuperación en crearReservaProfesorAutomatica: ' . $e->getMessage());
                }
            }

            // Limpiar registros incorrectos de clases no realizadas con hora real de ingreso
            \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                $espacio->id_espacio,
                $fechaActual,
                now()->format('H:i:s'),
                $profesor->run_profesor
            );

            $esClaseReagendada = (bool) $infoRecuperacion;

            return response()->json([
                'success' => true,
                'mensaje' => $esClaseReagendada ? 'Clase reagendada registrada exitosamente' : 'Reserva creada automáticamente según programación',
                'tipo' => $esClaseReagendada ? 'clase_reagendada' : 'clase_regular',
                'es_recuperacion' => $esClaseReagendada,
                'reserva' => [
                    'id'          => $reserva->id_reserva,
                    'profesor'    => $profesor->name,
                    'run_profesor'=> $profesor->run_profesor,
                    'espacio'     => $espacio->nombre_espacio,
                    'fecha'       => $fechaActual,
                    'asignatura'  => $nombreAsignaturaResp,
                    'hora_inicio' => $moduloInicioObj->hora_inicio ?? null,
                    'hora_termino'=> $moduloFinalObj->hora_termino ?? null,
                    'tipo_reserva'=> $reserva->tipo_reserva,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva automática: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva automática de profesor: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'run_profesor' => 'required|integer|digits_between:7,8|unique:profesors,run_profesor',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:profesors,email',
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'tipo_profesor' => 'required|in:Profesor Colaborador,Profesor Responsable,Ayudante',
                'id_universidad' => 'nullable|exists:universidades,id_universidad',
                'id_facultad' => 'nullable|exists:facultades,id_facultad',
                'id_carrera' => 'nullable|exists:carreras,id_carrera',
                'id_area_academica' => 'nullable|exists:area_academicas,id_area_academica',
            ]);

            $validatedData['sede_id'] = $request->input('sede_id') ?? (\App\Models\Tenant::current()?->sede_id);
            Profesor::create($validatedData);

            return redirect()->route('professors.index')->with('success', 'Profesor creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el profesor. Por favor, intente nuevamente.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $profesor = Profesor::findOrFail($id);
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            
            // Cargar clases temporales donde es colaborador
            $clasesTemporales = $profesor->clasesTemporalesColaborador()
                ->with(['asignatura', 'planificaciones.modulo'])
                ->orderBy('fecha_termino', 'desc')
                ->get();

            return view('profesores.edit', compact('profesor', 'universidades', 'facultades', 'carreras', 'areasAcademicas', 'clasesTemporales'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el profesor: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'run_profesor' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $request->input('run_profesor');
            $idEspacio = $request->input('id_espacio');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return redirect()->route('professors.index')->withErrors(['error' => 'Espacio no encontrado.']);
            }

            // Aquí iría la lógica de actualización del profesor
            return redirect()->route('professors.index')->with('success', 'Profesor actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Error al actualizar el profesor.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $profesor = Profesor::findOrFail($id);
            $profesor->delete();
            return redirect()->route('professors.index')->with('success', 'Profesor eliminado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Profesor no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el profesor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API method for getting profesor information (existing method)
     */
    public function getProfesor($run)
    {
        try {
            $profesor = Profesor::where('run_profesor', $run)->first();
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Aquí deberías obtener el espacio, por ejemplo por id_espacio (ajusta según tu lógica):
            $espacio = Espacio::where('id_espacio', $profesor->id_espacio ?? null)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            if ($espacio->estado !== 'Disponible') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio no está disponible'
                ], 400);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 7 * 60 + 30; // 07:30 (pero el mensaje dirá 08:10)
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                ], 400);
            }

            // Obtener profesor y su asignatura principal
            $profesor = Profesor::where('run_profesor', $run)->first();
            $asignatura = $profesor ? $profesor->asignaturas()->first() : null;

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->run_profesor = $run;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->id_asignatura = $asignatura ? $asignatura->id_asignatura : null;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->hora = $horaActual;
            $reserva->estado = 'activa';
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Enviar correo de confirmación de uso de sala
            app(\App\Services\ReservaService::class)->enviarCorreoReserva($reserva, $profesor->email ?? null);

            // Limpiar registros incorrectos de clases no realizadas (mover a atrasos)
            \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                $espacio->id_espacio,
                $fechaActual,
                $horaActual,
                $profesor->run_profesor
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente para el profesor',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor' => $profesor->name,
                    'run_profesor' => $profesor->run_profesor,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $horaActual
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva de profesor: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asignaturas de un profesor
     * Asegura que SIEMPRE se retornen las asignaturas con eager loading
     */
    public function getAsignaturasProfesor($run)
    {
        try {            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();


            // Buscar profesor con eager loading de asignaturas
            // IMPORTANTE: Incluir run_profesor en el select para que funcione el eager loading
            $profesor = Profesor::with(['asignaturas' => function($query) {
                $query->select('asignaturas.id_asignatura', 'asignaturas.codigo_asignatura', 'asignaturas.nombre_asignatura', 'asignaturas.run_profesor')
                      ->orderBy('asignaturas.codigo_asignatura');
            }])->where('run_profesor', $run)->first();

            if (!$profesor) {
                Log::warning('⚠️ Profesor no encontrado: ' . $run);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener asignaturas - usar la relación cargada
            $asignaturas = $profesor->asignaturas;



            return response()->json([
                'success' => true,
                'asignaturas' => $asignaturas,
                'count' => $asignaturas->count(),
                'profesor' => [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener asignaturas del profesor: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener asignaturas: ' . $e->getMessage(),
                'asignaturas' => []
            ], 500);
        }
    }

    /**
     * Establece el contexto del tenant desde el request
     */
    private function establecerContextoTenant()
    {
        if (Tenant::current()) {
            return;
        }

        $tenantId = tenant_id() ?? (request()->hasSession() ? session('tenant_id') : null);
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
                return;
            }
        }

        $host = request()->getHost();
        $subdomain = explode('.', $host)[0] ?? $host;
        $tenant = Tenant::where('domain', $host)
            ->orWhere('domain', $subdomain)
            ->orWhere('domain', 'LIKE', '%' . $host . '%')
            ->first();

        if ($tenant) {
            $tenant->makeCurrent();
        }
    }

    /**
     * Normaliza el ID de espacio soportando formatos compactos, comillas de lector QR y alias por sede.
     */
    private function normalizeEspacioId(?string $idEspacio): ?string
    {
        if (empty($idEspacio)) {
            return $idEspacio;
        }

        $idEspacio = strtoupper(trim(str_replace([' ', "'", '_'], ['', '-', '-'], $idEspacio)));

        // Corregir formato partido como THL-01 -> TH-L01, THC-1 -> TH-C1
        if (preg_match('/^(TH|LA|CH|CT)([A-Z])-([A-Z0-9]+)$/', $idEspacio, $mCorr)) {
            $idEspacio = "{$mCorr[1]}-{$mCorr[2]}{$mCorr[3]}";
        }

        $tenant = Tenant::current() ?? Tenant::find(tenant_id() ?? (request()->hasSession() ? session('tenant_id') : null));
        $prefix = $tenant ? strtoupper($tenant->prefijo_espacios ?: $tenant->domain) : null;

        if ($prefix) {
            $normalizedInput = str_replace('-', '', $idEspacio);
            if (strpos($normalizedInput, $prefix) === 0) {
                $normalizedInput = substr($normalizedInput, strlen($prefix));
            }
            $idEspacio = $prefix . '-' . $normalizedInput;
        }

        return \App\Helpers\EspacioAliasHelper::normalizar($idEspacio, $prefix);
    }
}
