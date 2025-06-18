<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\PlanificacionAsignatura;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Obtener datos para los KPIs
        $ocupacionSemanal = $this->calcularOcupacionSemanal();
        $ocupacionDiaria = $this->calcularOcupacionDiaria();
        $ocupacionMensual = $this->calcularOcupacionMensual();
        $usuariosSinEscaneo = $this->obtenerUsuariosSinEscaneo();
        $horasUtilizadas = $this->calcularHorasUtilizadas();
        $salasOcupadas = $this->obtenerSalasOcupadas();

        // Obtener datos para los gráficos
        $usoPorDia = $this->obtenerUsoPorDia();
        $topSalas = $this->obtenerTopSalas();
        $topAsignaturas = $this->obtenerTopAsignaturas();
        $comparativaTipos = $this->obtenerComparativaTipos();
        $evolucionMensual = $this->obtenerEvolucionMensual();

        // Obtener datos para las tablas
        $reservasCanceladas = $this->obtenerReservasCanceladas();
        $horariosPorDia = $this->obtenerHorariosPorDia();

        return view('layouts.dashboard', compact(
            'ocupacionSemanal',
            'ocupacionDiaria',
            'ocupacionMensual',
            'usuariosSinEscaneo',
            'horasUtilizadas',
            'salasOcupadas',
            'usoPorDia',
            'topSalas',
            'topAsignaturas',
            'comparativaTipos',
            'evolucionMensual',
            'reservasCanceladas',
            'horariosPorDia'
        ));
    }

    private function calcularOcupacionSemanal()
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $totalHoras = 40; // 8 horas * 5 días
        $horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->where('estado', 'activa')
            ->count();

        return round(($horasOcupadas / $totalHoras) * 100, 2);
    }

    private function calcularOcupacionDiaria()
    {
        $hoy = Carbon::today();
        $diasSemana = ['L', 'M', 'X', 'J', 'V'];
        $ocupacion = [];

        foreach ($diasSemana as $dia) {
            $ocupacion[$dia] = Reserva::whereDate('fecha_reserva', $hoy)
                ->where('estado', 'activa')
                ->count() * 12.5; // 100% / 8 horas
        }

        return $ocupacion;
    }

    private function calcularOcupacionMensual()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        $totalHoras = 160; // 8 horas * 20 días laborables
        $horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
            ->where('estado', 'activa')
            ->count();

        return round(($horasOcupadas / $totalHoras) * 100, 2);
    }

    private function obtenerUsuariosSinEscaneo()
    {
        $hoy = Carbon::today();
        return User::whereDoesntHave('reservas', function($query) use ($hoy) {
            $query->whereDate('fecha_reserva', $hoy);
        })->count();
    }

    private function calcularHorasUtilizadas()
    {
        $hoy = Carbon::today();
        $horasUtilizadas = Reserva::whereDate('fecha_reserva', $hoy)
            ->where('estado', 'activa')
            ->count();

        return [
            'utilizadas' => $horasUtilizadas,
            'disponibles' => 40 // 8 horas * 5 días
        ];
    }

    private function obtenerSalasOcupadas()
    {
        $hoy = Carbon::today();
        $totalEspacios = Espacio::count();
        $espaciosOcupados = Espacio::whereHas('reservas', function($query) use ($hoy) {
            $query->whereDate('fecha_reserva', $hoy)
                ->where('estado', 'activa');
        })->count();

        return [
            'ocupadas' => $espaciosOcupados,
            'libres' => $totalEspacios - $espaciosOcupados
        ];
    }

    private function obtenerUsoPorDia()
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $diasSemana = [];
        $usoPorDia = [];

        for ($i = 0; $i < 5; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia->format('D');
            $usoPorDia[] = Reserva::whereDate('fecha_reserva', $dia)
                ->where('estado', 'activa')
                ->count();
        }

        return [
            'labels' => $diasSemana,
            'data' => $usoPorDia
        ];
    }

    private function obtenerTopSalas()
    {
        return Espacio::withCount(['reservas' => function($query) {
            $query->where('estado', 'activa');
        }])
        ->orderBy('reservas_count', 'desc')
        ->take(3)
        ->get()
        ->map(function($espacio) {
            return [
                'nombre' => $espacio->nombre_espacio,
                'uso' => $espacio->reservas_count
            ];
        });
    }

    private function obtenerTopAsignaturas()
    {
        return Asignatura::withCount(['planificaciones' => function($query) {
            $query->whereHas('reservas', function($q) {
                $q->where('estado', 'activa');
            });
        }])
        ->orderBy('planificaciones_count', 'desc')
        ->take(3)
        ->get()
        ->map(function($asignatura) {
            return [
                'nombre' => $asignatura->nombre_asignatura,
                'uso' => $asignatura->planificaciones_count
            ];
        });
    }

    private function obtenerComparativaTipos()
    {
        return Espacio::select('tipo_espacio', DB::raw('count(*) as total'))
            ->groupBy('tipo_espacio')
            ->get()
            ->map(function($tipo) {
                return [
                    'tipo' => $tipo->tipo_espacio,
                    'total' => $tipo->total
                ];
            });
    }

    private function obtenerEvolucionMensual()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $dias = [];
        $ocupacion = [];

        for ($i = 0; $i < 30; $i++) {
            $dia = $inicioMes->copy()->addDays($i);
            $dias[] = $dia->format('d/m');
            $ocupacion[] = Reserva::whereDate('fecha_reserva', $dia)
                ->where('estado', 'activa')
                ->count() * 12.5; // Convertir a porcentaje
        }

        return [
            'labels' => $dias,
            'data' => $ocupacion
        ];
    }

    private function obtenerReservasCanceladas()
    {
        return Reserva::with(['user', 'espacio'])
            ->where('estado', 'finalizada')
            ->whereDate('fecha_reserva', Carbon::today())
            ->get()
            ->map(function($reserva) {
                return [
                    'usuario' => $reserva->user->name,
                    'espacio' => $reserva->espacio->nombre_espacio,
                    'fecha' => $reserva->fecha_reserva,
                    'motivo' => 'Cancelada'
                ];
            });
    }

    private function obtenerHorariosPorDia()
    {
        return Reserva::with(['user', 'espacio'])
            ->whereDate('fecha_reserva', Carbon::today())
            ->where('estado', 'activa')
            ->get()
            ->map(function($reserva) {
                $planificacion = PlanificacionAsignatura::where('id_espacio', $reserva->id_espacio)
                    ->where('id_horario', function($query) use ($reserva) {
                        $query->select('id_horario')
                            ->from('horarios')
                            ->where('run', $reserva->run)
                            ->first();
                    })
                    ->first();

                return [
                    'modulo' => $reserva->hora,
                    'dia' => Carbon::parse($reserva->fecha_reserva)->format('l'),
                    'asignatura' => $planificacion ? $planificacion->asignatura->nombre_asignatura : 'N/A',
                    'espacio' => $reserva->espacio->nombre_espacio,
                    'usuario' => $reserva->user->name
                ];
            });
    }
} 