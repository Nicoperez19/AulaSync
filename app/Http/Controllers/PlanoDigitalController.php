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
        $mapa = Mapa::with(['piso.facultad.sede'])->findOrFail($id);
        $estadoActual = $this->obtenerEstadoActual(Carbon::now());
        $bloques = $this->prepararBloques($mapa, $estadoActual);
        
        // Obtener todos los pisos de la misma sede
        $pisos = Mapa::with(['piso'])
            ->whereHas('piso.facultad.sede', function($query) use ($mapa) {
                $query->where('id_sede', $mapa->piso->facultad->sede->id_sede);
            })
            ->join('pisos', 'mapas.piso_id', '=', 'pisos.id')
            ->orderBy('pisos.numero_piso')
            ->select('mapas.*')
            ->get();

        return view('layouts.plano_digital.show', compact('mapa', 'bloques', 'pisos'));
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

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $reservasActivas, $planificacionesProximas, $estadoActual) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;
            
            // Determinar si el espacio está ocupado basado en su estado
            $estaOcupado = $espacio->estado === 'Ocupado';

            // Verificar si hay una reserva activa para este espacio
            $reservaActiva = $reservasActivas->firstWhere('id_espacio', $idEspacio);
            if ($reservaActiva) {
                // Si la reserva tiene hora_salida y es menor que la hora actual, el espacio no está ocupado
                if ($reservaActiva->hora_salida && $reservaActiva->hora_salida < $estadoActual['hora']) {
                    $estaOcupado = false;
                }
            }

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $this->determinarEstado($estaOcupado, false, false),
                'detalles' => array_merge(
                    $this->prepararDetallesBloque(
                        $bloque->espacio,
                        $estaOcupado ? $planificacionesActivas->firstWhere('id_espacio', $idEspacio) : null,
                        $estaOcupado ? $reservasActivas->firstWhere('id_espacio', $idEspacio) : null,
                        $planificacionesProximas->firstWhere('id_espacio', $idEspacio)
                    ),
                    ['estado' => $espacio->estado]
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

    private function obtenerReservasActivas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::now()->format('H:i:s');
        
        return Reserva::where('fecha_reserva', $estadoActual['fecha'])
            ->where('hora', '<=', $estadoActual['hora'])
            ->where('estado', 'activa') // Solo reservas activas
            ->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'))
            ->get();
    }

    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $horaLimite = $horaActual->copy()->addMinutes(5);
        $diaActual = $estadoActual['dia'];

        $mesActual = date('n');
        $anioActual = date('Y');
        $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
        $periodo = $anioActual . '-' . $semestre;

        return Planificacion_Asignatura::with(['horario', 'asignatura.profesor', 'modulo', 'espacio'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual, $horaActual, $horaLimite) {
                $query->where('dia', $diaActual)
                    ->where('hora_inicio', '>', $horaActual->format('H:i:s'))   // después de ahora
                    ->where('hora_inicio', '<=', $horaLimite->format('H:i:s')); // pero en <= 5 minutos
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();
    }

    private function determinarEstado(bool $estaOcupado, bool $estaReservado, bool $tieneClaseProxima): string
    {
        // Si el espacio está ocupado, mostrar en rojo
        if ($estaOcupado) {
            return 'red';
        }
        
        // Si el espacio está disponible, mostrar en verde
        return 'green';
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
                'hora_inicio' => $planificacionProxima->modulo->hora_inicio ?? '00:00:00',
                'hora_termino' => $planificacionProxima->modulo->hora_termino ?? '00:00:00'
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
}