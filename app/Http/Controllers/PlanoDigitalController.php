<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Bloque;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use App\Models\Reserva;
use App\Models\Sede;
use App\Models\Piso;
use App\Models\Espacio;
use App\Models\Profesor;
use App\Models\Solicitante;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlanoDigitalController extends Controller
{
    public function index()
    {
        $sedes = Sede::with(['universidad', 'facultades.pisos.mapas'])->get();

        // Verificar si hay mapas disponibles
        $mapasDisponibles = Mapa::count();
        $tieneMapas = $mapasDisponibles > 0;

        return view('layouts.plano_digital.index', compact('sedes', 'tieneMapas', 'mapasDisponibles'));
    }

    public function show($id)
    {
        try {
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);

            // Obtener todos los pisos de la misma facultad con sus mapas
            $pisos = Piso::with(['mapas'])
                ->where('id_facultad', $mapa->piso->id_facultad)
                ->orderBy('numero_piso')
                ->get();

            // Formatear los pisos con sus mapas
            $pisosFormateados = $pisos->map(function ($piso) {
                $primerMapa = $piso->mapas->first();
                return [
                    'id' => $piso->id,
                    'numero' => $piso->numero_piso,
                    'nombre' => "Piso {$piso->numero_piso}",
                    'id_mapa' => $primerMapa ? $primerMapa->id_mapa : null
                ];
            });

            return view('layouts.plano_digital.show', [
                'mapa' => $mapa,
                'bloques' => $bloques,
                'pisos' => $pisosFormateados
            ]);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar los pisos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al cargar los pisos: ' . $e->getMessage());
        }
    }

    public function bloques($id)
    {
        try {
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);

            return response()->json(['bloques' => $bloques]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los bloques'], 500);
        }
    }

    private function obtenerMapa($id)
    {
        return Mapa::with(['bloques.espacio', 'piso.facultad.sede.universidad'])
            ->where('id_mapa', $id)
            ->firstOrFail();
    }

    private function obtenerEstadoActual(Carbon $horaActual)
    {
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        $fechaActual = $horaActual->format('Y-m-d');

        return [
            'hora' => $horaActualStr,
            'dia' => $diaActual,
            'fecha' => $fechaActual,
            'codigo_dia' => $this->obtenerCodigoDia($diaActual)
        ];
    }

    private function obtenerCodigoDia(string $diaActual): ?string
    {
        return match ($diaActual) {
            'lunes' => 'LU',
            'martes' => 'MA',
            'miércoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sábado' => 'SA',
            default => null
        };
    }

    private function prepararBloques(Mapa $mapa, array $estadoActual): array
    {
        $moduloActual = $this->obtenerModuloActual($estadoActual);
        $planificacionesActivas = $this->obtenerPlanificacionesActivas($mapa, $moduloActual);
        $planificacionesProximas = $this->obtenerPlanificacionesProximas($mapa, $estadoActual);

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $planificacionesProximas, $mapa) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;

            // 1. Si el campo estado es "Ocupado", siempre ocupado
            if ($espacio->estado === 'Ocupado') {
                $estadoFinal = 'Ocupado';
            } else {
                // 2. Si el campo estado es "Disponible"
                $planificacionActiva = $planificacionesActivas->firstWhere('id_espacio', $idEspacio);
                $planificacionProxima = $planificacionesProximas->firstWhere('id_espacio', $idEspacio);
                if ($planificacionActiva) {
                    $estadoFinal = 'Reservado'; // Naranja (reservado)
                } elseif ($planificacionProxima) {
                    $estadoFinal = 'Proximo'; // Azul (próximo)
                } else {
                    $estadoFinal = 'Disponible'; // Verde (disponible)
                }
            }

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $estadoFinal,
                'detalles' => array_merge(
                    $this->prepararDetallesBloque(
                        $bloque->espacio,
                        $planificacionActiva ?? null,
                        null,
                        $planificacionProxima ?? null
                    ),
                    [
                        'estado' => $espacio->estado,
                        'facultad' => $mapa->piso->facultad->nombre_facultad
                    ]
                )
            ];
        })->toArray();
    }

    private function obtenerModuloActual(array $estadoActual): ?Modulo
    {
        return Modulo::where('dia', $estadoActual['dia'])
            ->where('hora_inicio', '<=', $estadoActual['hora'])
            ->where('hora_termino', '>=', $estadoActual['hora'])
            ->first();
    }

    private function obtenerPlanificacionesActivas(Mapa $mapa, ?Modulo $moduloActual)
    {
        if (!$moduloActual) {
            return collect([]);
        }

        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener la hora actual
        $horaActual = Carbon::now()->format('H:i:s');

        return Planificacion_Asignatura::with(['horario', 'asignatura.profesor', 'modulo', 'espacio'])
            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual); // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();
    }

    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $diaActual = $estadoActual['dia'];

        $periodo = SemesterHelper::getCurrentPeriod();

        // Verificar si estamos en el rango especial de 05:00 a 05:10
        $horaActualStr = $horaActual->format('H:i:s');
        $esRangoEspecial = ($horaActualStr >= '05:00:00' && $horaActualStr <= '05:10:00');

        if ($esRangoEspecial) {
            // Para el rango 05:00-05:10, buscar clases que empiecen a las 05:10
            $horaInicioBusqueda = '05:00:00';
            $horaFinBusqueda = '05:10:00';
        } else {
            // Para otros horarios, usar la lógica normal (10 minutos después)
            $horaInicioBusqueda = $horaActual->format('H:i:s');
            $horaFinBusqueda = $horaActual->copy()->addMinutes(9)->format('H:i:s');
        }

        return Planificacion_Asignatura::with(['horario', 'asignatura.profesor', 'modulo', 'espacio'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diaActual, $esRangoEspecial) {
                $query->where('dia', $diaActual);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 10 minutos
                    $query->where('hora_inicio', '>', $horaInicioBusqueda)
                          ->where('hora_inicio', '<=', $horaFinBusqueda);
                }
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();
    }

    private function prepararDetallesBloque($espacio, $planificacion, $reserva, $planificacionProxima): array
    {
        $detalles = [
            'tipo_espacio' => $espacio->tipo_espacio ?? 'No especificado',
            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
            'planificacion' => null,
            'reserva' => null,
            'planificacion_proxima' => null
        ];

        if ($planificacion && $planificacion->asignatura) {
            $detalles['planificacion'] = [
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'No especificada',
                'profesor' => ucwords($planificacion->asignatura->profesor->name ?? 'No asignado'),
                'modulos' => $planificacion->asignatura->planificaciones()
                    ->where('id_espacio', $espacio->id_espacio)
                    ->with('modulo')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'dia' => $plan->modulo->dia ?? 'No especificado',
                            'hora_inicio' => $plan->modulo->hora_inicio ?? '00:00:00',
                            'hora_termino' => $plan->modulo->hora_termino ?? '00:00:00'
                        ];
                    })->toArray()
            ];
        }

        if ($planificacionProxima && $planificacionProxima->asignatura) {
            $detalles['planificacion_proxima'] = [
                'asignatura' => $planificacionProxima->asignatura->nombre_asignatura ?? 'No especificada',
                'profesor' => ucwords($planificacionProxima->asignatura->profesor->name ?? 'No asignado'),
                'hora_inicio' => substr($planificacionProxima->modulo->hora_inicio ?? '00:00', 0, 5),
                'hora_termino' => substr($planificacionProxima->modulo->hora_termino ?? '00:00', 0, 5),
                'modulo' => explode('.', $planificacionProxima->modulo->id_modulo ?? '')[1] ?? 'No especificado'
            ];
        }

        if ($reserva) {
            $detalles['reserva'] = [
                'fecha_reserva' => $reserva->fecha_reserva ?? 'No especificada',
                'hora' => $reserva->hora ?? '00:00:00',
                'hora_salida' => $reserva->hora_salida ?? null
            ];
            // Incluir el nombre del usuario que ocupa el espacio
            $detalles['usuario_ocupando'] = $reserva->user ? $reserva->user->name : null;

            // Incluir información adicional del usuario si está disponible
            if ($reserva->user) {
                $detalles['usuario_info'] = [
                    'nombre' => $reserva->user->name ?? 'No especificado',
                    'email' => $reserva->user->email ?? 'No especificado',
                    'run' => $reserva->user->run ?? 'No especificado'
                ];
            }
        } else {
            $detalles['usuario_ocupando'] = null;
            $detalles['usuario_info'] = null;
        }

        return $detalles;
    }

    public function getModuloActual(Request $request, $id)
    {
        try {
            $horaActual = $request->input('hora');
            $diaActual = $request->input('dia');

            $modulo = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '<=', $horaActual)
                ->where('hora_termino', '>=', $horaActual)
                ->first();

            if ($modulo) {
                // Formatear las horas para mostrar solo HH:mm
                $modulo->hora_inicio = substr($modulo->hora_inicio, 0, 5);
                $modulo->hora_termino = substr($modulo->hora_termino, 0, 5);
            }

            return response()->json([
                'modulo' => $modulo
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el módulo actual'], 500);
        }
    }

    public function getPlanoData($id)
    {
        $mapa = Mapa::with(['piso.facultad.sede'])->findOrFail($id);
        $estadoActual = $this->obtenerEstadoActual(Carbon::now());
        $bloques = $this->prepararBloques($mapa, $estadoActual);

        return response()->json([
            'mapa' => [
                'id' => $mapa->id_mapa,
                'nombre' => $mapa->nombre_mapa,
                'ruta_mapa' => asset('storage/' . $mapa->ruta_mapa),
                'piso' => [
                    'numero' => $mapa->piso->numero_piso,
                    'facultad' => $mapa->piso->facultad->nombre_facultad,
                    'sede' => $mapa->piso->facultad->sede->nombre_sede
                ]
            ],
            'bloques' => $bloques
        ]);
    }

    public function estadosEspacios()
    {
        $horaActual = Carbon::now();
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');

        // Determinar el período actual usando el helper
        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener todos los espacios
        $espacios = Espacio::all();

        // Obtener todas las planificaciones activas para el período actual
        $planificacionesActivas = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.profesor'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual) {
                $query->where('dia', $diaActual);
            })
            ->get();

        // Obtener reservas activas para hoy
        $reservasActivas = Reserva::where('fecha_reserva', $horaActual->toDateString())
            ->where('estado', 'activa')
            ->get();

        return response()->json([
            'success' => true,
            'espacios' => $espacios->map(function($espacio) use ($horaActual, $horaActualStr, $diaActual, $planificacionesActivas, $reservasActivas) {
                $estadoTabla = $espacio->estado; // Estado actual en la tabla espacios

                // Verificar si el espacio está ocupado por una reserva activa
                $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();

                // Verificar si el espacio tiene una clase programada que debería estar en curso
                $claseEnCurso = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)
                    ->filter(function($planificacion) use ($horaActualStr) {
                        return $planificacion->modulo->hora_inicio <= $horaActualStr &&
                               $planificacion->modulo->hora_termino > $horaActualStr;
                    })->first();

                $tieneClaseEnCurso = $claseEnCurso !== null;

                // Verificar si el espacio tiene una clase próxima (entre módulos)
                $tieneClaseProxima = false;
                $planificacionesDelEspacio = $planificacionesActivas->where('id_espacio', $espacio->id_espacio);

                foreach ($planificacionesDelEspacio as $planificacion) {
                    $horaInicioModulo = $planificacion->modulo->hora_inicio;
                    $horaActualCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $horaActualStr);
                    $horaInicioCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $horaInicioModulo);

                    // Si la clase comienza dentro de los próximos 10 minutos Y no está actualmente en curso
                    if ($horaInicioCarbon->gt($horaActualCarbon) &&
                        $horaInicioCarbon->diffInMinutes($horaActualCarbon) <= 10 &&
                        !$tieneClaseEnCurso) {
                        $tieneClaseProxima = true;
                        break;
                    }
                }

                // Determinar el estado final según la lógica correcta
                if ($estadoTabla === 'Ocupado') {
                    // Si el estado en la tabla es "Ocupado", mostrar rojo y mantenerlo hasta devolución
                    $estado = 'Ocupado';
                } elseif ($tieneReservaActiva) {
                    $estado = 'Reservado';
                } elseif ($tieneClaseEnCurso && $estadoTabla !== 'Ocupado') {
                    // Clase en curso en el módulo actual - mostrar naranja
                    $estado = 'Reservado'; // Naranja
                } elseif ($tieneClaseProxima) {
                    // Clase próxima (siguiente módulo) - mostrar azul
                    $estado = 'Proximo';
                } elseif ($estadoTabla === 'Disponible') {
                    $estado = 'Disponible';
                } else {
                    $estado = $estadoTabla;
                }

                // Preparar información adicional para el modal
                $informacionAdicional = null;
                if ($tieneClaseEnCurso && $claseEnCurso) {
                    $informacionAdicional = [
                        'asignatura' => $claseEnCurso->asignatura->nombre_asignatura ?? 'No especificada',
                        'profesor' => $claseEnCurso->asignatura->profesor->name ?? 'No especificado',
                        'modulo' => explode('.', $claseEnCurso->modulo->id_modulo)[1] ?? 'No especificado',
                        'hora_inicio' => substr($claseEnCurso->modulo->hora_inicio, 0, 5),
                        'hora_termino' => substr($claseEnCurso->modulo->hora_termino, 0, 5)
                    ];
                }

                return [
                    'id_espacio' => $espacio->id_espacio,
                    'estado' => $estado,
                    'informacion_clase_actual' => $informacionAdicional
                ];
            })
        ]);
    }

    /**
     * Devolver un espacio ocupado
     */
    public function devolverEspacio(Request $request)
    {
        try {
            $request->validate([
                'id_espacio' => 'required|string',
                'run_usuario' => 'required|string'
            ]);

            $idEspacio = $request->input('id_espacio');
            $runUsuario = $request->input('run_usuario');

            // Buscar el espacio
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            $reservaActiva = Reserva::where(function($query) use ($runUsuario) {
                    $query->where('run_profesor', $runUsuario)
                          ->orWhere('run_solicitante', $runUsuario);
                })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->first();

            if (!$reservaActiva) {
                \Log::warning("Intento de devolución sin reserva activa - Usuario: {$runUsuario}, Espacio: {$idEspacio}");

                // Verificar si el espacio ya está disponible (puede que ya se haya devuelto)
                if ($espacio->estado === 'Disponible') {
                    return response()->json([
                        'success' => true,
                        'mensaje' => 'El espacio ya está disponible',
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio,
                            'estado' => $espacio->estado
                        ]
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'mensaje' => 'No tienes una reserva activa en este espacio'
                ], 400);
            }



            // Actualizar la reserva activa del usuario: establecer hora_salida y cambiar estado a finalizada
            if ($reservaActiva) {
                $reservaActiva->hora_salida = now()->format('H:i:s');
                $reservaActiva->estado = 'finalizada';
                $reservaActiva->save();
            }

            // Cambiar el estado del espacio a disponible
            $espacio->estado = 'Disponible';
            $espacio->save();

            // Registrar la devolución en un log o tabla de auditoría si es necesario
            \Log::info("Espacio {$idEspacio} devuelto exitosamente por usuario {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}");

            return response()->json([
                'success' => true,
                'mensaje' => 'Espacio devuelto exitosamente',
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al devolver espacio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Verificar estado del espacio y reservas del usuario
     */
    public function verificarEstadoEspacioYReserva(Request $request)
    {
        try {
            // Registro de diagnóstico: confirmar que la función fue invocada y mostrar payload (temporal)
            \Log::info('verificarEstadoEspacioYReserva called', ['payload' => $request->all()]);
            $request->validate([
                'run' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runUsuario = $request->input('run');
            $idEspacio = $request->input('id_espacio');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return response()->json([
                    'tipo' => 'error',
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en otro espacio (prioridad alta)
            $reservaExistente = Reserva::where(function($query) use ($runUsuario) {
                    $query->where('run_profesor', $runUsuario)
                          ->orWhere('run_solicitante', $runUsuario);
                })
                ->where('id_espacio', '!=', $idEspacio)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                // El usuario ya tiene una reserva activa en otro espacio
                return response()->json([
                    'tipo' => 'reserva_existente',
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio. Debes finalizarla antes de solicitar una nueva.',
                    'espacio_disponible' => false
                ]);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            $reservaActiva = Reserva::where(function($query) use ($runUsuario) {
                    $query->where('run_profesor', $runUsuario)
                          ->orWhere('run_solicitante', $runUsuario);
                })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->first();

            // Verificar si el espacio está disponible
            $espacioDisponible = $espacio->estado === 'Disponible';

            if ($reservaActiva) {
                // El usuario tiene una reserva activa en este espacio
                \Log::info("Reserva activa encontrada para devolución - Usuario: {$runUsuario}, Espacio: {$idEspacio}, Reserva ID: {$reservaActiva->id_reserva}");
                return response()->json([
                    'tipo' => 'devolucion',
                    'mensaje' => 'Tienes una reserva activa en este espacio. ¿Deseas devolver las llaves?',
                    'reserva' => [
                        'id_reserva' => $reservaActiva->id_reserva,
                        'hora_inicio' => $reservaActiva->hora,
                        'fecha' => $reservaActiva->fecha_reserva,
                        'espacio' => $espacio->nombre_espacio
                    ],
                    'espacio_disponible' => false
                ]);
            } elseif ($espacioDisponible) {
                // El espacio está disponible para crear una nueva reserva
                return response()->json([
                    'tipo' => 'nueva_reserva',
                    'mensaje' => 'Espacio disponible para reservar',
                    'espacio_disponible' => true
                ]);
            } else {
                // El espacio está ocupado por otro usuario - buscar información de la reserva activa más reciente
                $reservaOcupante = Reserva::where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $mensaje = 'El espacio está ocupado por otro usuario';
                $informacionOcupante = null;

                if ($reservaOcupante) {
                    if ($reservaOcupante->run_profesor) {
                        // Es un profesor
                        $profesor = Profesor::where('run_profesor', $reservaOcupante->run_profesor)->first();
                        if ($profesor) {
                            $mensaje = "El espacio está ocupado por el profesor {$profesor->name}";
                            $informacionOcupante = [
                                'tipo' => 'profesor',
                                'nombre' => $profesor->name,
                                'run' => $profesor->run_profesor,
                                'email' => $profesor->email,
                                'tipo_profesor' => $profesor->tipo_profesor,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva
                            ];
                        }
                    } elseif ($reservaOcupante->run_solicitante) {
                        // Es un solicitante
                        $solicitante = Solicitante::where('run_solicitante', $reservaOcupante->run_solicitante)->first();
                        if ($solicitante) {
                            $mensaje = "El espacio está ocupado por el solicitante {$solicitante->nombre}";
                            $informacionOcupante = [
                                'tipo' => 'solicitante',
                                'nombre' => $solicitante->nombre,
                                'run' => $solicitante->run_solicitante,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva
                            ];
                        }
                    }
                }

                return response()->json([
                    'tipo' => 'espacio_ocupado',
                    'mensaje' => $mensaje,
                    'espacio_disponible' => false,
                    'ocupante' => $informacionOcupante
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error al verificar estado del espacio y reserva: ' . $e->getMessage());
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error al verificar estado del espacio y reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar usuario (profesor, solicitante o usuario no registrado)
     */
    public function verificarUsuario($run)
    {
        try {
            // CASO 1: Verificar si es profesor registrado
            $profesor = Profesor::select('run_profesor', 'name', 'email', 'celular', 'tipo_profesor')
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

            // CASO 2: Verificar si es solicitante registrado
            $solicitante = Solicitante::where('run_solicitante', $run)
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
                        'telefono' => $solicitante->telefono
                    ],
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // CASO 3: Usuario no encontrado - Mostrar modal de registro como solicitante
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado. Se requiere registro como solicitante.',
                'requiere_registro' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al verificar usuario: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar espacio
     */
    public function verificarEspacio($idEspacio)
    {
        try {
            $espacio = Espacio::with(['piso.facultad.sede'])
                ->where('id_espacio', $idEspacio)
                ->first();

            if (!$espacio) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el espacio está disponible
            $disponible = $espacio->estado === 'Disponible';

            return response()->json([
                'verificado' => true,
                'disponible' => $disponible,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'puestos' => $espacio->puestos_disponibles,
                    'estado' => $espacio->estado,
                    'piso' => $espacio->piso->numero_piso,
                    'facultad' => $espacio->piso->facultad->nombre_facultad,
                    'sede' => $espacio->piso->facultad->sede->nombre_sede
                ],
                'mensaje' => $disponible ? 'Espacio disponible' : 'Espacio no disponible'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al verificar espacio: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva (método principal)
     */
    public function crearReserva(Request $request)
    {
        try {
            $request->validate([
                'run_usuario' => 'required|string',
                'id_espacio' => 'required|string',
                'tipo_usuario' => 'required|in:profesor,solicitante,solicitante_registrado'
            ]);

            $runUsuario = $request->input('run_usuario');
            $idEspacio = $request->input('id_espacio');
            $tipoUsuario = $request->input('tipo_usuario');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar que el espacio esté disponible
            if ($espacio->estado !== 'Disponible') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio no está disponible'
                ], 400);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');
            $ahora = now();

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 8 * 60 + 10; // 08:10
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Crear reserva según el tipo de usuario
            if ($tipoUsuario === 'profesor') {
                return $this->crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora);
            } elseif ($tipoUsuario === 'solicitante' || $tipoUsuario === 'solicitante_registrado') {
                return $this->crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora);
            } else {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Tipo de usuario no válido'
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación al crear reserva: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva para profesor
     */
    private function crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        $runUsuario = $request->input('run_usuario');

        // Verificar si el profesor existe
        $profesor = Profesor::where('run_profesor', $runUsuario)->first();
        if (!$profesor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Profesor no encontrado'
            ], 404);
        }

        // Verificar si ya tiene una reserva activa
        $reservaExistente = Reserva::where('run_profesor', $runUsuario)
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->first();

        if ($reservaExistente) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Ya tienes una reserva activa en otro espacio'
            ], 400);
        }

        // Crear la reserva
        $reserva = new Reserva();
        $reserva->id_reserva = Reserva::generarIdUnico();
        $reserva->run_profesor = $runUsuario;
        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = $fechaActual;
        $reserva->hora = $horaActual;
        $reserva->estado = 'activa';
        $reserva->save();

        // Cambiar estado del espacio
        $espacio->estado = 'Ocupado';
        $espacio->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Reserva creada exitosamente',
            'reserva' => [
                'id' => $reserva->id_reserva,
                'espacio' => $espacio->nombre_espacio,
                'fecha' => $fechaActual,
                'hora_inicio' => $horaActual
            ]
        ]);
    }

    /**
     * Crear reserva para solicitante
     */
    private function crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        $runUsuario = $request->input('run_usuario');

        // Verificar si el solicitante existe
        $solicitante = Solicitante::where('run_solicitante', $runUsuario)
            ->where('activo', true)
            ->first();

        if (!$solicitante) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Solicitante no encontrado'
            ], 404);
        }

        // Verificar si ya tiene una reserva activa
        $reservaExistente = Reserva::where('run_solicitante', $runUsuario)
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->first();

        if ($reservaExistente) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Ya tienes una reserva activa en otro espacio'
            ], 400);
        }

        // Crear la reserva
        $reserva = new Reserva();
        $reserva->id_reserva = Reserva::generarIdUnico();
        $reserva->run_solicitante = $runUsuario;
        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = $fechaActual;
        $reserva->hora = $horaActual;
        $reserva->run_profesor = null; // explícito: reserva creada por solicitante
        $reserva->tipo_reserva = 'espontanea';
        $reserva->estado = 'activa';
        $reserva->save();

        // Cambiar estado del espacio
        $espacio->estado = 'Ocupado';
        $espacio->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Reserva creada exitosamente',
            'reserva' => [
                'id' => $reserva->id_reserva,
                'espacio' => $espacio->nombre_espacio,
                'fecha' => $fechaActual,
                'hora_inicio' => $horaActual
            ]
        ]);
    }





    /**
     * Verificar clases programadas
     */
    public function verificarClasesProgramadas($run, $horaActual, $diaActual)
    {
        try {
            // Convertir el día numérico a nombre del día
            $diasSemana = [
                0 => 'domingo',
                1 => 'lunes',
                2 => 'martes',
                3 => 'miércoles',
                4 => 'jueves',
                5 => 'viernes',
                6 => 'sábado'
            ];

            $nombreDia = $diasSemana[$diaActual] ?? 'lunes';

            // Obtener período actual
            $periodo = \App\Helpers\SemesterHelper::getCurrentPeriod();

            // Buscar planificaciones del profesor para el día actual
            $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo', 'espacio'])
                ->whereHas('asignatura', function($query) use ($run) {
                    $query->where('run_profesor', $run);
                })
                ->whereHas('modulo', function($query) use ($nombreDia) {
                    $query->where('dia', $nombreDia);
                })
                ->whereHas('horario', function($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->get();

            // Verificar si la hora actual está dentro del rango del horario del profesor (20:10-22:00)
            $horaActualTime = \Carbon\Carbon::createFromFormat('H:i:s', $horaActual);
            $inicioHorario = \Carbon\Carbon::createFromFormat('H:i:s', '20:10:00');
            $finHorario = \Carbon\Carbon::createFromFormat('H:i:s', '22:00:00');

            $tieneClasesEnHorario = $planificaciones->count() > 0 &&
                                   $horaActualTime->between($inicioHorario, $finHorario);

            // Agrupar por asignatura y detectar módulos consecutivos
            $clasesConModulosConsecutivos = [];
            $planificacionesAgrupadas = $planificaciones->groupBy('asignatura.nombre_asignatura');

            foreach ($planificacionesAgrupadas as $nombreAsignatura => $planificacionesAsignatura) {
                $modulosOrdenados = $planificacionesAsignatura->sortBy('modulo.hora_inicio');
                $secuenciasModulos = [];
                $secuenciaActual = [];

                foreach ($modulosOrdenados as $planificacion) {
                    if (empty($secuenciaActual)) {
                        $secuenciaActual[] = $planificacion;
                    } else {
                        $ultimoModulo = end($secuenciaActual)->modulo;
                        $moduloActual = $planificacion->modulo;

                        // Verificar si son consecutivos (el siguiente empieza cuando termina el anterior)
                        if ($ultimoModulo->hora_termino === $moduloActual->hora_inicio) {
                            $secuenciaActual[] = $planificacion;
                        } else {
                            // No son consecutivos, guardar secuencia anterior y empezar nueva
                            if (!empty($secuenciaActual)) {
                                $secuenciasModulos[] = $secuenciaActual;
                            }
                            $secuenciaActual = [$planificacion];
                        }
                    }
                }

                // Agregar la última secuencia
                if (!empty($secuenciaActual)) {
                    $secuenciasModulos[] = $secuenciaActual;
                }

                $clasesConModulosConsecutivos[$nombreAsignatura] = $secuenciasModulos;
            }

            return response()->json([
                'success' => true,
                'tiene_clases' => $tieneClasesEnHorario,
                'planificaciones' => $planificaciones->map(function($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura,
                        'espacio' => $plan->espacio->nombre_espacio,
                        'modulo' => $plan->modulo->id_modulo,
                        'hora_inicio' => $plan->modulo->hora_inicio,
                        'hora_termino' => $plan->modulo->hora_termino
                    ];
                }),
                'secuencias_modulos' => $clasesConModulosConsecutivos
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al verificar clases programadas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al verificar clases: ' . $e->getMessage()
            ], 500);
        }
    }




}
