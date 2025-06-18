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
        return view('layouts.plano_digital.index', compact('sedes'));
    }

    public function show($id)
    {
        try {
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
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);
            return response()->json($bloques);
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
            
            // Inicializar estados
            $estaOcupado = false;
            $estaReservado = false;
            $deberiaEstarOcupado = false;

            // Verificar si hay una reserva activa para este espacio
            $reservaActiva = $reservasActivas->firstWhere('id_espacio', $idEspacio);
            if ($reservaActiva) {
                // Si la reserva tiene hora_salida y es menor que la hora actual, el espacio está disponible
                if ($reservaActiva->hora_salida && $reservaActiva->hora_salida < $estadoActual['hora']) {
                    $estaOcupado = false;
                    $estaReservado = false;
                } else {
                    $estaReservado = true;
                }
            }

            // Verificar si hay una planificación activa para este espacio
            $planificacionActiva = $planificacionesActivas->firstWhere('id_espacio', $idEspacio);
            if ($planificacionActiva) {
                $deberiaEstarOcupado = true;
                // Si el espacio está marcado como ocupado en la base de datos
                if ($espacio->estado === 'Ocupado') {
                    $estaOcupado = true;
                }
            }

            // Verificar si hay una planificación próxima para este espacio
            $tieneClaseProxima = $planificacionesProximas->contains('id_espacio', $idEspacio);

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $this->determinarEstado($estaOcupado, $estaReservado, $deberiaEstarOcupado),
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

    private function determinarEstado(bool $estaOcupado, bool $estaReservado, bool $deberiaEstarOcupado): string
    {
        // Si el espacio está reservado, mostrar en rojo
        if ($estaReservado) {
            return '#FF0000'; // Rojo
        }
        
        // Si el espacio está ocupado, mostrar en verde del sidebar
        if ($estaOcupado) {
            return '#059669'; // Verde sidebar
        }
        
        // Si el espacio debería estar ocupado pero no lo está, mostrar en naranja
        if ($deberiaEstarOcupado) {
            return '#FFA500'; // Naranja
        }
        
        // Si el espacio está disponible, mostrar en verde del sidebar
        return '#059669'; // Verde sidebar
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
                'hora' => $reserva->hora ?? '00:00:00'
            ];
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
        $espacios = \App\Models\Espacio::all()->map(function($espacio) {
            // Lógica para determinar si tiene próxima clase
            $estado = $espacio->estado;
            if (method_exists($espacio, 'tieneProximaClase') && $espacio->tieneProximaClase()) {
                $estado = 'Proximo';
            }
            // Si tienes un campo o relación diferente, ajusta aquí:
            // if ($espacio->planificacion_proxima) { $estado = 'Proximo'; }
            return [
                'id' => $espacio->id_espacio,
                'estado' => $estado,
            ];
        });
        return response()->json(['espacios' => $espacios]);
    }
}