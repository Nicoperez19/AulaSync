<?php

namespace App\Services;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\OcupacionSemanalSnapshot;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use Carbon\Carbon;

class OcupacionSemanalService
{
    /**
     * Días a evaluar en la semana de módulos
     */
    public const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    /**
     * Mapeo de días a prefijos de módulo en base de datos
     */
    public const PREFIJOS_DIAS = [
        'lunes' => 'LU',
        'martes' => 'MA',
        'miercoles' => 'MI',
        'jueves' => 'JU',
        'viernes' => 'VI',
        'sabado' => 'SA',
    ];

    /**
     * Tipos de filtros soportados
     */
    public const TIPOS_FILTRO = ['todos', 'laboratorios', 'salas_clases', 'salas_estudio'];

    /**
     * Construye la consulta de espacios según el tipo de filtro
     */
    public function getEspaciosQuery(string $tipoFilter = 'todos')
    {
        $tipoFilter = strtolower($tipoFilter);
        $espaciosQuery = Espacio::query();

        if ($tipoFilter === 'laboratorios') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%laboratorio%')
                    ->orWhere('tipo_espacio', 'like', '%lab%');
            });
        } elseif ($tipoFilter === 'salas_clases' || $tipoFilter === 'aula' || $tipoFilter === 'clases') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%clase%')
                    ->orWhere('tipo_espacio', 'like', '%aula%')
                    ->orWhere('tipo_espacio', 'like', '%taller%');
            });
        } elseif ($tipoFilter === 'salas_estudio' || $tipoFilter === 'estudio') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%estudio%')
                    ->orWhere('tipo_espacio', 'like', '%biblioteca%');
            });
        }

        return $espaciosQuery;
    }

    /**
     * Calcula la matriz de ocupación para una semana específica
     */
    public function calcularOcupacion(Carbon $lunesDate, string $tipoFilter = 'todos', bool $incluirEstadoVivo = false): array
    {
        $lunes = $lunesDate->copy()->startOfWeek();
        $tipoFilter = strtolower($tipoFilter);

        $espaciosQuery = $this->getEspaciosQuery($tipoFilter);
        $espaciosIds = $espaciosQuery->pluck('id_espacio');
        $totalEspacios = $espaciosIds->count();

        $fechasSemana = [
            'lunes' => $lunes->format('Y-m-d'),
            'martes' => $lunes->copy()->addDay(1)->format('Y-m-d'),
            'miercoles' => $lunes->copy()->addDay(2)->format('Y-m-d'),
            'jueves' => $lunes->copy()->addDay(3)->format('Y-m-d'),
            'viernes' => $lunes->copy()->addDay(4)->format('Y-m-d'),
            'sabado' => $lunes->copy()->addDay(5)->format('Y-m-d'),
        ];

        $fechaInicioSemana = $fechasSemana['lunes'];
        $fechaFinSemana = $fechasSemana['sabado'];

        $periodoActual = SemesterHelper::getCurrentPeriod($lunes);

        $planificacionesSemana = Planificacion_Asignatura::whereIn('id_espacio', $espaciosIds)
            ->whereHas('horario', function ($q) use ($periodoActual) {
                $q->where('periodo', $periodoActual);
            })
            ->get(['id_espacio', 'id_modulo'])
            ->groupBy('id_modulo');

        $reservasSemana = Reserva::whereBetween('fecha_reserva', [$fechaInicioSemana, $fechaFinSemana])
            ->whereIn('id_espacio', $espaciosIds)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->where(function ($q) {
                $q->whereNull('hubo_asistentes')
                    ->orWhere('hubo_asistentes', true);
            })
            ->get()
            ->groupBy(function ($reserva) {
                $fecha = $reserva->fecha_reserva;

                return $fecha instanceof Carbon
                    ? $fecha->format('Y-m-d')
                    : Carbon::parse($fecha)->format('Y-m-d');
            });

        // En vivo: sólo si se solicita y coincide con la semana actual
        $esSemanaActual = $lunes->isSameWeek(now());
        $diaHoyNormalizado = null;
        $moduloActualNum = null;

        if ($incluirEstadoVivo && $esSemanaActual) {
            $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
            $diaHoyNormalizado = ModulosHelper::normalizarDia($diaActual);
            $horaAhora = date('H:i:s');

            $horariosModulos = ModulosHelper::getHorariosModulos();
            if (isset($horariosModulos[$diaHoyNormalizado])) {
                foreach ($horariosModulos[$diaHoyNormalizado] as $num => $horario) {
                    if ($horaAhora >= $horario['inicio'] && $horaAhora < $horario['fin']) {
                        $moduloActualNum = $num;
                        break;
                    }
                }
            }
        }

        $ocupacion = [];

        foreach (self::DIAS_SEMANA as $dia) {
            $fechaDelDia = $fechasSemana[$dia];
            $ocupacion[$dia] = [];
            $maxModulos = ($dia === 'sabado') ? 6 : 15;
            $prefijo = self::PREFIJOS_DIAS[$dia] ?? 'LU';

            $reservasDia = $reservasSemana->get($fechaDelDia, collect());

            for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                if ($moduloNum > $maxModulos) {
                    $ocupacion[$dia][$moduloNum] = null; // Módulo no disponible (N/A)

                    continue;
                }

                $idModuloStr = $prefijo.'.'.$moduloNum;
                $espaciosUsados = [];

                // 1. Programación de clases
                if (isset($planificacionesSemana[$idModuloStr])) {
                    $espaciosProgramados = $planificacionesSemana[$idModuloStr]->pluck('id_espacio')->toArray();
                    $espaciosUsados = array_merge($espaciosUsados, $espaciosProgramados);
                }

                // 2. Reservas de uso real
                $horarioModulo = ModulosHelper::getHorarioModulo($dia, $moduloNum);
                foreach ($reservasDia as $reserva) {
                    $overlap = false;
                    $modInicio = $reserva->modulo_inicio ? (int) $reserva->modulo_inicio : null;
                    $modFin = $reserva->modulo_fin ? (int) $reserva->modulo_fin : null;

                    if ($modInicio !== null && $modFin !== null) {
                        if ($moduloNum >= $modInicio && $moduloNum <= $modFin) {
                            $overlap = true;
                        }
                    } else {
                        if ($horarioModulo && $reserva->hora) {
                            $resInicio = $reserva->hora;
                            $resFin = $reserva->hora_salida ?: Carbon::parse($reserva->hora)->addHour()->format('H:i:s');
                            $modInicioHorario = $horarioModulo['inicio'];
                            $modFinHorario = $horarioModulo['fin'];

                            $overlapStart = max($resInicio, $modInicioHorario);
                            $overlapEnd = min($resFin, $modFinHorario);
                            if ($overlapStart < $overlapEnd) {
                                $overlap = true;
                            }
                        }
                    }

                    if ($overlap) {
                        $espaciosUsados[] = $reserva->id_espacio;
                    }
                }

                // 3. En vivo (módulo actual)
                if ($dia === $diaHoyNormalizado && $moduloNum === $moduloActualNum) {
                    $espaciosEstadoOcupado = Espacio::whereIn('id_espacio', $espaciosIds)
                        ->where('estado', 'Ocupado')
                        ->pluck('id_espacio')
                        ->toArray();
                    $espaciosUsados = array_merge($espaciosUsados, $espaciosEstadoOcupado);
                }

                $totalUsados = count(array_unique($espaciosUsados));
                $porcentaje = $totalEspacios > 0 ? round(($totalUsados / $totalEspacios) * 100) : 0;
                $ocupacion[$dia][$moduloNum] = min(100, max(0, $porcentaje));
            }
        }

        $resumen = $this->calcularResumen($ocupacion);

        return [
            'ocupacion' => $ocupacion,
            'total_espacios' => $totalEspacios,
            'fechasSemana' => $fechasSemana,
            'semana_inicio' => $fechaInicioSemana,
            'semana_fin' => $fechaFinSemana,
            'periodo' => $periodoActual,
            'anio' => (int) $lunes->year,
            'semana_iso' => (int) $lunes->isoWeek(),
            'tipo_filtro' => $tipoFilter,
            'resumen' => $resumen,
        ];
    }

    /**
     * Calcula métricas de resumen a partir de la matriz de ocupación
     */
    public function calcularResumen(array $ocupacion): array
    {
        $valoresValidos = [];
        $promediosPorDia = [];
        $promediosPorModulo = [];
        $picoMaximo = 0;
        $diaPico = null;
        $moduloPico = null;

        foreach (self::DIAS_SEMANA as $dia) {
            $valoresDia = [];
            foreach (($ocupacion[$dia] ?? []) as $mod => $val) {
                if ($val !== null) {
                    $valInt = (int) $val;
                    $valoresValidos[] = $valInt;
                    $valoresDia[] = $valInt;

                    if (! isset($promediosPorModulo[$mod])) {
                        $promediosPorModulo[$mod] = [];
                    }
                    $promediosPorModulo[$mod][] = $valInt;

                    if ($valInt >= $picoMaximo) {
                        $picoMaximo = $valInt;
                        $diaPico = $dia;
                        $moduloPico = $mod;
                    }
                }
            }
            $promediosPorDia[$dia] = count($valoresDia) > 0 ? round(array_sum($valoresDia) / count($valoresDia), 1) : 0;
        }

        $moduloPromediosFinal = [];
        foreach ($promediosPorModulo as $mod => $arr) {
            $moduloPromediosFinal[$mod] = count($arr) > 0 ? round(array_sum($arr) / count($arr), 1) : 0;
        }

        $promedioGeneral = count($valoresValidos) > 0 ? round(array_sum($valoresValidos) / count($valoresValidos), 1) : 0;

        return [
            'promedio_general' => $promedioGeneral,
            'pico_maximo' => $picoMaximo,
            'dia_pico' => $diaPico,
            'modulo_pico' => $moduloPico,
            'promedios_por_dia' => $promediosPorDia,
            'promedios_por_modulo' => $moduloPromediosFinal,
        ];
    }

    /**
     * Almacena o actualiza un snapshot semanal en base de datos
     */
    public function guardarSnapshot(Carbon $lunesDate, string $tipoFilter = 'todos', bool $forzar = false, ?int $userId = null, string $origen = 'automatico'): OcupacionSemanalSnapshot
    {
        $lunes = $lunesDate->copy()->startOfWeek();
        $tipoFilter = strtolower($tipoFilter);

        $existente = OcupacionSemanalSnapshot::where('semana_inicio', $lunes->format('Y-m-d'))
            ->where('tipo_filtro', $tipoFilter)
            ->first();

        if ($existente && ! $forzar) {
            return $existente;
        }

        // Para snapshots históricos guardados, no incluimos estado en vivo 'Ocupado'
        $datos = $this->calcularOcupacion($lunes, $tipoFilter, false);

        return OcupacionSemanalSnapshot::updateOrCreate(
            [
                'semana_inicio' => $datos['semana_inicio'],
                'tipo_filtro' => $tipoFilter,
            ],
            [
                'semana_fin' => $datos['semana_fin'],
                'anio' => $datos['anio'],
                'semana_iso' => $datos['semana_iso'],
                'periodo' => $datos['periodo'],
                'total_espacios' => $datos['total_espacios'],
                'datos' => $datos['ocupacion'],
                'resumen' => $datos['resumen'],
                'origen' => $origen,
                'generado_por' => $userId,
            ]
        );
    }

    /**
     * Guarda snapshots para todos los tipos de filtro en la semana dada
     */
    public function guardarTodosLosTipos(Carbon $lunesDate, bool $forzar = false, ?int $userId = null, string $origen = 'automatico'): array
    {
        $snapshots = [];
        foreach (self::TIPOS_FILTRO as $tipo) {
            $snapshots[$tipo] = $this->guardarSnapshot($lunesDate, $tipo, $forzar, $userId, $origen);
        }

        return $snapshots;
    }

    /**
     * Obtiene snapshot guardado o calcula al vuelo si no existe
     */
    public function obtenerOGenerar(Carbon $lunesDate, string $tipoFilter = 'todos'): array
    {
        $lunes = $lunesDate->copy()->startOfWeek();
        $tipoFilter = strtolower($tipoFilter);

        // Si es la semana actual, calcular siempre fresco incluyendo vivo
        if ($lunes->isSameWeek(now())) {
            return $this->calcularOcupacion($lunes, $tipoFilter, true);
        }

        $snapshot = OcupacionSemanalSnapshot::where('semana_inicio', $lunes->format('Y-m-d'))
            ->where('tipo_filtro', $tipoFilter)
            ->first();

        if ($snapshot) {
            return [
                'ocupacion' => $snapshot->datos,
                'total_espacios' => $snapshot->total_espacios,
                'semana_inicio' => $snapshot->semana_inicio->format('Y-m-d'),
                'semana_fin' => $snapshot->semana_fin->format('Y-m-d'),
                'periodo' => $snapshot->periodo,
                'anio' => $snapshot->anio,
                'semana_iso' => $snapshot->semana_iso,
                'tipo_filtro' => $snapshot->tipo_filtro,
                'resumen' => $snapshot->resumen,
                'es_snapshot' => true,
            ];
        }

        return $this->calcularOcupacion($lunes, $tipoFilter, false);
    }
}
