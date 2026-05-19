<?php

namespace App\Traits;

use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Piso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\SemesterHelper;
use App\Exports\AccesosExport;

trait ReportCalculationsTrait
{
    private function calcularDiasLaborales($anio, $mes)
    {
        $fecha = Carbon::create($anio, $mes, 1);
        $diasEnMes = $fecha->daysInMonth;

        $diasLaborales = 0;
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fechaDia = Carbon::create($anio, $mes, $dia);
            if ($fechaDia->isWeekday()) {
                $diasLaborales++;
            }
        }

        return $diasLaborales;
    }

    private function determinarEstadoUtilizacion($porcentaje)
    {
        if ($porcentaje >= 80)
            return 'Óptimo';
        if ($porcentaje >= 40)
            return 'Medio uso';
        return 'Bajo uso';
    }

    private function calcularOcupacionHorarios($espacios, $mes, $anio, $diasDisponibles)
    {
        $ocupacionHorarios = [];

        // Calcular ocupación por espacio individual
        foreach ($espacios as $espacio) {
            $espacioId = $espacio->id_espacio;
            $ocupacionHorarios[$espacioId] = [];

            foreach ($diasDisponibles as $dia) {
                $ocupacionHorarios[$espacioId][$dia] = [];

                // Inicializar todos los módulos en 0
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupacionHorarios[$espacioId][$dia][$moduloNum] = 0;
                }

                // Obtener reservas para este espacio específico en este día
                $reservasDelDia = Reserva::where('id_espacio', $espacioId)
                    ->whereMonth('fecha_reserva', $mes)
                    ->whereYear('fecha_reserva', $anio)
                    ->get()
                    ->filter(function ($reserva) use ($dia) {
                        $diaSemana = strtolower(Carbon::parse($reserva->fecha_reserva)->locale('es')->isoFormat('dddd'));
                        return $diaSemana === $dia;
                    });

                // Contar reservas por módulo
                $ocupadosPorModulo = [];
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupadosPorModulo[$moduloNum] = 0;
                }

                foreach ($reservasDelDia as $reserva) {
                    if ($reserva->hora) {
                        $hora = Carbon::parse($reserva->hora);
                        $modulo = $this->obtenerModuloPorHora($hora->hour);
                        if (isset($ocupadosPorModulo[$modulo])) {
                            $ocupadosPorModulo[$modulo]++;
                        }
                    }
                }

                // Calcular porcentaje de ocupación por módulo (1 espacio = 100% si está ocupado)
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupacionHorarios[$espacioId][$dia][$moduloNum] = $ocupadosPorModulo[$moduloNum] > 0 ? 100 : 0;
                }
            }
        }

        return $ocupacionHorarios;
    }


    private function exportarHistoricoExcel($datos, $fechaInicio, $fechaFin)
    {
        try {
            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.xlsx';

            return Excel::download(new \App\Exports\HistoricoEspaciosExport($datos), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }


    private function exportarHistoricoPDF($datos, $fechaInicio, $fechaFin, $piso = '', $tipoUsuario = '', $tipoEspacioFiltro = '')
    {
        try {
            // Calcular resumen
            $total = count($datos);
            $completadas = collect($datos)->where('estado', 'Finalizada')->count();
            $canceladas = collect($datos)->where('estado', 'Cancelada')->count();
            $enProgreso = collect($datos)->where('estado', 'En progreso')->count();
            $activas = collect($datos)->where('estado', 'Activa')->count();

            $data = [
                'datos' => $datos,
                'fecha_inicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_registros' => $total,
                'filtros_aplicados' => [
                    'tipo_espacio' => $tipoEspacioFiltro,
                    'piso' => $piso,
                    'estado' => '',
                    'busqueda' => ''
                ],
                'resumen' => [
                    'total' => $total,
                    'completadas' => $completadas,
                    'canceladas' => $canceladas,
                    'en_progreso' => $enProgreso + $activas
                ]
            ];

            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.historico-espacios', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }

    private function determinarTipoUsuario($profesor)
    {
        if (!$profesor) {
            return 'externo';
        }

        if ($profesor->tipo_profesor) {
            return 'profesor';
        }

        return 'externo';
    }

    private function calcularDuracion($horaEntrada, $horaSalida)
    {
        if (!$horaSalida) {
            return 'En curso';
        }

        $entrada = Carbon::parse($horaEntrada);
        $salida = Carbon::parse($horaSalida);
        $duracion = $entrada->diffInMinutes($salida);

        if ($duracion < 60) {
            return $duracion . ' min';
        } else {
            $horas = floor($duracion / 60);
            $minutos = $duracion % 60;
            return $horas . 'h ' . $minutos . 'min';
        }
    }

    private function obtenerIncidencias($idReserva)
    {
        // Aquí puedes implementar la lógica para obtener incidencias
        // Por ahora retornamos un array vacío
        return [];
    }

    private function obtenerPisosDisponibles()
    {
        return Cache::remember('reportes.pisos_disponibles', 3600, function () {
            return Piso::whereHas('facultad', function ($query) {
                $query->where('id_facultad', 'IT_TH');
            })
                ->orderBy('numero_piso')
                ->pluck('numero_piso', 'numero_piso');
        });
    }

    private function obtenerEspaciosDisponibles()
    {
        return Cache::remember('reportes.espacios_disponibles', 3600, function () {
            return Espacio::whereHas('piso.facultad', function ($query) {
                $query->where('id_facultad', 'IT_TH');
            })
                ->orderBy('nombre_espacio')
                ->pluck('nombre_espacio', 'nombre_espacio');
        });
    }

    private function obtenerTiposUsuario()
    {
        return [
            'profesor' => 'Profesor',
            'solicitante' => 'Solicitante',
            'estudiante' => 'Estudiante',
            'administrativo' => 'Personal Administrativo'
        ];
    }

    private function exportarAccesosExcel($accesos)
    {
        try {
            // Obtener código de espacio
            $codigoEspacio = $accesos->first()['id_espacio'] ?? 'sin_codigo';
            // Obtener año y semestre usando el helper
            $anio = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . $semestre . '.xlsx';
            return Excel::download(new AccesosExport($accesos), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    private function exportarAccesosPDF($accesos)
    {
        try {
            $data = [
                'accesos' => $accesos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_accesos' => $accesos->count(),
                'usuarios_unicos' => $accesos->unique('run')->count(),
                'espacios_utilizados' => $accesos->unique('espacio')->count(),
                'en_curso' => $accesos->where('hora_salida', 'En curso')->count()
            ];

            // Obtener código de espacio
            $codigoEspacio = $accesos->first()['id_espacio'] ?? 'sin_codigo';
            // Obtener año y semestre usando el helper
            $anio = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . $semestre . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.accesos', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }

    private function obtenerTiposEspacioDisponibles()
    {
        return Espacio::select('tipo_espacio')
            ->distinct()
            ->orderBy('tipo_espacio')
            ->pluck('tipo_espacio', 'tipo_espacio');
    }

    private function obtenerDiasDisponibles()
    {
        return [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado'
        ];
    }


    private function generarDatosOcupacionHorarios($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $tipoEspacioFiltro = null, $diaFiltro = null)
    {
        $modulosHorarios = $this->getModulosHorarios();

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        // Aplicar filtro de día si está especificado
        if (!empty($diaFiltro) && in_array($diaFiltro, $dias)) {
            $dias = [$diaFiltro];
        }

        // Obtener tipos de espacio
        $tiposQuery = Espacio::query();
        if (!empty($piso)) {
            $tiposQuery->whereHas('piso', function ($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }
        $tiposEspacio = $tiposQuery->distinct()->pluck('tipo_espacio');

        // Aplicar filtro de tipo de espacio si está especificado
        if (!empty($tipoEspacioFiltro) && in_array($tipoEspacioFiltro, $tiposEspacio->toArray())) {
            $tiposEspacio = collect([$tipoEspacioFiltro]);
        }

        $ocupacionHorarios = [];

        foreach ($tiposEspacio as $tipo) {
            $ocupacionHorarios[$tipo] = [];

            foreach ($dias as $dia) {
                $ocupacionHorarios[$tipo][$dia] = [];

                foreach ($modulosHorarios as $moduloNum => $horario) {
                    // Contar reservas para este tipo de espacio, día y módulo
                    $reservasQuery = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
                        ->where('estado', 'activa')
                        ->whereRaw('DAYOFWEEK(fecha_reserva) = ?', [$this->obtenerNumeroDia($dia)])
                        ->whereTime('hora', '>=', $horario['inicio'])
                        ->whereTime('hora', '<', $horario['fin'])
                        ->whereHas('espacio', function ($q) use ($tipo, $piso) {
                            $q->where('tipo_espacio', $tipo);
                            if (!empty($piso)) {
                                $q->whereHas('piso', function ($q2) use ($piso) {
                                    $q2->where('numero_piso', $piso);
                                });
                            }
                        });

                    if (!empty($tipoUsuario)) {
                        $reservasQuery->whereHas('user', function ($q) use ($tipoUsuario) {
                            if ($tipoUsuario === 'profesor') {
                                $q->whereNotNull('tipo_profesor');
                            } elseif ($tipoUsuario === 'estudiante') {
                                $q->whereNull('tipo_profesor')->whereNotNull('id_carrera');
                            } elseif ($tipoUsuario === 'administrativo') {
                                $q->whereNull('tipo_profesor')->whereNull('id_carrera')->whereNotNull('id_facultad');
                            } else {
                                $q->whereNull('tipo_profesor')->whereNull('id_carrera')->whereNull('id_facultad');
                            }
                        });
                    }

                    $totalReservas = $reservasQuery->count();

                    // Calcular capacidad máxima para este módulo
                    $espaciosDelTipo = Espacio::where('tipo_espacio', $tipo);
                    if (!empty($piso)) {
                        $espaciosDelTipo->whereHas('piso', function ($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                    $totalEspacios = $espaciosDelTipo->count();

                    // Calcular porcentaje de ocupación
                    $porcentajeOcupacion = $totalEspacios > 0 ? round(($totalReservas / $totalEspacios) * 100) : 0;

                    $ocupacionHorarios[$tipo][$dia][$moduloNum] = $porcentajeOcupacion;
                }
            }
        }

        return $ocupacionHorarios;
    }

    private function calcularHorariosPico($ocupacionHorarios)
    {
        $modulosPico = [
            1 => '08:10-09:00',
            2 => '09:10-10:00',
            3 => '10:10-11:00',
            4 => '11:10-12:00',
            5 => '12:10-13:00',
            6 => '13:10-14:00',
            7 => '14:10-15:00',
            8 => '15:10-16:00',
            9 => '16:10-17:00',
            10 => '17:10-18:00',
            11 => '18:10-19:00',
            12 => '19:10-20:00',
            13 => '20:10-21:00',
            14 => '21:10-22:00',
            15 => '22:10-23:00'
        ];

        $horariosPico = [];
        $promediosModulos = [];

        // Calcular promedio de ocupación por módulo
        foreach ($modulosPico as $moduloNum => $horario) {
            $sumaOcupacion = 0;
            $contador = 0;

            foreach ($ocupacionHorarios as $tipo => $dias) {
                foreach ($dias as $dia => $modulosData) {
                    if (isset($modulosData[$moduloNum])) {
                        $sumaOcupacion += $modulosData[$moduloNum];
                        $contador++;
                    }
                }
            }

            $promedio = $contador > 0 ? $sumaOcupacion / $contador : 0;
            $promediosModulos[$moduloNum] = [
                'horario' => $horario,
                'promedio' => $promedio
            ];
        }

        // Ordenar por promedio de ocupación (descendente)
        uasort($promediosModulos, function ($a, $b) {
            return $b['promedio'] <=> $a['promedio'];
        });

        // Tomar los 3 horarios con mayor ocupación
        $contador = 0;
        foreach ($promediosModulos as $moduloNum => $data) {
            if ($contador >= 3)
                break;

            $nivelDemanda = 'Baja demanda';
            $colorClase = 'bg-[#E5FFF2] text-[#05CD99]';

            if ($data['promedio'] >= 80) {
                $nivelDemanda = 'Alta demanda';
                $colorClase = 'bg-[#FFE5E5] text-[#F97E5E]';
            } elseif ($data['promedio'] >= 40) {
                $nivelDemanda = 'Media demanda';
                $colorClase = 'bg-[#FFF7E5] text-[#F7B267]';
            }

            $horariosPico[] = [
                'horario' => $data['horario'],
                'nivel_demanda' => $nivelDemanda,
                'color_clase' => $colorClase,
                'porcentaje' => round($data['promedio'], 1)
            ];

            $contador++;
        }

        return $horariosPico;
    }

    private function obtenerNumeroDia($dia)
    {
        $dias = [
            'lunes' => 2,
            'martes' => 3,
            'miercoles' => 4,
            'jueves' => 5,
            'viernes' => 6,
            'sabado' => 7,
            'domingo' => 1
        ];

        return $dias[$dia] ?? 1;
    }

    private function obtenerHoraModulo($moduloNum, $tipo = 'inicio')
    {
        $modulosHorarios = $this->getModulosHorarios();

        return $modulosHorarios[$moduloNum][$tipo] ?? '00:00';
    }

    private function getModulosHorarios(): array
    {
        return [
            1 => ['inicio' => '08:10', 'fin' => '09:00'],
            2 => ['inicio' => '09:10', 'fin' => '10:00'],
            3 => ['inicio' => '10:10', 'fin' => '11:00'],
            4 => ['inicio' => '11:10', 'fin' => '12:00'],
            5 => ['inicio' => '12:10', 'fin' => '13:00'],
            6 => ['inicio' => '13:10', 'fin' => '14:00'],
            7 => ['inicio' => '14:10', 'fin' => '15:00'],
            8 => ['inicio' => '15:10', 'fin' => '16:00'],
            9 => ['inicio' => '16:10', 'fin' => '17:00'],
            10 => ['inicio' => '17:10', 'fin' => '18:00'],
            11 => ['inicio' => '18:10', 'fin' => '19:00'],
            12 => ['inicio' => '19:10', 'fin' => '20:00'],
            13 => ['inicio' => '20:10', 'fin' => '21:00'],
            14 => ['inicio' => '21:10', 'fin' => '22:00'],
            15 => ['inicio' => '22:10', 'fin' => '23:00'],
        ];
    }

    private function obtenerModuloPorHora($hora)
    {
        $horaInt = (int) $hora;

        if ($horaInt >= 8 && $horaInt < 9)
            return 1;
        if ($horaInt >= 9 && $horaInt < 10)
            return 2;
        if ($horaInt >= 10 && $horaInt < 11)
            return 3;
        if ($horaInt >= 11 && $horaInt < 12)
            return 4;
        if ($horaInt >= 12 && $horaInt < 13)
            return 5;
        if ($horaInt >= 13 && $horaInt < 14)
            return 6;
        if ($horaInt >= 14 && $horaInt < 15)
            return 7;
        if ($horaInt >= 15 && $horaInt < 16)
            return 8;
        if ($horaInt >= 16 && $horaInt < 17)
            return 9;
        if ($horaInt >= 17 && $horaInt < 18)
            return 10;
        if ($horaInt >= 18 && $horaInt < 19)
            return 11;
        if ($horaInt >= 19 && $horaInt < 20)
            return 12;
        if ($horaInt >= 20 && $horaInt < 21)
            return 13;
        if ($horaInt >= 21 && $horaInt < 22)
            return 14;
        if ($horaInt >= 22 && $horaInt < 23)
            return 15;

        return 1;  // Por defecto
    }


    private function exportarHorariosExcel($datos, $fecha, $moduloInicio, $moduloFin, $modulosDia)
    {
        $filename = 'ocupacion_horarios_' . $fecha . '_modulos_' . ($moduloInicio + 1) . '_' . ($moduloFin + 1) . '.xlsx';

        return Excel::download(new \App\Exports\OcupacionHorariosExport($datos, $moduloInicio, $moduloFin, $modulosDia), $filename);
    }


    private function exportarHorariosPDF($datos, $fecha, $moduloInicio, $moduloFin, $modulosDia)
    {
        $data = [
            'datos' => $datos,
            'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
            'moduloInicio' => $moduloInicio + 1,
            'moduloFin' => $moduloFin + 1,
            'modulosDia' => $modulosDia,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'total_registros' => count($datos)
        ];

        $filename = 'ocupacion_horarios_' . $fecha . '_modulos_' . ($moduloInicio + 1) . '_' . ($moduloFin + 1) . '.pdf';
        $pdf = Pdf::loadView('reportes.pdf.horarios', $data);
        return $pdf->download($filename);
    }


    private function exportarResumenExcel($datos)
    {
        $filename = 'analisis_espacios_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\AnalisisEspaciosExport($datos), $filename);
    }


    private function exportarResumenPDF($datos, $tipoEspacioFiltro = '', $pisoFiltro = '', $estadoFiltro = '', $busqueda = '')
    {
        $data = [
            'datos' => $datos,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'periodo' => Carbon::now()->format('m/Y'),
            'total_registros' => count($datos),
            'filtros_aplicados' => [
                'tipo_espacio' => $tipoEspacioFiltro,
                'piso' => $pisoFiltro,
                'estado' => $estadoFiltro,
                'busqueda' => $busqueda
            ]
        ];

        $filename = 'analisis_espacios_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf = Pdf::loadView('reportes.pdf.espacios', $data);
        return $pdf->download($filename);
    }

    private function exportarHistoricoTipoEspacioExcel($datos, $fechaInicio, $fechaFin, $tipoEspacio)
    {
        try {
            $filename = 'historico_tipo_espacio_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new \App\Exports\HistoricoTipoEspacioExport($datos, $fechaInicio, $fechaFin, $tipoEspacio), $filename);
        } catch (\Exception $e) {
            \Log::error('Error al exportar a Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    private function exportarHistoricoTipoEspacioPDF($datos, $fechaInicio, $fechaFin, $tipoEspacio, $total_reservas, $completadas, $canceladas, $en_progreso)
    {
        try {
            $data = [
                'datos' => $datos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'fecha_inicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                'tipo_espacio' => $tipoEspacio ?: 'Todos',
                'total_registros' => $total_reservas,
                'total_reservas' => $total_reservas,
                'completadas' => $completadas,
                'canceladas' => $canceladas,
                'en_progreso' => $en_progreso
            ];

            $filename = 'historico_tipo_espacio_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.historico-tipo-espacio', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Error al exportar a PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function obtenerNombreUsuario($reserva)
    {
        if ($reserva->profesor) {
            return $reserva->profesor->name ?? 'Profesor no encontrado';
        } elseif ($reserva->solicitante) {
            return $reserva->solicitante->nombre ?? 'Solicitante no encontrado';
        }
        return 'Usuario no encontrado';
    }

    private function obtenerTipoUsuario($reserva)
    {
        if ($reserva->profesor) {
            return 'Profesor';
        } elseif ($reserva->solicitante) {
            return ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
        }
        return 'N/A';
    }

    private function agruparReservasPorSesion($reservas)
    {
        $grupos = [];
        $grupoActual = [];
        $horaFinGrupo = null;

        foreach ($reservas as $reserva) {
            // Asegurar que fecha_reserva es un objeto Carbon
            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva
                : Carbon::parse($reserva->fecha_reserva);

            $horaEntrada = Carbon::parse($fechaReserva->format('Y-m-d') . ' ' . $reserva->hora);
            $horaSalida = $reserva->hora_salida
                ? Carbon::parse($fechaReserva->format('Y-m-d') . ' ' . $reserva->hora_salida)
                : $horaEntrada->copy()->addHours(2);

            // Si el grupo está vacío o hay solapamiento, agregar al grupo actual
            if (empty($grupoActual) || ($horaFinGrupo && $horaEntrada->lte($horaFinGrupo))) {
                $grupoActual[] = $reserva;

                // Actualizar hora fin del grupo (la más tardía)
                if (!$horaFinGrupo || $horaSalida->gt($horaFinGrupo)) {
                    $horaFinGrupo = $horaSalida;
                }
            } else {
                // No hay solapamiento, guardar grupo anterior y crear uno nuevo
                if (count($grupoActual) > 0) {
                    $primeraReserva = $grupoActual[0];
                    $primeraFecha = $primeraReserva->fecha_reserva instanceof Carbon
                        ? $primeraReserva->fecha_reserva
                        : Carbon::parse($primeraReserva->fecha_reserva);

                    $grupos[] = [
                        'reservas' => $grupoActual,
                        'hora_inicio' => Carbon::parse($primeraFecha->format('Y-m-d') . ' ' . $primeraReserva->hora),
                        'hora_fin' => $horaFinGrupo,
                        'fecha' => $primeraFecha
                    ];
                }

                $grupoActual = [$reserva];
                $horaFinGrupo = $horaSalida;
            }
        }

        // Agregar el último grupo
        if (count($grupoActual) > 0) {
            $primeraReserva = $grupoActual[0];
            $primeraFecha = $primeraReserva->fecha_reserva instanceof Carbon
                ? $primeraReserva->fecha_reserva
                : Carbon::parse($primeraReserva->fecha_reserva);

            $grupos[] = [
                'reservas' => $grupoActual,
                'hora_inicio' => Carbon::parse($primeraFecha->format('Y-m-d') . ' ' . $primeraReserva->hora),
                'hora_fin' => $horaFinGrupo,
                'fecha' => $primeraFecha
            ];
        }

        return $grupos;
    }

    private function prepareAuditorioReportData($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        $auditorios = Espacio::where('tipo_espacio', 'Auditorio')->get();
        $idsAuditorios = $auditorios->pluck('id_espacio');
        $totalAuditorios = $auditorios->count();

        $totalOcupados = Espacio::where('tipo_espacio', 'Auditorio')->where('estado', 'Ocupado')->count();

        $reservasRaw = Reserva::whereIn('id_espacio', $idsAuditorios)
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->with(['espacio', 'profesor', 'solicitante', 'asignatura'])
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'asc')
            ->get();

        // Cálculo de horas
        $horasUtilizadas = $reservasRaw->sum(function ($r) {
            if ($r->hora && $r->hora_salida) {
                return Carbon::parse($r->hora)->diffInHours(Carbon::parse($r->hora_salida), true);
            }
            return 0;
        });

        $horasDisponibles = 0;
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $horasDisponibles += $totalAuditorios * $this->occupancyService->horasPorTurno(null, $fecha);
            }
        }

        $promedioUtilizacion = $horasDisponibles > 0 ? round(($horasUtilizadas / $horasDisponibles) * 100) : 0;

        $historico = $reservasRaw->map(function ($reserva) {
            $usuario = $reserva->profesor->name ?? $reserva->solicitante->nombre ?? 'N/A';
            $run = $reserva->profesor->run_profesor ?? $reserva->solicitante->run_solicitante ?? 'N/A';

            $duracion = 'N/A';
            if ($reserva->hora && $reserva->hora_salida) {
                $diff = Carbon::parse($reserva->hora)->diffInMinutes(Carbon::parse($reserva->hora_salida));
                $duracion = $diff >= 60
                    ? floor($diff / 60) . 'h ' . ($diff % 60 > 0 ? ($diff % 60) . 'min' : '')
                    : $diff . ' min';
            } elseif ($reserva->estado === 'activa') {
                $duracion = 'En curso';
            }

            return [
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'espacio' => $reserva->espacio->nombre_espacio ?? 'N/A',
                'usuario' => $usuario,
                'run' => $run,
                'asignatura' => $reserva->asignatura->nombre_asignatura ?? $reserva->motivo ?? '',
                'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : ($reserva->estado === 'activa' ? 'En curso' : 'N/A'),
                'duracion' => $duracion,
                'estado' => ucfirst($reserva->estado)
            ];
        });

        return [
            'totalAuditorios' => $totalAuditorios,
            'totalOcupados' => $totalOcupados,
            'totalReservas' => $reservasRaw->count(),
            'promedioUtilizacion' => $promedioUtilizacion,
            'horasUtilizadas' => round($horasUtilizadas, 1),
            'historico' => $historico,
            'reservasRaw' => $reservasRaw
        ];
    }
}
