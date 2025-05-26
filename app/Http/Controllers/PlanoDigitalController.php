<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Bloque;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlanoDigitalController extends Controller
{
    public function index()
    {
        $mapas = Mapa::with(['piso.facultad.sede.universidad'])->get();
        return view('layouts.plano_digital.index', compact('mapas'));
    }

    public function show($id)
    {
        $mapa = $this->obtenerMapa($id);
        $horaActual = Carbon::now();
        $estadoActual = $this->obtenerEstadoActual($horaActual);

        $bloques = $this->prepararBloques($mapa, $estadoActual);

        return view('layouts.plano_digital.show', compact('mapa', 'bloques'));
    }

    public function bloques($id)
    {
        $mapa = $this->obtenerMapa($id);
        $estadoActual = $this->obtenerEstadoActual(Carbon::now());
        $bloques = $this->prepararBloques($mapa, $estadoActual);

        return response()->json($bloques);
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

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $reservasActivas, $planificacionesProximas) {
            $idEspacio = $bloque->id_espacio;
            $estaOcupado = $planificacionesActivas->contains('id_espacio', $idEspacio);
            $estaReservado = $reservasActivas->contains('id_espacio', $idEspacio);
            $tieneClaseProxima = $planificacionesProximas->contains('id_espacio', $idEspacio);

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $this->determinarEstado($estaOcupado, $estaReservado, $tieneClaseProxima),
                'detalles' => $this->prepararDetallesBloque(
                    $bloque->espacio,
                    $estaOcupado ? $planificacionesActivas->firstWhere('id_espacio', $idEspacio) : null,
                    $estaReservado ? $reservasActivas->firstWhere('id_espacio', $idEspacio) : null,
                    $tieneClaseProxima ? $planificacionesProximas->firstWhere('id_espacio', $idEspacio) : null
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

        return Planificacion_Asignatura::with(['horario', 'asignatura.profesor', 'modulo', 'espacio'])
            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();
    }

    private function obtenerReservasActivas(Mapa $mapa, array $estadoActual)
    {
        return Reserva::where('fecha_reserva', $estadoActual['fecha'])
            ->where('hora', $estadoActual['hora'])
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
        if ($estaOcupado)
            return 'red';
        if ($tieneClaseProxima)
            return 'blue';
        return 'green';
    }

    private function prepararDetallesBloque($espacio, $planificacion, $reserva, $planificacionProxima): array
    {
        $detalles = [
            'tipo_espacio' => $espacio->tipo_espacio,
            'puestos_disponibles' => $espacio->puestos_disponibles,
            'planificacion' => null,
            'reserva' => null,
            'planificacion_proxima' => null
        ];
        \Log::info('DEBUG Profesor:', [
            'asignatura_id' => $planificacion?->asignatura?->id_asignatura,
            'profesor' => $planificacion?->asignatura?->profesor,
        ]);
        if ($planificacion) {
            $detalles['planificacion'] = [
                'asignatura' => $planificacion->asignatura->nombre_asignatura,
                'profesor' => ucwords($planificacion->asignatura->profesor->name ?? 'No asignado'),
                'modulos' => $planificacion->asignatura->planificaciones()
                    ->where('id_espacio', $espacio->id_espacio)
                    ->with('modulo')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'dia' => $plan->modulo->dia,
                            'hora_inicio' => $plan->modulo->hora_inicio,
                            'hora_termino' => $plan->modulo->hora_termino
                        ];
                    })->toArray()
            ];
        }

        if ($planificacionProxima) {
            $detalles['planificacion_proxima'] = [
                'asignatura' => $planificacionProxima->asignatura->nombre_asignatura,
                'profesor' => $planificacionProxima->asignatura->profesor->nombre ?? 'No asignado',
                'hora_inicio' => $planificacionProxima->modulo->hora_inicio,
                'hora_termino' => $planificacionProxima->modulo->hora_termino
            ];
        }

        if ($reserva) {
            $detalles['reserva'] = [
                'fecha_reserva' => $reserva->fecha_reserva,
                'hora' => $reserva->hora
            ];
        }

        return $detalles;
    }

}