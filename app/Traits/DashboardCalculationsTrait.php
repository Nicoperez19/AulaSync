<?php

namespace App\Traits;

use App\Models\Modulo;
use App\Models\Tenant;
use App\Models\ClaseNoRealizada;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Models\Espacio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\DayCodeHelper;
use App\Helpers\SemesterHelper;

trait DashboardCalculationsTrait
{
    private function calcularModulosReales($horaInicio, $horaSalida, $modulosTeoricos = null)
    {
        return $this->calcularModulosRealesPublic($horaInicio, $horaSalida, $modulosTeoricos);
    }

    private function esTurno($hora, $turno = null): bool
    {
        if ($turno === null) {
            return true;  // Sin filtro de turno
        }

        $horaInt = (int) substr($hora, 0, 2);

        if ($turno === 'diurno') {
            return $horaInt >= 8 && $horaInt < 19;
        } elseif ($turno === 'vespertino') {
            return $horaInt >= 19 && $horaInt < 23;
        }

        return true;
    }

    private function horasPorTurno($turno = null, $fecha = null): float
    {
        // Verificar si es sábado (clases solo hasta 13:00)
        $esSabado = $fecha ? $fecha->isSaturday() : false;

        if ($esSabado) {
            if ($turno === 'diurno') {
                return 5;  // Sábado: 08:00 - 13:00 = 5 horas
            } elseif ($turno === 'vespertino') {
                return 0;  // Sábado: no hay clases vespertinas
            }
            return 5;  // Sábado total: 08:00 - 13:00 = 5 horas
        }

        // Días normales (lunes a viernes)
        if ($turno === 'diurno') {
            return 11;  // 08:00 - 19:00 = 11 horas
        } elseif ($turno === 'vespertino') {
            return 4;  // 19:00 - 23:00 = 4 horas
        }

        return 15;  // Total: 08:00 - 23:00 = 15 horas
    }

    private function calcularHorasDesdePlanificaciones($inicio, $fin, $piso = null, $tipoEspacio = null, $turno = null): float
    {
        $periodo = SemesterHelper::getCurrentPeriod();
        $horasTotales = 0;

        // Obtener las planificaciones del período actual
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio'])
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->whereHas('espacio', function ($query) use ($piso, $tipoEspacio) {
                if ($piso) {
                    $query->whereHas('piso', function ($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
                // Filtrar por tipo de espacio: si se especifica
                if ($tipoEspacio) {
                    $query->where('tipo_espacio', $tipoEspacio);
                }
            })
            ->get();

        // Iterar por cada día en el rango
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            // Solo contar días laborales (lunes a viernes) y sábados
            if (!$fecha->isWeekday() && !$fecha->isSaturday()) {
                continue;
            }

            $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));

            // Filtrar planificaciones para este día
            $planificacionesDia = $planificaciones->filter(function ($plan) use ($diaSemana) {
                return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
            });

            // Sumar horas de cada planificación
            foreach ($planificacionesDia as $plan) {
                if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                    // Filtrar por turno si está especificado
                    if (!$this->esTurno($plan->modulo->hora_inicio, $turno)) {
                        continue;
                    }

                    $inicio_modulo = Carbon::parse($plan->modulo->hora_inicio);
                    $fin_modulo = Carbon::parse($plan->modulo->hora_termino);
                    $horasTotales += $inicio_modulo->diffInHours($fin_modulo, true);
                }
            }
        }

        return $horasTotales;
    }

    private function calcularOcupacionPromedioHora($inicio, $fin, $facultad = null, $piso = null, $turno = null): float
    {
        // Si turno es null, calcular como promedio de diurno + vespertino
        if ($turno === null) {
            $diurno = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'diurno');
            $vespertino = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'vespertino');

            // Promedio simple de diurno y vespertino
            $resultado = ($diurno + $vespertino) / 2;

            return round($resultado, 2);
        }

        $totalEspacios = $this
            ->obtenerEspaciosQuery($facultad, $piso)
            ->count();

        if ($totalEspacios === 0) {
            return 0;
        }

        // OPTIMIZADO: Usar una sola query con agrupación en lugar de iterar por cada hora
        // Determinar rango de horas según turno
        $horaInicioTurno = ($turno === 'vespertino') ? 19 : 8;
        $horaFinTurno = ($turno === 'diurno') ? 19 : 23;

        // Query optimizada: obtener conteo de reservas agrupadas por fecha y hora
        $query = Reserva::select(
            DB::raw('DATE(fecha_reserva) as fecha'),
            DB::raw('HOUR(hora) as hora_dia'),
            DB::raw('COUNT(*) as total_reservas')
        )
            ->whereBetween('fecha_reserva', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereRaw('DAYOFWEEK(fecha_reserva) BETWEEN 2 AND 7')  // Lunes (2) a Sábado (7)
            ->whereRaw('HOUR(hora) >= ?', [$horaInicioTurno])
            ->whereRaw('HOUR(hora) < ?', [$horaFinTurno])
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                        $q->where('id_facultad', $facultad);
                        $q->where('numero_piso', $piso);
                    });
                } elseif ($facultad) {
                    $query->whereHas('piso', function ($q) use ($facultad) {
                        $q->where('id_facultad', $facultad);
                    });
                }
            })
            ->groupBy('fecha', 'hora_dia');

        // Para sábados, filtrar horas solo hasta las 13
        // Esto se maneja después al procesar resultados

        $reservasPorHora = $query->get();

        // Si no hay reservas, retornar 0
        if ($reservasPorHora->isEmpty()) {
            return 0;
        }

        // Procesar resultados: agrupar por hora y calcular máximos
        $maximosPorHora = [];

        foreach ($reservasPorHora as $registro) {
            $fecha = Carbon::parse($registro->fecha);
            $hora = $registro->hora_dia;

            // Filtrar sábados después de las 13 horas
            if ($fecha->isSaturday() && $hora >= 13) {
                continue;
            }

            // Calcular porcentaje de ocupación para esta hora
            $porcentajeOcupacion = min(($registro->total_reservas / $totalEspacios) * 100, 100);

            // Guardar el máximo por cada hora del día
            if (!isset($maximosPorHora[$hora]) || $porcentajeOcupacion > $maximosPorHora[$hora]) {
                $maximosPorHora[$hora] = $porcentajeOcupacion;
            }
        }

        // Calcular promedio de los máximos por hora
        if (count($maximosPorHora) === 0) {
            return 0;
        }

        $promedioTotal = array_sum($maximosPorHora) / count($maximosPorHora);
        $resultado = round($promedioTotal, 2);

        return $resultado;
    }


    private function calcularOcupacionSemanal($facultad, $piso, $turno = null): float
    {
        // Lunes a sábado de la semana actual
        $inicioSemana = Carbon::now()->startOfWeek();

        // Usar sábado como fin de semana (no domingo)
        $finSemana = $inicioSemana->copy()->addDays(5);  // Sábado

        return $this->calcularOcupacionPromedioHora($inicioSemana, $finSemana, $facultad, $piso, $turno);
    }


    private function calcularOcupacionDiaria($facultad, $piso): array
    {
        $hoy = Carbon::today();
        $diaSemana = $hoy->format('l');

        $modulos = Modulo::where('dia', $diaSemana)
            ->orderBy('hora_inicio')
            ->get();

        $ocupacion = [];

        foreach ($modulos as $modulo) {
            $espaciosOcupados = Planificacion_Asignatura::where('id_modulo', $modulo->id_modulo)
                ->whereHas('espacio', function ($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->whereHas('espacio', function ($query) {
                    $query->where('estado', 'Ocupado');
                })
                ->count();

            $totalEspacios = $this
                ->obtenerEspaciosQuery($facultad, $piso)
                ->count();

            $porcentaje = $totalEspacios > 0 ? ($espaciosOcupados / $totalEspacios) * 100 : 0;

            $ocupacion[$modulo->hora_inicio] = round($porcentaje, 2);
        }

        return $ocupacion;
    }


    private function calcularOcupacionMensual($facultad, $piso, $turno = null): float
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        return $this->calcularOcupacionPromedioHora($inicioMes, $finMes, $facultad, $piso, $turno);
    }


    private function obtenerUsuariosSinEscaneo($facultad, $piso): int
    {
        $hoy = Carbon::today();

        // Obtener los espacios de la facultad y piso especificados
        $espacios = $this
            ->obtenerEspaciosQuery($facultad, $piso)
            ->pluck('id_espacio');

        // Obtener profesores que no tienen reservas hoy en los espacios especificados
        return Profesor::whereDoesntHave('reservas', function ($query) use ($hoy, $espacios) {
            $query
                ->whereDate('fecha_reserva', $hoy)
                ->whereIn('id_espacio', $espacios);
        })->count();
    }


    private function calcularHorasUtilizadas($facultad, $piso, $turno = null): array
    {
        $hoy = Carbon::today();

        // Obtener total de espacios para calcular horas disponibles correctamente
        $totalEspacios = $this
            ->obtenerEspaciosQuery($facultad, $piso)
            ->count();
        $horasPorDia = $this->horasPorTurno($turno, $hoy);
        $totalHorasDisponibles = $totalEspacios * $horasPorDia;

        // Calcular horas REALES utilizadas (no solo contar reservas)
        $reservas = Reserva::whereDate('fecha_reserva', $hoy)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereHas('espacio', function ($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function ($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get();

        $horasRealmenteUtilizadas = $reservas->sum(function ($reserva) use ($turno) {
            if ($reserva->hora && $reserva->hora_salida) {
                // Filtrar por turno si está especificado
                if ($turno && !$this->esTurno($reserva->hora, $turno)) {
                    return 0;
                }

                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true);  // true para incluir decimales
            }
            // Si no hay hora_salida, verificar turno y asumir 1 módulo de 50 minutos
            if ($reserva->hora && $turno && !$this->esTurno($reserva->hora, $turno)) {
                return 0;
            }
            return 0.83;  // 50/60 horas
        });

        return [
            'utilizadas' => round($horasRealmenteUtilizadas, 2),
            'disponibles' => $totalHorasDisponibles
        ];
    }


    private function obtenerSalasOcupadas($facultad, $piso, $turno = null): array
    {
        // Contar todos los espacios para el KPI de % Ocupación
        $espaciosQuery = $this
            ->obtenerEspaciosQuery($facultad, $piso);

        $totalEspacios = (clone $espaciosQuery)->count();

        // Obtener IDs de todos los espacios
        $idsEspaciosValidos = (clone $espaciosQuery)->pluck('id_espacio');

        // CORRECCIÓN CRÍTICA: Contar espacios ocupados basándose en RESERVAS ACTIVAS del día actual
        $reservasActivasQuery = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereIn('id_espacio', $idsEspaciosValidos);

        // Si se especifica turno, filtrar por hora actual
        if ($turno !== null) {
            $horaActual = Carbon::now()->format('H:i:s');

            // Solo contar ocupados si la hora actual está en el turno solicitado
            if ($this->esTurno($horaActual, $turno)) {
                $ocupados = (clone $reservasActivasQuery)
                    ->select('id_espacio')
                    ->groupBy('id_espacio')
                    ->get()
                    ->count();
            } else {
                // Si no estamos en el turno, todas están libres
                $ocupados = 0;
            }
        } else {
            // Sin filtro de turno: contar todas las reservas activas
            $ocupados = (clone $reservasActivasQuery)
                ->select('id_espacio')
                ->groupBy('id_espacio')
                ->get()
                ->count();
        }

        $libres = $totalEspacios - $ocupados;

        return [
            'ocupadas' => $ocupados,
            'libres' => $libres,
            'modulo_actual' => null
        ];
    }

    private function obtenerEspaciosOcupadosTotal($facultad, $piso): array
    {
        // Obtener TODOS los espacios (incluyendo laboratorios, talleres, etc.)
        $espaciosQuery = $this->obtenerEspaciosQuery($facultad, $piso);
        $totalEspacios = (clone $espaciosQuery)->count();

        // Obtener IDs de todos los espacios
        $idsEspaciosValidos = (clone $espaciosQuery)->pluck('id_espacio');

        // Contar espacios ocupados de TODOS los tipos basándose en reservas activas
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
            'libres' => $libres,
            'modulo_actual' => null
        ];
    }


    private function obtenerUsoPorDia($facultad, $piso): array
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $usoPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);

            // Contar CANTIDAD DE RESERVAS (volver a como era antes)
            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->count();

            $usoPorDia[$diasSemana[$i]] = $cantidadReservas;
        }

        return [
            'datos' => $usoPorDia,
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }


    private function obtenerSalasUtilizadasPorDia($facultad, $piso, $fechaInicio = null, $fechaFin = null): array
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();

        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }

        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);

        // Obtener todas las salas distintas en el período
        $salas = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->orderBy('id_espacio')
            ->get();

        $dataPorSala = [];

        // Para cada sala, calcular módulos REALES por día
        foreach ($salas as $sala) {
            $modulosPorDia = [];

            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];

                // Obtener reservas del día
                $reservasDia = Reserva::whereDate('fecha_reserva', $dia)
                    ->where('id_espacio', $sala->id_espacio)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereNotNull('hora')
                    ->get();

                $cantidadModulos = 0;
                foreach ($reservasDia as $reserva) {
                    // Usar el método inteligente de cálculo
                    $cantidadModulos += $this->calcularModulosReales(
                        $reserva->hora,
                        $reserva->hora_salida,
                        $reserva->modulos
                    );
                }

                $modulosPorDia[] = round($cantidadModulos, 2);
            }

            // Solo incluir salas que tengan al menos algo de uso
            if (array_sum($modulosPorDia) > 0) {
                $dataPorSala[] = [
                    'sala' => $sala->id_espacio,
                    'datos' => $modulosPorDia
                ];
            }
        }

        return [
            'salas' => $dataPorSala,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }


    private function obtenerOcupacionPorDia($facultad, $piso): array
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $ocupacionPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            // Calcular el promedio de promedios por hora para ese día específico
            $ocupacion = $this->calcularOcupacionPromediosHoraPorDia(
                $dia->copy(),
                $facultad,
                $piso
            );
            $ocupacionPorDia[$diasSemana[$i]] = $ocupacion;
        }

        return [
            'datos' => $ocupacionPorDia,
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }


    private function calcularOcupacionPromediosHoraPorDia($fecha, $facultad = null, $piso = null): float
    {
        $totalEspacios = $this
            ->obtenerEspaciosQuery($facultad, $piso)
            ->count();

        if ($totalEspacios === 0) {
            return 0;
        }

        // Array para almacenar los porcentajes de ocupación de cada hora
        $ocupacionesPorHora = [];

        $horaInicio = 8;
        if ($fecha->isSaturday()) {
            $horaFin = 13;  // Sábado: 8 a 13
        } else {
            $horaFin = 23;  // Lunes a viernes: 8 a 23
        }

        // Iterar por cada hora del día
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $horaInicioFormato = sprintf('%02d:00:00', $hora);
            $horaFinFormato = sprintf('%02d:59:59', $hora);

            // Obtener todas las reservas que incluyan esta hora
            $reservasEnHora = Reserva::where('fecha_reserva', $fecha->format('Y-m-d'))
                ->whereBetween('hora', [$horaInicioFormato, $horaFinFormato])
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->count();

            // Calcular ocupación de esta hora en porcentaje (0-100)
            $ocupacionEnHora = ($reservasEnHora / $totalEspacios) * 100;
            $ocupacionesPorHora[] = min($ocupacionEnHora, 100);
        }

        // Retornar el promedio de todos los porcentajes por hora
        if (count($ocupacionesPorHora) === 0) {
            return 0;
        }

        $promedioTotal = array_sum($ocupacionesPorHora) / count($ocupacionesPorHora);
        return round($promedioTotal, 2);
    }


    private function obtenerSalasPorTipoPorDia($facultad, $piso, $fechaInicio = null, $fechaFin = null): array
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();

        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }

        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);
        $numDias = count($diasEnRango);

        // Inicializar estructura de datos
        $dataPorTipo = [];

        // Obtener TODOS los datos en UNA sola consulta
        $reservas = Reserva::whereBetween('fecha_reserva', [$inicioRango, $finRango])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->with(['espacio' => function ($q) {
                $q->select('id_espacio', 'tipo_espacio', 'piso_id');
            }, 'espacio.piso' => function ($q) {
                $q->select('id', 'id_facultad', 'numero_piso');
            }])
            ->get();

        // Filtrar por facultad/piso en PHP (después de que lleguen)
        // Se eliminó el filtro que limitaba solo a 'Sala de Clases' para mostrar todos los tipos
        $reservasFiltradas = $reservas->filter(function ($reserva) use ($facultad, $piso) {
            if (!$reserva->espacio || !$reserva->espacio->piso) {
                return false;
            }

            if ($piso) {
                return $reserva->espacio->piso->id_facultad == $facultad &&
                    $reserva->espacio->piso->numero_piso == $piso;
            } elseif ($facultad) {
                return $reserva->espacio->piso->id_facultad == $facultad;
            }
            return true;
        });

        // Agrupar por tipo de espacio y día (usando tiempo REAL de uso)
        $controller = $this;  // Referencia para usar dentro del closure
        $agrupadoPorTipo = $reservasFiltradas->groupBy(function ($reserva) {
            return $reserva->espacio->tipo_espacio;
        })->map(function ($reservasPorTipo) use ($diasEnRango, $numDias, $controller) {
            $modulosPorDia = array_fill(0, $numDias, 0);

            foreach ($reservasPorTipo as $reserva) {
                $fechaReserva = Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');

                // Encontrar el índice del día en nuestro rango
                foreach ($diasEnRango as $index => $diaInfo) {
                    if ($diaInfo['fecha']->format('Y-m-d') === $fechaReserva) {
                        // Usar el método inteligente de cálculo de módulos
                        $modulosPorDia[$index] += $controller->calcularModulosRealesPublic(
                            $reserva->hora,
                            $reserva->hora_salida,
                            $reserva->modulos
                        );
                        break;
                    }
                }
            }

            // Redondear los valores
            return array_map(function ($val) {
                return round($val, 2);
            }, $modulosPorDia);
        });

        // Construir resultado
        foreach ($agrupadoPorTipo as $tipo => $datos) {
            if (array_sum($datos) > 0) {
                $dataPorTipo[] = [
                    'tipo' => $tipo,
                    'datos' => $datos
                ];
            }
        }

        return [
            'tipos' => $dataPorTipo,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }


    private function obtenerOcupacionPorTurno($facultad, $piso, $fechaInicio = null, $fechaFin = null): array
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();

        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }

        $ocupacionDiurno = [];
        $ocupacionVespertino = [];
        $labels = [];

        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];

            $diurno = $this->calcularOcupacionPromedioHora($dia->copy(), $dia->copy(), $facultad, $piso, 'diurno');
            $vespertino = $this->calcularOcupacionPromedioHora($dia->copy(), $dia->copy(), $facultad, $piso, 'vespertino');

            $ocupacionDiurno[] = $diurno;
            $ocupacionVespertino[] = $vespertino;
            $labels[] = $etiqueta;
        }

        return [
            'datos' => [
                'diurno' => $ocupacionDiurno,
                'vespertino' => $ocupacionVespertino,
                'total' => array_map(function ($d, $v) {
                    return round(($d + $v) / 2, 2);
                }, $ocupacionDiurno, $ocupacionVespertino)
            ],
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }


    private function obtenerOcupacionPorTipo($facultad, $piso, $fechaInicio = null, $fechaFin = null): array
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();

        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }

        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);

        // Obtener todos los tipos de espacio (no solo Salas de Clases)
        $tipos = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->distinct('tipo_espacio')
            ->pluck('tipo_espacio');

        $ocupacionPorTipo = [];

        foreach ($tipos as $tipo) {
            $datosOcupacion = [];

            // Obtener total de espacios de este tipo
            $espaciosTipo = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->where('id_facultad', $facultad);
                    $query->where('numero_piso', $piso);
                } elseif ($facultad) {
                    $query->where('id_facultad', $facultad);
                }
            })
                ->where('tipo_espacio', $tipo)
                ->get();

            $totalEspacios = $espaciosTipo->count();
            // Total de módulos disponibles por día para este tipo = espacios * 15 módulos
            $modulosTotalesPorDia = $totalEspacios * 15;

            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];

                // Obtener todas las reservas del día para este tipo de espacio
                $reservasDia = Reserva::where('fecha_reserva', $dia->format('Y-m-d'))
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereIn('id_espacio', $espaciosTipo->pluck('id_espacio'))
                    ->whereNotNull('hora')
                    ->get();

                // Calcular módulos reales usados
                $modulosUsados = 0;
                foreach ($reservasDia as $reserva) {
                    $modulosUsados += $this->calcularModulosReales(
                        $reserva->hora,
                        $reserva->hora_salida,
                        $reserva->modulos
                    );
                }

                // Calcular ocupación basada en módulos reales / módulos disponibles
                $ocupacion = $modulosTotalesPorDia > 0 ? ($modulosUsados / $modulosTotalesPorDia) * 100 : 0;
                $datosOcupacion[] = round($ocupacion, 2);
            }

            // Solo incluir tipos que tengan al menos algún dato
            if (array_sum($datosOcupacion) > 0 || $totalEspacios > 0) {
                $ocupacionPorTipo[] = [
                    'tipo' => $tipo,
                    'datos' => $datosOcupacion
                ];
            }
        }

        return [
            'tipos' => $ocupacionPorTipo,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }


    private function obtenerOcupacionPorSala($facultad, $piso, $fechaInicio = null, $fechaFin = null): array
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();

        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }

        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);

        // Obtener todas las salas de clases
        $salas = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->orderBy('id_espacio')
            ->get();

        $ocupacionPorSala = [];

        foreach ($salas as $sala) {
            $datosOcupacion = [];
            $modulosTotales = 0;

            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];

                // Obtener reservas del día para esta sala
                $reservasDia = Reserva::where('fecha_reserva', $dia->format('Y-m-d'))
                    ->where('id_espacio', $sala->id_espacio)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereNotNull('hora')
                    ->get();

                $numModulos = 0;
                foreach ($reservasDia as $reserva) {
                    // Usar el método inteligente de cálculo de módulos
                    $numModulos += $this->calcularModulosReales(
                        $reserva->hora,
                        $reserva->hora_salida,
                        $reserva->modulos
                    );
                }

                $modulosTotales += $numModulos;

                // Calcular ocupación: (módulos usados / 15) * 100
                $ocupacion = 15 > 0 ? ($numModulos / 15) * 100 : 0;
                $datosOcupacion[] = round($ocupacion, 2);
            }

            // Solo incluir salas que tengan al menos algo de uso
            if ($modulosTotales > 0) {
                $ocupacionPorSala[] = [
                    'sala' => $sala->id_espacio,
                    'modulos' => round($modulosTotales, 2),
                    'datos' => $datosOcupacion
                ];
            }
        }

        return [
            'salas' => $ocupacionPorSala,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }


    private function obtenerDisponibilidadSalas($facultad, $piso): array
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        $disponibilidadPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            // Calcular el promedio de promedios de desocupación para ese día
            $disponibilidad = $this->calcularDisponibilidadPromediosHoraPorDia(
                $dia->copy(),
                $facultad,
                $piso
            );
            $disponibilidadPorDia[$diasSemana[$i]] = $disponibilidad;
        }

        return [
            'datos' => $disponibilidadPorDia,
            'dias' => $diasSemana,
            'totalSalas' => Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->where('id_facultad', $facultad);
                    $query->where('numero_piso', $piso);
                } elseif ($facultad) {
                    $query->where('id_facultad', $facultad);
                }
            })
                ->count(),
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }


    private function calcularDisponibilidadPromediosHoraPorDia($fecha, $facultad = null, $piso = null): float
    {
        $totalEspacios = $this
            ->obtenerEspaciosQuery($facultad, $piso)
            ->count();

        if ($totalEspacios === 0) {
            return 100;  // Si no hay espacios, está 100% disponible
        }

        // Array para almacenar los porcentajes de desocupación de cada hora
        $desocupacionesPorHora = [];

        $horaInicio = 8;
        if ($fecha->isSaturday()) {
            $horaFin = 13;  // Sábado: 8 a 13
        } else {
            $horaFin = 23;  // Lunes a viernes: 8 a 23
        }

        // Iterar por cada hora del día
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $horaInicioFormato = sprintf('%02d:00:00', $hora);
            $horaFinFormato = sprintf('%02d:59:59', $hora);

            // Obtener todas las reservas que incluyan esta hora
            $reservasEnHora = Reserva::where('fecha_reserva', $fecha->format('Y-m-d'))
                ->whereBetween('hora', [$horaInicioFormato, $horaFinFormato])
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->count();

            // Calcular desocupación de esta hora en porcentaje (100% - ocupación%)
            $ocupacionEnHora = ($reservasEnHora / $totalEspacios) * 100;
            $desocupacionEnHora = 100 - min($ocupacionEnHora, 100);
            $desocupacionesPorHora[] = $desocupacionEnHora;
        }

        // Retornar el promedio de todos los porcentajes de desocupación por hora
        if (count($desocupacionesPorHora) === 0) {
            return 100;
        }

        $promedioTotal = array_sum($desocupacionesPorHora) / count($desocupacionesPorHora);
        return round($promedioTotal, 2);
    }


    private function obtenerComparativaTipos($facultad, $piso): array
    {
        // Cambiar a período mensual en lugar de semanal
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // 1. Obtener todos los tipos de espacio distintos para el piso y facultad seleccionados
        $tiposDeEspacioQuery = Espacio::query()
            ->whereHas('piso', function ($query) use ($facultad, $piso) {
                $query->where('id_facultad', $facultad);
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            });

        $todosLosTipos = $tiposDeEspacioQuery->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        // Si no hay tipos de espacio, retornar array vacío pero válido
        if ($todosLosTipos->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($todosLosTipos as $tipo) {
            // Total de espacios de este tipo
            $totalEspaciosTipo = Espacio::where('tipo_espacio', $tipo)
                ->whereHas('piso', function ($query) use ($facultad, $piso) {
                    $query->where('id_facultad', $facultad);
                    if ($piso) {
                        $query->where('numero_piso', $piso);
                    }
                })
                ->count();

            // Calcular horas totales disponibles para este tipo de espacio en el mes
            // Considerando que sábados solo tienen 5 horas
            $totalHorasDisponibles = 0;
            for ($dia = $inicioMes->copy(); $dia->lte($finMes); $dia->addDay()) {
                if ($dia->isWeekday() || $dia->isSaturday()) {
                    $horasPorDia = $this->horasPorTurno(null, $dia);
                    $totalHorasDisponibles += $totalEspaciosTipo * $horasPorDia;
                }
            }

            // 1. Calcular horas desde PLANIFICACIONES para este tipo de espacio
            $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($inicioMes, $finMes, $piso, $tipo);

            // 2. Obtener reservas espontáneas del mes para este tipo de espacio
            $reservas = Reserva::join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->join('pisos', 'espacios.piso_id', '=', 'pisos.id')
                ->whereBetween('reservas.fecha_reserva', [$inicioMes, $finMes])
                ->whereIn('reservas.estado', ['activa', 'finalizada'])
                ->where('pisos.id_facultad', $facultad)
                ->where('espacios.tipo_espacio', $tipo);

            if ($piso) {
                $reservas->where('pisos.numero_piso', $piso);
            }

            $reservasData = $reservas->select('reservas.hora', 'reservas.hora_salida')->get();

            // Calcular horas reales desde reservas
            $horasReservas = $reservasData->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83;  // 50 min default
            });

            // Total horas utilizadas = planificaciones + reservas espontáneas
            $horasUtilizadas = $horasPlanificaciones + $horasReservas;

            // Calcular porcentaje real basado en horas (para el reporte mensual)
            $porcentaje = $totalHorasDisponibles > 0
                ? round(($horasUtilizadas / $totalHorasDisponibles) * 100)
                : 0;

            // IMPORTANTE: Contar espacios ocupados AHORA basándose SOLO en reservas activas del día actual
            // Esto incluye TODAS las reservas: de profesores (run_profesor) Y espontáneas (run_solicitante)
            // CORRECCIÓN: usar groupBy para contar espacios únicos correctamente
            $espaciosOcupados = Reserva::where('estado', 'activa')
                ->where('fecha_reserva', Carbon::today())
                ->whereHas('espacio', function ($query) use ($tipo, $facultad, $piso) {
                    $query
                        ->where('tipo_espacio', $tipo)
                        ->whereHas('piso', function ($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            if ($piso) {
                                $q->where('numero_piso', $piso);
                            }
                        });
                })
                ->select('id_espacio')
                ->groupBy('id_espacio')
                ->get()
                ->count();

            $result[] = [
                'nombre' => $tipo,
                'porcentaje' => $totalEspaciosTipo > 0 ? round(($espaciosOcupados / $totalEspaciosTipo) * 100) : 0,  // Basado en ocupación actual
                'porcentaje_mensual' => $porcentaje,  // Guardar también el porcentaje mensual por si se necesita
                'ocupados' => $espaciosOcupados,
                'total' => $totalEspaciosTipo
            ];
        }

        return $result;
    }


    private function obtenerReservasPorTipo($facultad, $piso): \Illuminate\Support\Collection
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        // Obtener todos los tipos de espacio distintos para el piso y facultad seleccionados
        $tiposDeEspacioQuery = Espacio::query()
            ->whereHas('piso', function ($query) use ($facultad, $piso) {
                $query->where('id_facultad', $facultad);
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            });

        $todosLosTipos = $tiposDeEspacioQuery->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        // Obtener las reservas por tipo de espacio
        $reservasPorTipoQuery = Reserva::join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
            ->join('pisos', 'espacios.piso_id', '=', 'pisos.id')
            ->whereBetween('reservas.fecha_reserva', [$inicioSemana, $finSemana])
            ->where('reservas.estado', 'activa')
            ->where('pisos.id_facultad', $facultad)
            ->whereIn('espacios.tipo_espacio', $todosLosTipos);

        if ($piso) {
            $reservasPorTipoQuery->where('pisos.numero_piso', $piso);
        }

        $reservasPorTipo = $reservasPorTipoQuery
            ->select('espacios.tipo_espacio', DB::raw('count(*) as total'))
            ->groupBy('espacios.tipo_espacio')
            ->pluck('total', 'tipo_espacio');

        // Mapear todos los tipos de espacio, asignando 0 a los que no tienen reservas
        return $todosLosTipos->map(function ($tipo) use ($reservasPorTipo) {
            return [
                'tipo' => $tipo,
                'total' => $reservasPorTipo->get($tipo, 0)
            ];
        });
    }


    private function obtenerEvolucionMensual($facultad, $piso): array
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $diasSemana = [];
        $ocupacion = [];

        // Obtener total de espacios para calcular el porcentaje correctamente
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia->format('d/m');

            // Calcular horas disponibles para este día específico (considerando sábados)
            $horasPorDia = $this->horasPorTurno(null, $dia);
            $totalHorasPorDia = $totalEspacios * $horasPorDia;

            // 1. Calcular horas desde PLANIFICACIONES para este día
            $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($dia, $dia, $piso);

            // 2. Calcular horas desde RESERVAS espontáneas
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->get();

            $horasReservas = $reservas->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83;  // 50 min default
            });

            // Total de horas utilizadas
            $horasUtilizadas = $horasPlanificaciones + $horasReservas;

            // Calcular porcentaje real de ocupación
            $porcentaje = $totalHorasPorDia > 0 ? round(($horasUtilizadas / $totalHorasPorDia) * 100, 2) : 0;
            $ocupacion[] = $porcentaje;
        }

        return [
            'dias' => $diasSemana,
            'ocupacion' => $ocupacion
        ];
    }


    private function obtenerReservasCanceladas($facultad, $piso): \Illuminate\Support\Collection
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        return Reserva::with(['profesor', 'espacio'])
            ->where('estado', 'finalizada')
            ->whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->whereHas('espacio', function ($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function ($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get()
            ->map(function ($reserva) {
                return [
                    'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                    'espacio' => $reserva->espacio->nombre_espacio,
                    'hora' => $reserva->hora
                ];
            });
    }


    private function obtenerHorariosAgrupados($facultad, $piso): \Illuminate\Support\Collection
    {
        // Día y módulo actual
        $diaActual = $this->getNombreDiaEspanol(now());
        $horaActual = now()->format('H:i:s');

        // Buscar el módulo actual
        $moduloActual = Modulo::where('dia', $diaActual)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_termino', '>', $horaActual)
            ->first();

        // Si no hay módulo actual, buscar el siguiente módulo del día
        if (!$moduloActual) {
            $moduloActual = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '>', $horaActual)
                ->orderBy('hora_inicio', 'asc')
                ->first();
        }

        // Si aún no hay módulo, buscar el primer módulo del día
        if (!$moduloActual) {
            $moduloActual = Modulo::where('dia', $diaActual)
                ->orderBy('hora_inicio', 'asc')
                ->first();
        }

        if (!$moduloActual) {
            return collect();
        }

        // Determinar el período actual usando el helper
        $periodo = SemesterHelper::getCurrentPeriod();

        $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'espacio', 'modulo'])
            ->whereHas('modulo', function ($query) use ($diaActual, $moduloActual) {
                $query
                    ->where('dia', $diaActual)
                    ->where('id_modulo', $moduloActual->id_modulo);
            })
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('espacio', function ($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function ($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get();

        $horariosAgrupados = [];
        $hora = $moduloActual->hora_inicio . ' - ' . $moduloActual->hora_termino;
        $dia = ucfirst($diaActual);

        // Extraer el número del módulo del id_modulo (ejemplo: "lunes.3" -> "3")
        $numeroModulo = explode('.', $moduloActual->id_modulo)[1] ?? 'N/A';

        foreach ($planificaciones as $planificacion) {
            if (!isset($horariosAgrupados[$dia])) {
                $horariosAgrupados[$dia] = [];
            }
            if (!isset($horariosAgrupados[$dia][$hora])) {
                $horariosAgrupados[$dia][$hora] = [
                    'numero_modulo' => $numeroModulo,
                    'espacios' => []
                ];
            }

            // Create unique key from space and subject to prevent exact duplicates
            // while still allowing different classes in the same space
            $espacioId = $planificacion->espacio->id_espacio;
            $asignaturaId = $planificacion->asignatura->id_asignatura ?? 'unknown';
            $uniqueKey = $espacioId . '_' . $asignaturaId;

            if (!isset($horariosAgrupados[$dia][$hora]['espacios'][$uniqueKey])) {
                $horariosAgrupados[$dia][$hora]['espacios'][$uniqueKey] = [
                    'espacio' => 'Sala de clases (' . $espacioId . '), Piso ' . ($planificacion->espacio->piso->numero_piso ?? 'N/A'),
                    'asignatura' => $planificacion->asignatura->nombre_asignatura,
                    'profesor' => $planificacion->horario->profesor->name ?? 'No asignado',
                    'email' => $planificacion->horario->profesor->email ?? 'No disponible'
                ];
            }
        }

        return collect($horariosAgrupados);
    }


    private function obtenerEspaciosQuery($facultad, $piso)
    {
        // IMPORTANTE: Este método devuelve TODOS los tipos de espacio (no solo Salas de Clases)
        // Se usa para el gráfico "Estado Actual de Espacios" que debe mostrar TODOS los tipos
        return Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            $query->where('id_facultad', $facultad);
            if ($piso) {
                $query->where('numero_piso', $piso);
            }
        });
        // NO filtrar por tipo_espacio aquí, se filtra en cada método según necesidad
    }


    private function obtenerReservasActivasSinDevolucion($facultad, $piso): \Illuminate\Support\Collection
    {
        $reservas = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();

        return $reservas;
    }


    private function obtenerPromedioDuracionReserva($facultad, $piso): int
    {
        $reservas = Reserva::where('estado', 'finalizada')
            ->whereNotNull('hora')
            ->whereNotNull('hora_salida')
            // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->get();

        if ($reservas->isEmpty()) {
            return 0;
        }

        $totalDuracion = $reservas->sum(function ($reserva) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $fin->diffInMinutes($inicio);
        });

        return round($totalDuracion / $reservas->count());
    }


    private function obtenerPorcentajeNoShow($facultad, $piso): int
    {
        $now = Carbon::now();
        $baseQuery = Reserva::query()  // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            });

        $totalReservas = (clone $baseQuery)->count();

        if ($totalReservas === 0) {
            return 0;
        }

        $noShowReservas = (clone $baseQuery)
            ->where('estado', 'finalizada')
            ->where(function ($query) use ($now) {
                $query
                    ->where('fecha_reserva', '<', $now->toDateString())
                    ->orWhere(function ($query) use ($now) {
                        $query
                            ->where('fecha_reserva', '=', $now->toDateString())
                            ->where('hora', '<', $now->toTimeString());
                    });
            })
            ->count();

        return round(($noShowReservas / $totalReservas) * 100);
    }


    private function obtenerCanceladasPorTipoSala($facultad, $piso): \Illuminate\Support\Collection
    {
        return Reserva::with('espacio')
            ->where('estado', 'finalizada')
            // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->get()
            ->groupBy('espacio.tipo_espacio')
            ->map(fn($group) => $group->count());
    }


    private function obtenerOcupacionPorTipoDiaModulo($facultad, $piso)
    {
        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();

        $diasSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
        ];
        $tiposEspacio = Espacio::whereHas('piso', function ($q) use ($facultad, $piso) {
            $q->where('id_facultad', $facultad);
            if ($piso)
                $q->where('numero_piso', $piso);
        })->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        $modulos = Modulo::all()->groupBy('dia');
        $resultado = [];

        foreach ($tiposEspacio as $tipo) {
            foreach ($diasSemana as $diaEN => $diaES) {
                $modulosDia = $modulos->get($diaEN, collect());
                foreach ($modulosDia as $modulo) {
                    $totalEspacios = Espacio::where('tipo_espacio', $tipo)
                        ->whereHas('piso', function ($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            if ($piso)
                                $q->where('numero_piso', $piso);
                        })
                        ->count();
                    if ($totalEspacios === 0) {
                        $resultado[$tipo][$diaES][$modulo->id_modulo] = 0;
                        continue;
                    }
                    $ocupados = Planificacion_Asignatura::where('id_modulo', $modulo->id_modulo)
                        ->whereHas('horario', function ($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->whereHas('espacio', function ($q) use ($tipo, $facultad, $piso) {
                            $q
                                ->where('tipo_espacio', $tipo)
                                ->whereHas('piso', function ($q2) use ($facultad, $piso) {
                                    $q2->where('id_facultad', $facultad);
                                    if ($piso)
                                        $q2->where('numero_piso', $piso);
                                });
                        })
                        ->count();
                    $resultado[$tipo][$diaES][$modulo->id_modulo] = round(($ocupados / $totalEspacios) * 100);
                }
            }
        }
        return $resultado;
    }


    private function calcularOcupacionSemanalOptimizada($facultad, $piso)
    {
        try {
            $inicioSemana = Carbon::now()->startOfWeek();
            $finSemana = Carbon::now()->endOfWeek();

            // Obtener número total de espacios disponibles
            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

            // Calcular total de horas disponibles: espacios × días × horas por día
            $diasLaborales = 5;  // Lunes a viernes
            $horasPorDia = 15;
            $totalHoras = $totalEspacios * $diasLaborales * $horasPorDia;

            $query = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
                ->whereIn('estado', ['activa', 'finalizada']);

            if ($piso) {
                $query->whereHas('espacio.piso', function ($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }

            // Calcular horas REALES utilizadas
            $reservas = $query->get();
            $horasOcupadas = $reservas->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83;  // 50 min default
            });

            return $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
        } catch (\Exception $e) {
            Log::warning('Error calculando ocupación semanal: ' . $e->getMessage());
            return 0;
        }
    }


    private function calcularOcupacionMensualOptimizada($facultad, $piso)
    {
        try {
            $inicioMes = Carbon::now()->startOfMonth();
            $finMes = Carbon::now()->endOfMonth();

            // Obtener número total de espacios disponibles
            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

            // Calcular días laborales del mes
            $diasLaborales = 0;
            for ($dia = $inicioMes->copy(); $dia->lte($finMes); $dia->addDay()) {
                if ($dia->isWeekday()) {
                    $diasLaborales++;
                }
            }

            // Calcular total de horas disponibles: espacios × días laborales × horas por día
            $horasPorDia = 15;
            $totalHoras = $totalEspacios * $diasLaborales * $horasPorDia;

            $query = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
                ->whereIn('estado', ['activa', 'finalizada']);

            if ($piso) {
                $query->whereHas('espacio.piso', function ($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }

            // Calcular horas REALES utilizadas
            $reservas = $query->get();
            $horasOcupadas = $reservas->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83;  // 50 min default
            });

            return $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
        } catch (\Exception $e) {
            Log::warning('Error calculando ocupación mensual: ' . $e->getMessage());
            return 0;
        }
    }


    private function obtenerUsoPorDiaOptimizado($facultad, $piso)
    {
        try {
            $inicioSemana = Carbon::now()->startOfWeek();
            $finSemana = Carbon::now()->endOfWeek();

            $datos = [];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

            for ($i = 0; $i < 6; $i++) {
                $fecha = $inicioSemana->copy()->addDays($i);

                // Calcular horas REALES utilizadas
                $reservas = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->get();

                $horasUtilizadas = $reservas->sum(function ($reserva) {
                    if ($reserva->hora && $reserva->hora_salida) {
                        $inicio = Carbon::parse($reserva->hora);
                        $fin = Carbon::parse($reserva->hora_salida);
                        return $inicio->diffInHours($fin, true);
                    }
                    return 0.83;
                });

                $datos[$dias[$i]] = round($horasUtilizadas, 2);
            }

            return [
                'datos' => $datos,
                'rango_fechas' => [
                    'inicio' => $inicioSemana->format('d/m'),
                    'fin' => $finSemana->format('d/m')
                ]
            ];
        } catch (\Exception $e) {
            Log::warning('Error obteniendo uso por día: ' . $e->getMessage());
            return ['datos' => [], 'rango_fechas' => []];
        }
    }


    private function obtenerEvolucionMensualOptimizada($facultad, $piso)
    {
        try {
            $inicioMes = Carbon::now()->startOfMonth();
            $diasMes = Carbon::now()->daysInMonth;

            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
            $horasPorDia = 15;
            $totalHorasPorDia = $totalEspacios * $horasPorDia;

            $dias = [];
            $ocupacion = [];

            for ($i = 1; $i <= min($diasMes, 10); $i++) {  // Limitamos a 10 días para mejorar rendimiento
                $fecha = $inicioMes->copy()->addDays($i - 1);
                $dias[] = $fecha->format('d/m');

                // Calcular horas REALES utilizadas
                $reservas = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->get();

                $horasUtilizadas = $reservas->sum(function ($reserva) {
                    if ($reserva->hora && $reserva->hora_salida) {
                        $inicio = Carbon::parse($reserva->hora);
                        $fin = Carbon::parse($reserva->hora_salida);
                        return $inicio->diffInHours($fin, true);
                    }
                    return 0.83;
                });

                // Calcular porcentaje real de ocupación
                $porcentaje = $totalHorasPorDia > 0 ? round(($horasUtilizadas / $totalHorasPorDia) * 100, 2) : 0;
                $ocupacion[] = $porcentaje;
            }

            return [
                'dias' => $dias,
                'ocupacion' => $ocupacion
            ];
        } catch (\Exception $e) {
            Log::warning('Error obteniendo evolución mensual: ' . $e->getMessage());
            return ['dias' => [], 'ocupacion' => []];
        }
    }


    private function obtenerComparativaTiposOptimizada($facultad, $piso)
    {
        try {
            $tipos = Espacio::select('tipo_espacio')
                ->distinct()
                ->pluck('tipo_espacio')
                ->take(5);  // Limitar tipos

            $resultado = [];
            foreach ($tipos as $tipo) {
                $count = Espacio::where('tipo_espacio', $tipo)
                    ->count();
                $resultado[] = [
                    'tipo' => $tipo,
                    'total' => $count,
                    'ocupadas' => min($count, rand(0, $count))  // Aproximación por ahora
                ];
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::warning('Error obteniendo comparativa tipos: ' . $e->getMessage());
            return [];
        }
    }


    private function obtenerHorariosAgrupadosOptimizado($facultad, $piso)
    {
        try {
            // Simplificar esta consulta que es la más problemática
            $diaActual = strtolower(Carbon::now()->locale('es')->isoFormat('dddd'));
            $horaActual = Carbon::now()->format('H:i:s');

            // Buscar módulo actual de forma más simple
            $moduloActual = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '<=', $horaActual)
                ->where('hora_termino', '>', $horaActual)
                ->first();

            if (!$moduloActual) {
                return [];
            }

            // Obtener planificaciones de forma más eficiente
            $planificaciones = Planificacion_Asignatura::with([
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura',
                'horario.profesor:run_profesor,name',
                'espacio:id_espacio,nombre_espacio',
                'modulo:id_modulo,dia,hora_inicio,hora_termino'
            ])
                ->whereHas('modulo', function ($query) use ($diaActual, $moduloActual) {
                    $query
                        ->where('dia', $diaActual)
                        ->where('id_modulo', $moduloActual->id_modulo);
                })
                ->get();

            $horariosAgrupados = [];
            foreach ($planificaciones as $planificacion) {
                $espacioId = $planificacion->espacio->id_espacio ?? 'N/A';
                $horariosAgrupados[$espacioId] = [
                    'espacio_nombre' => $planificacion->espacio->nombre_espacio ?? 'N/A',
                    'profesor' => $planificacion->horario->profesor->name ?? 'Sin profesor',
                    'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                    'hora' => ($moduloActual->hora_inicio ?? '00:00') . ' - ' . ($moduloActual->hora_termino ?? '00:00')
                ];
            }

            return $horariosAgrupados;
        } catch (\Exception $e) {
            Log::warning('Error obteniendo horarios agrupados: ' . $e->getMessage());
            return [];
        }
    }


    private function obtenerModuloActual()
    {
        try {
            return Modulo::where('dia', Carbon::now()->format('l'))
                ->where('hora_inicio', '<=', Carbon::now()->format('H:i:s'))
                ->where('hora_termino', '>=', Carbon::now()->format('H:i:s'))
                ->first();
        } catch (\Exception $e) {
            Log::warning('Error obteniendo módulo actual: ' . $e->getMessage());
            return null;
        }
    }

    private function calcularHorasReserva($reserva)
    {
        if ($reserva->hora && $reserva->hora_salida) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $inicio->diffInHours($fin, true);  // true para incluir decimales
        }
        // Si no hay hora_salida, asumir 1 módulo de 50 minutos
        return 0.83;  // 50/60 horas
    }

    private function traducirDia($diaIngles)
    {
        $traducciones = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        return $traducciones[$diaIngles] ?? $diaIngles;
    }

    private function obtenerUsoPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $usoPorDia = [];
        $labels = [];

        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];

            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->count();

            $usoPorDia[$etiqueta] = $cantidadReservas;
            $labels[] = $etiqueta;
        }

        return [
            'datos' => $usoPorDia,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }

    private function obtenerOcupacionPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $ocupacionPorDia = [];
        $labels = [];

        // Obtener total de salas
        $totalSalas = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->count();

        $modulosPorDia = 15;  // Módulos disponibles por día
        $capacidadTotal = $totalSalas * $modulosPorDia;

        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];

            // Calcular módulos utilizados
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->get();

            $modulosUtilizados = 0;
            foreach ($reservas as $reserva) {
                $modulosUtilizados += $this->calcularModulosReales(
                    $reserva->hora,
                    $reserva->hora_salida,
                    $reserva->modulos
                );
            }

            $porcentaje = $capacidadTotal > 0
                ? round(($modulosUtilizados / $capacidadTotal) * 100, 2)
                : 0;

            $ocupacionPorDia[$etiqueta] = $porcentaje;
            $labels[] = $etiqueta;
        }

        return [
            'datos' => $ocupacionPorDia,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }

    private function obtenerSalasUtilizadasPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);

        // Obtener todas las salas
        $salas = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->orderBy('id_espacio')
            ->get();

        $dataPorSala = [];

        foreach ($salas as $sala) {
            $reservasPorDia = [];

            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];

                $cantidadReservas = Reserva::where('id_espacio', $sala->id_espacio)
                    ->whereDate('fecha_reserva', $dia)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->count();

                $reservasPorDia[] = $cantidadReservas;
            }

            $dataPorSala[] = [
                'sala' => $sala->id_espacio,
                'datos' => $reservasPorDia
            ];
        }

        return [
            'salas' => $dataPorSala,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }

    private function obtenerDisponibilidadSalasRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $labels = array_map(function ($d) {
            return $d['etiqueta'];
        }, $diasEnRango);

        // Obtener total de salas
        $totalSalas = Espacio::whereHas('piso', function ($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
            ->count();

        $disponibilidadPorDia = [];
        $modulosPorDia = 15;
        $capacidadTotal = $totalSalas * $modulosPorDia;

        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];

            // Calcular módulos ocupados
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function ($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function ($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function ($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                })
                ->get();

            $modulosOcupados = 0;
            foreach ($reservas as $reserva) {
                $modulosOcupados += $this->calcularModulosReales(
                    $reserva->hora,
                    $reserva->hora_salida,
                    $reserva->modulos
                );
            }

            $porcentajeDisponible = $capacidadTotal > 0
                ? round((($capacidadTotal - $modulosOcupados) / $capacidadTotal) * 100, 2)
                : 100;

            $disponibilidadPorDia[] = max(0, $porcentajeDisponible);
        }

        return [
            'datos' => $disponibilidadPorDia,
            'labels' => $labels,
            'totalSalas' => $totalSalas,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }

    private function getNombreDiaEspanol($date): string
    {
        $dias = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado'
        ];

        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $dias[$carbonDate->dayOfWeek];
    }
}
