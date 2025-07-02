<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Carrera;
use App\Models\Incidente;
use App\Models\Acceso;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Exports\AccesosExport;

class ReporteriaController extends Controller
{
    // 1. Utilización de espacios
    public function utilizacion(Request $request)
    {
        // Lógica para obtener datos de utilización de espacios
        return view('reporteria.utilizacion');
    }
    public function exportUtilizacion($format)
    {
        // Lógica para exportar a Excel o PDF
    }

    // 2. Análisis por tipo de espacio
    public function tipoEspacio(Request $request)
    {
        $mes = now()->month;
        $anio = now()->year;

        // KPIs
        $total_tipos = \App\Models\Espacio::distinct('tipo_espacio')->count('tipo_espacio');
        $total_espacios = \App\Models\Espacio::count();
        $espacios_ocupados = \App\Models\Espacio::where('estado', 'Ocupado')->count();

        $total_reservas = \App\Models\Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();

        // Días laborales del mes actual (lunes a viernes)
        $dias_laborales = collect(range(1, now()->daysInMonth))
            ->map(function($day) use ($anio, $mes) {
                return \Carbon\Carbon::create($anio, $mes, $day);
            })
            ->filter(function($date) {
                return $date->isWeekday();
            })->count();

        // Promedio utilización (porcentaje de módulos reservados sobre el total de módulos posibles en el mes)
        $modulos_posibles = $total_espacios * $dias_laborales * 15;
        $modulos_reservados = \App\Models\Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();
        $promedio_utilizacion = $modulos_posibles > 0 ? round(($modulos_reservados / $modulos_posibles) * 100) : 0;

        // Resumen por tipo de espacio
        $tipos = \App\Models\Espacio::distinct()->pluck('tipo_espacio');
        $resumen = [];
        $labels_grafico = [];
        $data_grafico = [];
        $data_reservas_grafico = [];
        foreach ($tipos as $tipo) {
            $espacios = \App\Models\Espacio::where('tipo_espacio', $tipo)->pluck('id_espacio');
            $total_espacios_tipo = $espacios->count();
            $reservas_tipo = \App\Models\Reserva::whereIn('id_espacio', $espacios)
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio)
                ->get();
            $total_reservas_tipo = $reservas_tipo->count();
            $horas_utilizadas = $reservas_tipo->sum(function($r) {
                return $r->hora && $r->hora_salida ? \Carbon\Carbon::parse($r->hora)->diffInMinutes(\Carbon\Carbon::parse($r->hora_salida))/60 : 0;
            });
            $capacidad = $total_espacios_tipo * $dias_laborales * 15;
            $promedio = $capacidad > 0 ? round(($total_reservas_tipo / $capacidad) * 100) : 0;
            $estado = $promedio >= 80 ? 'Óptimo' : ($promedio >= 40 ? 'Medio uso' : 'Bajo uso');
            $resumen[] = [
                'nombre' => $tipo,
                'total_espacios' => $total_espacios_tipo,
                'total_reservas' => $total_reservas_tipo,
                'horas_utilizadas' => round($horas_utilizadas),
                'promedio' => $promedio,
                'estado' => $estado,
            ];
            $labels_grafico[] = $tipo;
            $data_grafico[] = $promedio;
            $data_reservas_grafico[] = $total_reservas_tipo;
        }

        // --- POR HORARIOS ---
        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $tiposEspacioDisponibles = $tipos;
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles)) $diaActual = 'lunes';

        // Ocupación por horarios: [tipo][dia][modulo] = porcentaje
        $ocupacionHorarios = [];
        foreach ($tiposEspacioDisponibles as $tipo) {
            foreach ($diasDisponibles as $dia) {
                // Definir módulos para cada día (1-15)
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $totalEspacios = \App\Models\Espacio::where('tipo_espacio', $tipo)->count();
                    if ($totalEspacios === 0) {
                        $ocupacionHorarios[$tipo][$dia][$moduloNum] = 0;
                        continue;
                    }
                    $ocupados = \App\Models\Planificacion_Asignatura::where('id_modulo', $dia.'.'.$moduloNum)
                        ->whereHas('espacio', function($q) use ($tipo) {
                            $q->where('tipo_espacio', $tipo);
                        })
                        ->count();
                    $ocupacionHorarios[$tipo][$dia][$moduloNum] = round(($ocupados / $totalEspacios) * 100);
                }
            }
        }

        return view('reporteria.tipo-espacio', compact(
            'total_tipos',
            'total_espacios',
            'espacios_ocupados',
            'total_reservas',
            'promedio_utilizacion',
            'resumen',
            'labels_grafico',
            'data_grafico',
            'data_reservas_grafico',
            'diasDisponibles',
            'tiposEspacioDisponibles',
            'diaActual',
            'ocupacionHorarios'
        ));
    }

    public function exportTipoEspacio($format)
    {
        // Obtener todos los tipos de espacio distintos
        $tipos = \App\Models\Espacio::select('tipo_espacio')->distinct()->pluck('tipo_espacio');
        $totalReservas = \App\Models\Reserva::where('estado', 'activa')->count();
        $tiposEspacio = [];
        foreach ($tipos as $tipo) {
            $reservasTipo = \App\Models\Reserva::whereHas('espacio', function ($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')->count();
            $utilizacion = $totalReservas > 0 ? round(($reservasTipo / $totalReservas) * 100) : 0;
            $mesAnterior = now()->subMonth();
            $reservasTipoMesAnterior = \App\Models\Reserva::whereHas('espacio', function ($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')
              ->whereMonth('fecha_reserva', $mesAnterior->month)
              ->whereYear('fecha_reserva', $mesAnterior->year)
              ->count();
            $reservasTipoActual = $reservasTipo;
            $diferencia = $reservasTipoMesAnterior > 0 ? round((($reservasTipoActual - $reservasTipoMesAnterior) / $reservasTipoMesAnterior) * 100) : 0;
            $comparativa = $reservasTipoMesAnterior == 0 ? 'Sin datos previos' : ($diferencia > 0 ? '+' : '') . $diferencia . '% respecto al mes anterior';
            $tiposEspacio[] = [
                'nombre' => $tipo,
                'utilizacion' => $utilizacion . '%',
                'comparativa' => $comparativa,
            ];
        }
        $total_tipos = count($tipos);
        $total_espacios = \App\Models\Espacio::count();
        $espacios_ocupados = \App\Models\Espacio::where('estado', 'Ocupado')->count();
        $total_reservas = \App\Models\Reserva::where('estado', 'activa')->count();
        $promedio_utilizacion = $total_espacios > 0 ? round(($espacios_ocupados / $total_espacios) * 100) : 0;

        // Resumen detallado por tipo de espacio
        $resumen = [];
        foreach ($tipos as $tipo) {
            $total_espacios_tipo = \App\Models\Espacio::where('tipo_espacio', $tipo)->count();
            $total_reservas_tipo = \App\Models\Reserva::whereHas('espacio', function ($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')->count();
            $horas_utilizadas = \App\Models\Reserva::whereHas('espacio', function ($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')->sum('duracion_minutos') / 60;
            $promedio = $total_espacios_tipo > 0 ? round(($total_reservas_tipo / $total_espacios_tipo) * 100) : 0;
            $estado = $promedio >= 80 ? 'Óptimo' : ($promedio >= 50 ? 'Medio uso' : 'Bajo uso');
            $resumen[] = [
                'nombre' => $tipo,
                'total_espacios' => $total_espacios_tipo,
                'total_reservas' => $total_reservas_tipo,
                'horas_utilizadas' => round($horas_utilizadas, 1),
                'promedio' => $promedio,
                'estado' => $estado
            ];
        }

        // Datos para gráficos
        $labels_grafico = $tipos->toArray();
        $data_grafico = [];
        $data_reservas_grafico = [];
        foreach ($tipos as $tipo) {
            $espacios_tipo = \App\Models\Espacio::where('tipo_espacio', $tipo)->count();
            $reservas_tipo = \App\Models\Reserva::whereHas('espacio', function ($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')->count();
            $data_grafico[] = $espacios_tipo > 0 ? round(($reservas_tipo / $espacios_tipo) * 100) : 0;
            $data_reservas_grafico[] = $reservas_tipo;
        }

        // Definir días disponibles (sin horariosModulos del backend)
        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $tiposEspacioDisponibles = $tipos;
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles)) $diaActual = 'lunes';

        // Ocupación por horarios: [tipo][dia][modulo] = porcentaje
        $ocupacionHorarios = [];
        foreach ($tiposEspacioDisponibles as $tipo) {
            foreach ($diasDisponibles as $dia) {
                // Definir módulos para cada día (1-15)
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $totalEspacios = \App\Models\Espacio::where('tipo_espacio', $tipo)->count();
                    if ($totalEspacios === 0) {
                        $ocupacionHorarios[$tipo][$dia][$moduloNum] = 0;
                        continue;
                    }
                    $ocupados = \App\Models\Planificacion_Asignatura::where('id_modulo', $dia.'.'.$moduloNum)
                        ->whereHas('espacio', function($q) use ($tipo) {
                            $q->where('tipo_espacio', $tipo);
                        })
                        ->count();
                    $ocupacionHorarios[$tipo][$dia][$moduloNum] = round(($ocupados / $totalEspacios) * 100);
                }
            }
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reporteria.pdf.tipo-espacio', compact('tiposEspacio', 'total_tipos', 'total_espacios', 'espacios_ocupados', 'total_reservas', 'promedio_utilizacion', 'resumen', 'labels_grafico', 'data_grafico', 'data_reservas_grafico', 'diasDisponibles', 'tiposEspacioDisponibles', 'diaActual', 'ocupacionHorarios'));
            $filename = 'tipo_espacio_qr_' . date('Y-m-d_H-i-s') . '.pdf';
            return $pdf->download($filename);
        }
        // Excel: implementar si se requiere
        return back();
    }

    public function exportHistoricoEspacios(Request $request, $format)
    {
        try {
            // Obtener filtros de la request
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $piso = $request->get('piso', '');
            $tipoUsuario = $request->get('tipo_usuario', '');
            $tipoEspacioFiltro = $request->get('tipo_espacio_filtro', '');
            $diaFiltro = $request->get('dia_filtro', '');

            // Obtener espacios filtrados
            $espaciosQuery = Espacio::whereHas('piso.facultad', function ($q) {
                $q->where('id_facultad', 'IT_TH');
            });
            if (!empty($piso)) {
                $espaciosQuery->whereHas('piso', function ($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }
            if (!empty($tipoEspacioFiltro)) {
                $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
            }
            $espacios = $espaciosQuery->get();

            // Obtener reservas filtradas
            $reservasQuery = Reserva::with(['espacio', 'user'])
                ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
                ->where('estado', 'activa')
                ->whereHas('espacio', function($q) use ($espacios) {
                    $q->whereIn('id_espacio', $espacios->pluck('id_espacio'));
                });
            
            if (!empty($diaFiltro)) {
                $numeroDia = $this->obtenerNumeroDia($diaFiltro);
                $reservasQuery->whereRaw('DAYOFWEEK(fecha_reserva) = ?', [$numeroDia]);
            }
            
            if (!empty($tipoUsuario)) {
                $reservasQuery->whereHas('user', function($q) use ($tipoUsuario) {
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
            
            $reservas = $reservasQuery->orderBy('fecha_reserva', 'desc')
                                     ->orderBy('hora', 'desc')
                                     ->get();

            // Preparar datos para exportación
            $datosExport = [];
            foreach ($reservas as $reserva) {
                $duracionMinutos = 0;
                $horasUtilizadas = 0;
                
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    $duracionMinutos = $inicio->diffInMinutes($fin);
                    $horasUtilizadas = $duracionMinutos / 60;
                }
                
                $duracionFormateada = $duracionMinutos > 0 ? 
                    (floor($duracionMinutos / 60) > 0 ? floor($duracionMinutos / 60) . 'h ' : '') . 
                    ($duracionMinutos % 60) . ' min' : '0 min';

                $datosExport[] = [
                    'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                    'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : 'N/A',
                    'espacio' => $reserva->espacio->nombre_espacio . ' (' . $reserva->espacio->id_espacio . ')',
                    'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                    'tipo_usuario' => ucfirst($this->determinarTipoUsuario($reserva->user)),
                    'horas_utilizadas' => round($horasUtilizadas, 1),
                    'duracion' => $duracionFormateada,
                    'estado' => ucfirst($reserva->estado)
                ];
            }

            if ($format === 'excel') {
                return $this->exportarHistoricoExcel($datosExport, $fechaInicio, $fechaFin);
            } elseif ($format === 'pdf') {
                return $this->exportarHistoricoPDF($datosExport, $fechaInicio, $fechaFin);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    private function exportarHistoricoExcel($datos, $fechaInicio, $fechaFin)
    {
        try {
            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
            
            return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                private $datos;
                
                public function __construct($datos) {
                    $this->datos = $datos;
                }
                
                public function array(): array {
                    return $this->datos;
                }
                
                public function headings(): array {
                    return [
                        'Fecha',
                        'Hora Inicio',
                        'Hora Fin',
                        'Espacio',
                        'Usuario',
                        'Tipo Usuario',
                        'Horas Utilizadas',
                        'Duración',
                        'Estado'
                    ];
                }
                
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                    return [
                        1 => ['font' => ['bold' => true]],
                    ];
                }
            }, $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    private function exportarHistoricoPDF($datos, $fechaInicio, $fechaFin)
    {
        try {
            $data = [
                'datos' => $datos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'total_registros' => count($datos)
            ];

            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.pdf';
            $pdf = Pdf::loadView('reporteria.pdf.historico-espacios', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }

    // 3. Accesos registrados
    public function accesos(Request $request)
    {
        // Obtener datos para filtros primero
        $pisos = $this->obtenerPisosDisponibles();
        $espacios = $this->obtenerEspaciosDisponibles();
        $tiposUsuario = $this->obtenerTiposUsuario();

        // Obtener filtros de la request
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $piso = $request->get('piso', ''); // Valor vacío por defecto
        $tipoUsuario = $request->get('tipo_usuario', ''); // Valor vacío por defecto
        $espacio = $request->get('espacio', ''); // Valor vacío por defecto

        // Obtener accesos registrados (reservas activas)
        $accesos = $this->obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso, $tipoUsuario, $espacio);

        return view('reporteria.accesos', compact(
            'accesos',
            'fechaInicio',
            'fechaFin',
            'piso',
            'tipoUsuario',
            'espacio',
            'pisos',
            'espacios',
            'tiposUsuario'
        ));
    }

    // Método para limpiar filtros
    public function limpiarFiltrosAccesos()
    {
        return redirect()->route('reporteria.accesos')->with('success', 'Filtros limpiados correctamente');
    }

    public function exportAccesos($format)
    {
        try {
            // Obtener todos los accesos para exportar
            $accesos = $this->obtenerAccesosRegistrados(
                Carbon::now()->startOfMonth()->format('Y-m-d'),
                Carbon::now()->endOfMonth()->format('Y-m-d')
            );

            if ($accesos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay datos para exportar');
            }

            if ($format === 'excel') {
                return $this->exportarAccesosExcel($accesos);
            } elseif ($format === 'pdf') {
                return $this->exportarAccesosPDF($accesos);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function exportAccesosConFiltros(Request $request, $format)
    {
        try {
            // Obtener filtros de la request
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $piso = $request->get('piso');
            $tipoUsuario = $request->get('tipo_usuario');
            $espacio = $request->get('espacio');

            // Obtener accesos con filtros aplicados
            $accesos = $this->obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso, $tipoUsuario, $espacio);

            if ($accesos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay datos para exportar con los filtros aplicados');
            }

            if ($format === 'excel') {
                return $this->exportarAccesosExcel($accesos);
            } elseif ($format === 'pdf') {
                return $this->exportarAccesosPDF($accesos);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function getDetallesAcceso($id)
    {
        $reserva = Reserva::with(['user', 'espacio.piso.facultad.sede.universidad'])
            ->where('id_reserva', $id)
            ->first();

        if (!$reserva) {
            return response()->json(['error' => 'Acceso no encontrado'], 404);
        }

        $detalles = [
            'id' => $reserva->id_reserva,
            'usuario' => [
                'nombre' => $reserva->user->name ?? 'Usuario no encontrado',
                'run' => $reserva->user->run ?? 'N/A',
                'email' => $reserva->user->email ?? 'N/A',
                'celular' => $reserva->user->celular ?? 'N/A',
                'tipo_usuario' => $this->determinarTipoUsuario($reserva->user),
                'universidad' => $reserva->user->universidad->nombre_universidad ?? 'N/A',
                'facultad' => $reserva->user->facultad->nombre_facultad ?? 'N/A',
                'carrera' => $reserva->user->carrera->nombre_carrera ?? 'N/A',
            ],
            'espacio' => [
                'nombre' => $reserva->espacio->nombre_espacio ?? 'Espacio no encontrado',
                'tipo' => $reserva->espacio->tipo_espacio ?? 'N/A',
                'capacidad' => $reserva->espacio->capacidad ?? 'N/A',
                'piso' => $reserva->espacio->piso->numero_piso ?? 'N/A',
                'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                'sede' => $reserva->espacio->piso->facultad->sede->nombre_sede ?? 'N/A',
                'universidad' => $reserva->espacio->piso->facultad->sede->universidad->nombre_universidad ?? 'N/A',
            ],
            'reserva' => [
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_entrada' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i:s') : 'En curso',
                'tipo_reserva' => $reserva->tipo_reserva ?? 'Directa',
                'estado' => $reserva->estado,
                'duracion' => $this->calcularDuracion($reserva->hora, $reserva->hora_salida),
            ],
            'incidencias' => $this->obtenerIncidencias($reserva->id_reserva)
        ];

        return response()->json($detalles);
    }

    // 4. Reportes por unidad académica
    public function unidadAcademica(Request $request)
    {
        return view('reporteria.unidad-academica');
    }
    public function exportUnidadAcademica($format)
    {
    }

    /**
     * Obtener accesos registrados con filtros
     */
    public function obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $espacio = null)
    {
        $query = Reserva::with(['user', 'espacio.piso.facultad'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada']) // Mostrar activas y finalizadas
            ->whereNotNull('hora') // Solo las que tienen hora de entrada (escaneo QR)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc');

        // Filtrar por piso
        if (!empty($piso)) {
            $query->whereHas('espacio.piso', function ($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }

        // Filtrar por tipo de usuario
        if (!empty($tipoUsuario)) {
            $query->whereHas('user', function ($q) use ($tipoUsuario) {
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

        // Filtrar por espacio
        if (!empty($espacio)) {
            $query->whereHas('espacio', function ($q) use ($espacio) {
                $q->where('nombre_espacio', 'like', '%' . $espacio . '%');
            });
        }

        return $query->get()->map(function ($reserva) {
            return [
                'id' => $reserva->id_reserva,
                'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                'run' => $reserva->user->run ?? 'N/A',
                'email' => $reserva->user->email ?? 'N/A',
                'tipo_usuario' => $this->determinarTipoUsuario($reserva->user),
                'espacio' => $reserva->espacio->nombre_espacio ?? 'Espacio no encontrado',
                'id_espacio' => $reserva->espacio->id_espacio ?? '',
                'piso' => $reserva->espacio->piso->numero_piso ?? 'N/A',
                'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_entrada' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i:s') : 'En curso',
                'tipo_reserva' => $reserva->tipo_reserva ?? 'Directa',
                'estado' => $reserva->estado,
                'duracion' => $this->calcularDuracion($reserva->hora, $reserva->hora_salida),
                'incidencias' => $this->obtenerIncidencias($reserva->id_reserva)
            ];
        });
    }

    /**
     * Determinar el tipo de usuario basado en los campos del modelo
     */
    private function determinarTipoUsuario($user)
    {
        if (!$user) {
            return 'externo';
        }

        if ($user->tipo_profesor) {
            return 'profesor';
        }

        if ($user->id_carrera) {
            return 'estudiante';
        }

        if ($user->id_facultad) {
            return 'administrativo';
        }

        return 'externo';
    }

    /**
     * Calcular duración de la reserva
     */
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

    /**
     * Obtener incidencias de la reserva
     */
    private function obtenerIncidencias($idReserva)
    {
        // Aquí puedes implementar la lógica para obtener incidencias
        // Por ahora retornamos un array vacío
        return [];
    }

    /**
     * Obtener pisos disponibles
     */
    private function obtenerPisosDisponibles()
    {
        return \App\Models\Piso::whereHas('facultad', function ($query) {
            $query->where('id_facultad', 'IT_TH');
        })
        ->orderBy('numero_piso')
        ->pluck('numero_piso', 'numero_piso');
    }

    /**
     * Obtener espacios disponibles
     */
    private function obtenerEspaciosDisponibles()
    {
        return \App\Models\Espacio::whereHas('piso.facultad', function ($query) {
            $query->where('id_facultad', 'IT_TH');
        })
        ->orderBy('nombre_espacio')
        ->pluck('nombre_espacio', 'nombre_espacio');
    }

    /**
     * Obtener tipos de usuario
     */
    private function obtenerTiposUsuario()
    {
        return [
            'profesor' => 'Profesor',
            'estudiante' => 'Estudiante',
            'administrativo' => 'Administrativo',
            'externo' => 'Externo'
        ];
    }

    /**
     * Exportar accesos a Excel
     */
    private function exportarAccesosExcel($accesos)
    {
        try {
            // Obtener código de espacio
            $codigoEspacio = $accesos->first()['id_espacio'] ?? 'sin_codigo';
            // Obtener año y semestre
            $anio = date('Y');
            $mes = date('n');
            $semestre = ($mes >= 3 && $mes <= 7) ? 1 : 2; 
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . '.xlsx';
            return Excel::download(new AccesosExport($accesos), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    /**
     * Exportar accesos a PDF
     */
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
            // Obtener año y semestre
            $anio = date('Y');
            $mes = date('n');
            $semestre = ($mes >= 3 && $mes <= 7) ? 1 : 2; // Marzo-Julio = 1, Agosto-Febrero = 2
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . '.pdf';
            $pdf = Pdf::loadView('reporteria.pdf.accesos', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tipos de espacio disponibles
     */
    private function obtenerTiposEspacioDisponibles()
    {
        return \App\Models\Espacio::select('tipo_espacio')
            ->distinct()
            ->orderBy('tipo_espacio')
            ->pluck('tipo_espacio', 'tipo_espacio');
    }

    /**
     * Obtener días disponibles
     */
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

    /**
     * Generar datos dinámicos de ocupación por horarios basados en reservas reales
     */
    private function generarDatosOcupacionHorarios($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $tipoEspacioFiltro = null, $diaFiltro = null)
    {
        // Definir los módulos y sus rangos horarios
        $modulosHorarios = [
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
            15 => ['inicio' => '22:10', 'fin' => '23:00']
        ];

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        
        // Aplicar filtro de día si está especificado
        if (!empty($diaFiltro) && in_array($diaFiltro, $dias)) {
            $dias = [$diaFiltro];
        }

        // Obtener tipos de espacio
        $tiposQuery = \App\Models\Espacio::query();
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
                    $espaciosDelTipo = \App\Models\Espacio::where('tipo_espacio', $tipo);
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

    /**
     * Calcular horarios pico basados en los datos de ocupación
     */
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
        uasort($promediosModulos, function($a, $b) {
            return $b['promedio'] <=> $a['promedio'];
        });

        // Tomar los 3 horarios con mayor ocupación
        $contador = 0;
        foreach ($promediosModulos as $moduloNum => $data) {
            if ($contador >= 3) break;
            
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

    public function historicoAjax(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tipoEspacio = $request->input('tipo_espacio', '');
        $perPage = 10;

        $query = \App\Models\Reserva::with(['espacio', 'user'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin]);
        if ($tipoEspacio) {
            $query->whereHas('espacio', function($q) use ($tipoEspacio) {
                $q->where('tipo_espacio', $tipoEspacio);
            });
        }
        $query->orderBy('fecha_reserva', 'desc')->orderBy('hora', 'desc');
        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(function($reserva) {
            return [
                'fecha' => $reserva->fecha_reserva,
                'hora_inicio' => $reserva->hora ? \Carbon\Carbon::parse($reserva->hora)->format('H:i') : '',
                'hora_termino' => $reserva->hora_salida ? \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i') : '',
                'espacio' => $reserva->espacio->nombre_espacio ?? '',
                'tipo_espacio' => $reserva->espacio->tipo_espacio ?? '',
                'usuario' => $reserva->user->name ?? '',
                'estado' => $reserva->estado,
            ];
        });

        // KPIs inferiores
        $total = $paginator->total();
        $completadas = (clone $query)->where('estado', 'completada')->count();
        $canceladas = (clone $query)->where('estado', 'cancelada')->count();
        $en_progreso = (clone $query)->where('estado', 'en progreso')->count();

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $total,
            'completadas' => $completadas,
            'canceladas' => $canceladas,
            'en_progreso' => $en_progreso,
        ]);
    }
} 