<?php

namespace App\Services;

use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * OccupancyService - Servicio centralizado para cálculos de ocupación de espacios
 * 
 * Centraliza toda la lógica de ocupación que antes estaba dispersa entre:
 * - DashboardController
 * - ModulosActualesTable
 * - Varias vistas show.blade.php
 * 
 * Garantiza coherencia en los datos de ocupación en toda la aplicación
 */
class OccupancyService
{
    /**
     * Obtiene espacios filtrados por facultad y piso
     * 
     * @param string|null $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getEspaciosQuery($facultad = null, $piso = null)
    {
        $query = Espacio::query();

        if ($facultad) {
            $query->whereHas('piso', function ($q) use ($facultad) {
                $q->where('id_facultad', $facultad);
            });
        }

        if ($piso) {
            $query->whereHas('piso', function ($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }

        return $query;
    }

    /**
     * Calcula módulos reales de uso basado en hora de inicio y salida
     * Considera mínimo 10 minutos para contar como válido
     * 
     * @param string|null $horaInicio Hora de inicio (formato H:i:s o H:i)
     * @param string|null $horaSalida Hora de salida real
     * @param int|null $modulosTeoricos Campo modulos de la reserva (fallback)
     * @return float Número de módulos calculados
     */
    public function calcularModulosReales($horaInicio, $horaSalida, $modulosTeoricos = null)
    {
        if (!$horaSalida || !$horaInicio) {
            return $modulosTeoricos ?? 1;
        }

        try {
            $inicio = Carbon::parse($horaInicio);
            $fin = Carbon::parse($horaSalida);

            if ($fin->lt($inicio)) {
                return $modulosTeoricos ?? 1;
            }

            $minutosReales = $inicio->diffInMinutes($fin);

            if ($minutosReales < 10) {
                return 0;
            }

            $modulosCalculados = $minutosReales / 50;
            if ($modulosCalculados > 15) {
                return $modulosTeoricos ?? 1;
            }

            $parteDecimal = $modulosCalculados - floor($modulosCalculados);

            if ($parteDecimal < 0.3) {
                return floor($modulosCalculados);
            } elseif ($parteDecimal < 0.7) {
                return floor($modulosCalculados) + 0.5;
            } else {
                return ceil($modulosCalculados);
            }
        } catch (\Exception $e) {
            return $modulosTeoricos ?? 1;
        }
    }

    /**
     * Verifica si una hora está en un turno específico
     * Diurno: 08:00 - 19:00
     * Vespertino: 19:00 - 23:00
     * 
     * @param string $hora Hora en formato H:i:s o H:i
     * @param string|null $turno 'diurno', 'vespertino' o null para todos
     * @return bool
     */
    public function esTurno($hora, $turno = null)
    {
        if ($turno === null) {
            return true;
        }

        $horaInt = (int) substr($hora, 0, 2);

        if ($turno === 'diurno') {
            return $horaInt >= 8 && $horaInt < 19;
        } elseif ($turno === 'vespertino') {
            return $horaInt >= 19 && $horaInt < 23;
        }

        return true;
    }

    /**
     * Calcula horas disponibles para un turno
     * 
     * @param string|null $turno 'diurno', 'vespertino' o null
     * @param Carbon|null $fecha Fecha para determinar si es sábado
     * @return int|float
     */
    public function horasPorTurno($turno = null, $fecha = null)
    {
        $esSabado = $fecha ? $fecha->isSaturday() : false;

        if ($esSabado) {
            if ($turno === 'diurno') {
                return 5;  // Sábado: 08:00 - 13:00 = 5 horas
            } elseif ($turno === 'vespertino') {
                return 0;  // Sábado: no hay clases vespertinas
            }
            return 5;
        }

        if ($turno === 'diurno') {
            return 11;  // 08:00 - 19:00 = 11 horas
        } elseif ($turno === 'vespertino') {
            return 4;   // 19:00 - 23:00 = 4 horas
        }

        return 15;  // Total: 08:00 - 23:00 = 15 horas
    }

    /**
     * Obtiene salas ocupadas y libres para una facultad y piso
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso (opcional)
     * @param string|null $turno Filtro por turno (opcional)
     * @return array ['ocupadas' => count, 'libres' => count]
     */
    public function obtenerSalasOcupadas($facultad, $piso = null, $turno = null)
    {
        $espacios = $this->getEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->get();

        $ocupadas = 0;
        $libres = 0;

        foreach ($espacios as $espacio) {
            // Verificar si hay una reserva activa ahora
            $horaActual = Carbon::now()->format('H:i:s');
            $fechaActual = Carbon::now()->format('Y-m-d');

            $reservaActiva = Reserva::where('id_espacio', $espacio->id_espacio)
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->where('hora', '<=', $horaActual)
                ->where(function ($q) {
                    $q->whereNull('hora_salida')
                        ->orWhereRaw('hora_salida > ?', [Carbon::now()->format('H:i:s')]);
                })
                ->exists();

            if ($reservaActiva) {
                $ocupadas++;
            } else {
                $libres++;
            }
        }

        return [
            'ocupadas' => $ocupadas,
            'libres' => $libres
        ];
    }

    /**
     * Calcula ocupación promedio por hora
     * 
     * @param Carbon $inicio Fecha inicial
     * @param Carbon $fin Fecha final
     * @param string|null $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @param string|null $turno Filtro por turno
     * @return float Porcentaje de ocupación
     */
    public function calcularOcupacionPromedioHora($inicio, $fin, $facultad = null, $piso = null, $turno = null)
    {
        if ($turno === null) {
            $diurno = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'diurno');
            $vespertino = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'vespertino');
            return round(($diurno + $vespertino) / 2, 2);
        }

        $totalEspacios = $this->getEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->count();

        if ($totalEspacios === 0) {
            return 0;
        }

        $horaInicioTurno = ($turno === 'vespertino') ? 19 : 8;
        $horaFinTurno = ($turno === 'diurno') ? 19 : 23;

        $query = Reserva::select(
            DB::raw('DATE(fecha_reserva) as fecha'),
            DB::raw('HOUR(hora) as hora_dia'),
            DB::raw('COUNT(*) as total_reservas')
        )
            ->whereBetween('fecha_reserva', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereRaw('DAYOFWEEK(fecha_reserva) BETWEEN 2 AND 7')
            ->whereRaw('HOUR(hora) >= ?', [$horaInicioTurno])
            ->whereRaw('HOUR(hora) < ?', [$horaFinTurno])
            ->whereHas('espacio', function ($q) use ($facultad, $piso) {
                if ($piso) {
                    $q->whereHas('piso', function ($subQ) use ($facultad, $piso) {
                        $subQ->where('id_facultad', $facultad);
                        $subQ->where('numero_piso', $piso);
                    });
                } elseif ($facultad) {
                    $q->whereHas('piso', function ($subQ) use ($facultad) {
                        $subQ->where('id_facultad', $facultad);
                    });
                }
                $q->where('tipo_espacio', 'Sala de Clases');
            })
            ->groupBy('fecha', 'hora_dia')
            ->get();

        if ($query->isEmpty()) {
            return 0;
        }

        $maximosPorHora = [];

        foreach ($query as $registro) {
            $fecha = Carbon::parse($registro->fecha);
            $hora = $registro->hora_dia;

            if ($fecha->isSaturday() && $hora >= 13) {
                continue;
            }

            $key = $hora;
            if (!isset($maximosPorHora[$key])) {
                $maximosPorHora[$key] = 0;
            }
            $maximosPorHora[$key] = max($maximosPorHora[$key], $registro->total_reservas);
        }

        $ocupacionPromedio = 0;
        $horasCont = 0;

        foreach ($maximosPorHora as $totalReservas) {
            $ocupacionPromedio += ($totalReservas / $totalEspacios) * 100;
            $horasCont++;
        }

        if ($horasCont === 0) {
            return 0;
        }

        return round($ocupacionPromedio / $horasCont, 2);
    }

    /**
     * Calcula ocupación semanal
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @param string|null $turno Filtro por turno
     * @return float Porcentaje de ocupación
     */
    public function calcularOcupacionSemanal($facultad, $piso = null, $turno = null)
    {
        $inicio = Carbon::now()->startOfWeek();
        $fin = Carbon::now()->endOfWeek();

        return $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, $turno);
    }

    /**
     * Calcula ocupación mensual
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @param string|null $turno Filtro por turno
     * @return float Porcentaje de ocupación
     */
    public function calcularOcupacionMensual($facultad, $piso = null, $turno = null)
    {
        $inicio = Carbon::now()->startOfMonth();
        $fin = Carbon::now()->endOfMonth();

        return $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, $turno);
    }

    /**
     * Obtiene el estado actual de ocupación de un espacio específico
     * 
     * @param string $idEspacio ID del espacio
     * @return array ['ocupado' => bool, 'clase' => array|null, 'finalizaEn' => string|null]
     */
    public function obtenerEstadoEspacio($idEspacio)
    {
        $horaActual = Carbon::now()->format('H:i:s');
        $fechaActual = Carbon::now()->format('Y-m-d');

        $reservaActiva = Reserva::where('id_espacio', $idEspacio)
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '<=', $horaActual)
            ->where(function ($q) {
                $q->whereNull('hora_salida')
                    ->orWhereRaw('hora_salida > ?', [Carbon::now()->format('H:i:s')]);
            })
            ->with(['asignatura', 'profesor', 'espacio'])
            ->first();

        if (!$reservaActiva) {
            return [
                'ocupado' => false,
                'clase' => null,
                'finalizaEn' => null
            ];
        }

        return [
            'ocupado' => true,
            'clase' => [
                'asignatura' => $reservaActiva->asignatura?->nombre_asignatura,
                'profesor' => $reservaActiva->profesor?->nombre_profesor,
                'modulos' => $reservaActiva->modulos,
                'horaInicio' => $reservaActiva->hora
            ],
            'finalizaEn' => $reservaActiva->hora_salida ?? 'Por determinar'
        ];
    }

    /**
     * Obtiene todas las salas ocupadas en este momento
     * 
     * @param string|null $facultad ID de facultad (opcional)
     * @param int|null $piso Número de piso (opcional)
     * @return \Illuminate\Support\Collection
     */
    public function obtenerSalasOcupadasAhora($facultad = null, $piso = null)
    {
        $horaActual = Carbon::now()->format('H:i:s');
        $fechaActual = Carbon::now()->format('Y-m-d');

        return Reserva::where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '<=', $horaActual)
            ->where(function ($q) {
                $q->whereNull('hora_salida')
                    ->orWhereRaw('hora_salida > ?', [Carbon::now()->format('H:i:s')]);
            })
            ->whereHas('espacio', function ($q) use ($facultad, $piso) {
                if ($facultad || $piso) {
                    $q->whereHas('piso', function ($subQ) use ($facultad, $piso) {
                        if ($facultad) {
                            $subQ->where('id_facultad', $facultad);
                        }
                        if ($piso) {
                            $subQ->where('numero_piso', $piso);
                        }
                    });
                }
            })
            ->with(['espacio', 'asignatura', 'profesor'])
            ->get();
    }

    /**
     * Obtiene espacios ocupados/libres totales (incluyendo todos los tipos)
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return array ['ocupadas' => count, 'libres' => count]
     */
    public function obtenerEspaciosOcupadosTotal($facultad, $piso = null)
    {
        $espaciosQuery = $this->getEspaciosQuery($facultad, $piso);
        $totalEspacios = (clone $espaciosQuery)->count();

        $idsEspaciosValidos = (clone $espaciosQuery)->pluck('id_espacio');

        $ocupados = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereIn('id_espacio', $idsEspaciosValidos)
            ->select('id_espacio')
            ->groupBy('id_espacio')
            ->get()
            ->count();

        $libres = $totalEspacios - $ocupados;

        return [
            'ocupadas' => $ocupados,
            'libres' => $libres
        ];
    }

    /**
     * Obtiene uso de espacios por día de la semana
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return array Datos de uso por día
     */
    public function obtenerUsoPorDia($facultad, $piso = null)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $usoPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);

            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($q) use ($piso, $facultad) {
                    if ($piso) {
                        $q->whereHas('piso', function ($subQ) use ($piso, $facultad) {
                            $subQ->where('id_facultad', $facultad);
                            $subQ->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $q->whereHas('piso', function ($subQ) use ($facultad) {
                            $subQ->where('id_facultad', $facultad);
                        });
                    }
                    $q->where('tipo_espacio', 'Sala de Clases');
                })
                ->count();

            $usoPorDia[] = [
                'dia' => $diasSemana[$i],
                'cantidad' => $cantidadReservas
            ];
        }

        return $usoPorDia;
    }

    /**
     * Obtiene salas utilizadas por día
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return array Datos de salas por día
     */
    public function obtenerSalasUtilizadasPorDia($facultad, $piso = null)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $salasUtilizadas = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);

            $salasCount = Reserva::selectRaw('COUNT(DISTINCT id_espacio) as cantidad')
                ->whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($q) use ($piso, $facultad) {
                    if ($piso) {
                        $q->whereHas('piso', function ($subQ) use ($piso, $facultad) {
                            $subQ->where('id_facultad', $facultad);
                            $subQ->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $q->whereHas('piso', function ($subQ) use ($facultad) {
                            $subQ->where('id_facultad', $facultad);
                        });
                    }
                })
                ->first();

            $salasUtilizadas[] = [
                'dia' => $diasSemana[$i],
                'cantidad' => $salasCount?->cantidad ?? 0
            ];
        }

        return $salasUtilizadas;
    }

    /**
     * Obtiene ocupación por día
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return array Datos de ocupación por día
     */
    public function obtenerOcupacionPorDia($facultad, $piso = null)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $ocupacionPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $ocupacion = $this->calcularOcupacionPromedioHora(
                $dia->copy()->startOfDay(),
                $dia->copy()->endOfDay(),
                $facultad,
                $piso
            );

            $ocupacionPorDia[] = [
                'dia' => $diasSemana[$i],
                'ocupacion' => $ocupacion
            ];
        }

        return $ocupacionPorDia;
    }

    /**
     * Obtiene ocupación por turno
     * 
     * @param string $facultad ID de facultad
     * @param int|null $piso Número de piso
     * @return array Datos de ocupación por turno
     */
    public function obtenerOcupacionPorTurno($facultad, $piso = null)
    {
        return [
            'diurno' => $this->calcularOcupacionSemanal($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularOcupacionSemanal($facultad, $piso, 'vespertino'),
        ];
    }

    /**
     * Verifica coherencia de datos de ocupación
     * 
     * @param string $idEspacio ID del espacio
     * @return array Reporte de coherencia
     */
    public function verificarCoherenciaEspacio($idEspacio)
    {
        $reporte = [
            'espacio_id' => $idEspacio,
            'coherente' => true,
            'problemas' => [],
            'estado_actual' => $this->obtenerEstadoEspacio($idEspacio),
            'reservas_activas' => 0,
            'reservas_finalizadas_sin_hora_salida' => 0
        ];

        $horaActual = Carbon::now()->format('H:i:s');
        $fechaActual = Carbon::now()->format('Y-m-d');

        // Verificar múltiples reservas activas (problema)
        $reservasActivas = Reserva::where('id_espacio', $idEspacio)
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '<=', $horaActual)
            ->where(function ($q) {
                $q->whereNull('hora_salida')
                    ->orWhereRaw('hora_salida > ?', [Carbon::now()->format('H:i:s')]);
            })
            ->count();

        $reporte['reservas_activas'] = $reservasActivas;

        if ($reservasActivas > 1) {
            $reporte['coherente'] = false;
            $reporte['problemas'][] = "Múltiples reservas activas simultáneamente: {$reservasActivas}";
        }

        // Verificar reservas finalizadas sin hora_salida
        $reservasFinalizadasSinSalida = Reserva::where('id_espacio', $idEspacio)
            ->where('estado', 'finalizada')
            ->whereNull('hora_salida')
            ->count();

        $reporte['reservas_finalizadas_sin_hora_salida'] = $reservasFinalizadasSinSalida;

        if ($reservasFinalizadasSinSalida > 0) {
            $reporte['coherente'] = false;
            $reporte['problemas'][] = "Reservas finalizadas sin hora_salida registrada: {$reservasFinalizadasSinSalida}";
        }

        return $reporte;
    }
}
