<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Bloque;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use App\Models\Reserva;
use App\Models\Sede;
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
            // Verificar si hay mapas disponibles
            $mapasDisponibles = Mapa::count();
            if ($mapasDisponibles === 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay mapas disponibles en el sistema'
                    ], 404);
                }
                
                return redirect()->route('plano.index')->with('error', 'No hay mapas disponibles en el sistema');
            }

            $mapa = Mapa::with(['piso.facultad.sede'])->findOrFail($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);
            
            // Obtener todos los pisos de la sede TH y facultad IT_TH
            $pisos = Mapa::with(['piso' => function($query) {
                    $query->with(['facultad' => function($query) {
                        $query->with('sede');
                    }]);
                }])
                ->whereHas('piso.facultad.sede', function($query) {
                    $query->where('id_sede', 'TH');
                })
                ->whereHas('piso.facultad', function($query) {
                    $query->where('id_facultad', 'IT_TH');
                })
                ->join('pisos', 'mapas.piso_id', '=', 'pisos.id')
                ->orderBy('pisos.numero_piso')
                ->select('mapas.*', 'pisos.numero_piso')
                ->get();

            \Log::info('Pisos encontrados:', ['count' => $pisos->count(), 'pisos' => $pisos->toArray()]);

            // Obtener la sede actual
            $sede = $mapa->piso->facultad->sede;

            // Convertir los pisos a un formato más simple para la vista
            $pisosFormateados = $pisos->map(function($piso) {
                return [
                    'id_mapa' => $piso->id_mapa,
                    'numero_piso' => $piso->numero_piso,
                    'nombre_piso' => "Piso {$piso->numero_piso}"
                ];
            });

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pisos' => $pisosFormateados,
                        'mapa' => $mapa,
                        'bloques' => $bloques,
                        'sede' => $sede
                    ]
                ]);
            }

            return view('layouts.plano_digital.show', [
                'mapa' => $mapa,
                'bloques' => $bloques,
                'pisos' => $pisos,
                'sede' => $sede,
                'pisosJson' => json_encode($pisosFormateados)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en PlanoDigitalController@show:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
            \Log::info('Solicitud de bloques recibida:', ['id' => $id]);
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);
            \Log::info('Bloques preparados:', ['count' => count($bloques)]);
            
            // Log detallado de cada bloque para debuggear
            foreach ($bloques as $bloque) {
                \Log::info('Bloque procesado:', [
                    'id' => $bloque['id'],
                    'nombre' => $bloque['nombre'],
                    'estado' => $bloque['estado'],
                    'detalles' => $bloque['detalles']
                ]);
            }
            
            return response()->json(['bloques' => $bloques]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener bloques: ' . $e->getMessage());
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

        \Log::info('Estado actual:', [
            'hora' => $horaActualStr,
            'dia' => $diaActual,
            'fecha' => $fechaActual
        ]);

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
        $reservasActivas = $this->obtenerReservasActivas($mapa, $estadoActual);
        $planificacionesProximas = $this->obtenerPlanificacionesProximas($mapa, $estadoActual);

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $reservasActivas, $planificacionesProximas, $estadoActual, $mapa) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;
            
            \Log::info('Procesando espacio:', [
                'id_espacio' => $idEspacio,
                'nombre_espacio' => $espacio->nombre_espacio,
                'estado_bd' => $espacio->estado
            ]);
            
            // Inicializar estados
            $estaOcupado = false;
            $estaReservado = false;
            $deberiaEstarOcupado = false;
            $tieneClaseProxima = false;

            // Verificar si hay una reserva activa para este espacio
            $reservaActiva = $reservasActivas->firstWhere('id_espacio', $idEspacio);
            if ($reservaActiva) {
                \Log::info('Reserva activa encontrada:', [
                    'id_espacio' => $idEspacio,
                    'reserva' => $reservaActiva->toArray()
                ]);
                // Si la reserva tiene hora_salida y es menor que la hora actual, el espacio está disponible
                if ($reservaActiva->hora_salida && $reservaActiva->hora_salida < $estadoActual['hora']) {
                    $estaOcupado = false;
                    $estaReservado = false;
                } else {
                    // Si el espacio está marcado como Ocupado en la BD, entonces está ocupado, no reservado
                    if ($espacio->estado === 'Ocupado') {
                        $estaOcupado = true;
                        $estaReservado = false;
                        \Log::info('Espacio con reserva activa marcado como Ocupado en BD:', ['id_espacio' => $idEspacio]);
                    } else {
                        $estaReservado = true;
                        \Log::info('Espacio con reserva activa marcado como Reservado:', ['id_espacio' => $idEspacio]);
                    }
                }
            }

            // Verificar si hay una planificación activa para este espacio (clase en curso)
            $planificacionActiva = $planificacionesActivas->firstWhere('id_espacio', $idEspacio);
            if ($planificacionActiva) {
                \Log::info('Planificación activa encontrada:', [
                    'id_espacio' => $idEspacio,
                    'planificacion' => $planificacionActiva->toArray()
                ]);
                $deberiaEstarOcupado = true;
                // Si el espacio está marcado como ocupado en la base de datos, tiene prioridad
                if ($espacio->estado === 'Ocupado') {
                    $estaOcupado = true;
                    $estaReservado = false; // Asegurar que no se marque como reservado
                    \Log::info('Espacio con planificación activa marcado como Ocupado en BD:', ['id_espacio' => $idEspacio]);
                }
            }

            // Verificar si hay una planificación próxima para este espacio (clase próxima)
            $tieneClaseProxima = $planificacionesProximas->contains('id_espacio', $idEspacio);

            $estadoFinal = $this->determinarEstado($estaOcupado, $estaReservado, $deberiaEstarOcupado, $tieneClaseProxima);
            
            \Log::info('Estado determinado:', [
                'id_espacio' => $idEspacio,
                'estaOcupado' => $estaOcupado,
                'estaReservado' => $estaReservado,
                'deberiaEstarOcupado' => $deberiaEstarOcupado,
                'tieneClaseProxima' => $tieneClaseProxima,
                'estado_final' => $estadoFinal
            ]);

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $estadoFinal,
                'detalles' => array_merge(
                    $this->prepararDetallesBloque(
                        $bloque->espacio,
                        $estaOcupado ? $planificacionesActivas->firstWhere('id_espacio', $idEspacio) : null,
                        $estaOcupado ? $reservasActivas->firstWhere('id_espacio', $idEspacio) : null,
                        $planificacionesProximas->firstWhere('id_espacio', $idEspacio)
                    ),
                    [
                        'estado' => $estaReservado ? 'Reservado' : ($estaOcupado ? 'Ocupado' : 'Disponible'),
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

        $mesActual = date('n');
        $anioActual = date('Y');
        $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
        $periodo = $anioActual . '-' . $semestre;

        // Obtener la hora actual
        $horaActual = Carbon::now()->format('H:i:s');

        return Planificacion_Asignatura::with(['horario', 'asignatura.user', 'modulo', 'espacio'])
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

    private function obtenerReservasActivas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::now()->format('H:i:s');
        
        return Reserva::where('fecha_reserva', $estadoActual['fecha'])
            ->where('hora', '<=', $estadoActual['hora'])
            ->where('estado', 'activa')
            ->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'))
            ->get();
    }

    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $diaActual = $estadoActual['dia'];

        $mesActual = date('n');
        $anioActual = date('Y');
        $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
        $periodo = $anioActual . '-' . $semestre;

        // Calcular la hora límite (10 minutos después de la hora actual)
        $horaLimite = $horaActual->copy()->addMinutes(9)->format('H:i:s');

        return Planificacion_Asignatura::with(['horario', 'asignatura.user', 'modulo', 'espacio'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaActual, $horaLimite, $diaActual) {
                $query->where('dia', $diaActual)
                    ->where('hora_inicio', '>', $horaActual->format('H:i:s'))
                    ->where('hora_inicio', '<=', $horaLimite);
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();
    }

    private function determinarEstado(bool $estaOcupado, bool $estaReservado, bool $deberiaEstarOcupado, bool $tieneClaseProxima): string
    {
        \Log::info('Determinando estado:', [
            'estaOcupado' => $estaOcupado,
            'estaReservado' => $estaReservado,
            'deberiaEstarOcupado' => $deberiaEstarOcupado,
            'tieneClaseProxima' => $tieneClaseProxima
        ]);
        
        // Si el espacio está ocupado (estado en BD = 'Ocupado'), siempre mostrar en rojo
        if ($estaOcupado) {
            \Log::info('Estado determinado: Ocupado (Rojo)');
            return '#FF0000'; // Rojo - se mantiene hasta devolver llaves
        }
        // Si el espacio está reservado pero no ocupado, mostrar en naranja
        if ($estaReservado) {
            \Log::info('Estado determinado: Reservado (Naranja)');
            return '#FFA500'; // Naranja
        }
        // Si el espacio debería estar ocupado pero no lo está (clase en curso), mostrar naranja
        if ($deberiaEstarOcupado) {
            \Log::info('Estado determinado: Debería estar ocupado (Naranja)');
            return '#FFA500'; // Naranja - para clases en curso en el módulo actual
        }
        // Si el espacio tiene clase próxima (siguiente módulo), mostrar azul
        if ($tieneClaseProxima) {
            \Log::info('Estado determinado: Próximo (Azul)');
            return '#3B82F6'; // Azul - para clases próximas
        }
        // Si el espacio está disponible, mostrar en verde
        \Log::info('Estado determinado: Disponible (Verde)');
        return '#059669'; // Verde
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
                'profesor' => ucwords($planificacion->asignatura->user->name ?? 'No asignado'),
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
                'profesor' => ucwords($planificacionProxima->asignatura->user->name ?? 'No asignado'),
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
            \Log::error('Error al obtener módulo actual: ' . $e->getMessage());
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
        $horaActual = \Carbon\Carbon::now();
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        
        // Determinar el período actual
        $mesActual = date('n');
        $anioActual = date('Y');
        $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
        $periodo = $anioActual . '-' . $semestre;

        // Obtener todos los espacios
        $espacios = \App\Models\Espacio::all();
        
        // Obtener todas las planificaciones activas para el período actual
        $planificacionesActivas = \App\Models\Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual) {
                $query->where('dia', $diaActual);
            })
            ->get();
        
        // Obtener reservas activas para hoy
        $reservasActivas = \App\Models\Reserva::where('fecha_reserva', $horaActual->toDateString())
            ->where('estado', 'activa')
            ->get();

        return response()->json([
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
                        'profesor' => $claseEnCurso->asignatura->user->name ?? 'No especificado',
                        'modulo' => explode('.', $claseEnCurso->modulo->id_modulo)[1] ?? 'No especificado',
                        'hora_inicio' => substr($claseEnCurso->modulo->hora_inicio, 0, 5),
                        'hora_termino' => substr($claseEnCurso->modulo->hora_termino, 0, 5)
                    ];
                }
                
                return [
                    'id' => $espacio->id_espacio,
                    'estado' => $estado,
                    'informacion_clase_actual' => $informacionAdicional
                ];
            })
        ]);
    }
}