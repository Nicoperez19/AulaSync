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
    public function utilizacion(Request $request) {
        // Lógica para obtener datos de utilización de espacios
        return view('reporteria.utilizacion');
    }
    public function exportUtilizacion($format) {
        // Lógica para exportar a Excel o PDF
    }

    // 2. Análisis por tipo de espacio
    public function tipoEspacio(Request $request) {
        // Filtros
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $piso = $request->get('piso', '');
        $tipoUsuario = $request->get('tipo_usuario', '');

        $tiposUsuario = $this->obtenerTiposUsuario();
        $pisos = $this->obtenerPisosDisponibles();

        // Tipos de espacio distintos
        $tipos = \App\Models\Espacio::query();
        if (!empty($piso)) {
            $tipos->whereHas('piso', function($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }
        $tiposEspacioNombres = $tipos->distinct()->pluck('tipo_espacio');

        // Total reservas activas en el rango
        $reservasQuery = \App\Models\Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('estado', 'activa');
        if (!empty($piso)) {
            $reservasQuery->whereHas('espacio.piso', function($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
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
        $totalReservas = $reservasQuery->count();

        // Mes anterior
        $fechaInicioAnterior = Carbon::parse($fechaInicio)->subMonth()->startOfMonth()->format('Y-m-d');
        $fechaFinAnterior = Carbon::parse($fechaInicio)->subMonth()->endOfMonth()->format('Y-m-d');
        $reservasMesAnterior = \App\Models\Reserva::whereBetween('fecha_reserva', [$fechaInicioAnterior, $fechaFinAnterior])
            ->where('estado', 'activa');
        if (!empty($piso)) {
            $reservasMesAnterior->whereHas('espacio.piso', function($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }
        if (!empty($tipoUsuario)) {
            $reservasMesAnterior->whereHas('user', function($q) use ($tipoUsuario) {
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
        $totalReservasAnterior = $reservasMesAnterior->count();

        // Datos por tipo de espacio
        $tiposEspacio = [];
        $sumaUtilizacion = 0;
        $mayorUtilizacion = null;
        $mayorValor = 0;
        foreach ($tiposEspacioNombres as $nombreTipo) {
            $reservasTipo = \App\Models\Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
                ->where('estado', 'activa')
                ->whereHas('espacio', function($q) use ($nombreTipo, $piso) {
                    $q->where('tipo_espacio', $nombreTipo);
                    if (!empty($piso)) {
                        $q->whereHas('piso', function($q2) use ($piso) {
                            $q2->where('numero_piso', $piso);
                        });
                    }
                });
            if (!empty($tipoUsuario)) {
                $reservasTipo->whereHas('user', function($q) use ($tipoUsuario) {
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
            $countTipo = $reservasTipo->count();
            $porcentaje = $totalReservas > 0 ? round(($countTipo / $totalReservas) * 100, 1) : 0;
            $sumaUtilizacion += $porcentaje;
            if ($porcentaje > $mayorValor) {
                $mayorValor = $porcentaje;
                $mayorUtilizacion = $nombreTipo;
            }
            // Comparativa con el mes anterior
            $reservasTipoAnterior = \App\Models\Reserva::whereBetween('fecha_reserva', [$fechaInicioAnterior, $fechaFinAnterior])
                ->where('estado', 'activa')
                ->whereHas('espacio', function($q) use ($nombreTipo, $piso) {
                    $q->where('tipo_espacio', $nombreTipo);
                    if (!empty($piso)) {
                        $q->whereHas('piso', function($q2) use ($piso) {
                            $q2->where('numero_piso', $piso);
                        });
                    }
                });
            if (!empty($tipoUsuario)) {
                $reservasTipoAnterior->whereHas('user', function($q) use ($tipoUsuario) {
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
            $countTipoAnterior = $reservasTipoAnterior->count();
            $comparativa = $countTipoAnterior > 0 ? round((($countTipo - $countTipoAnterior) / $countTipoAnterior) * 100, 1) : ($countTipo > 0 ? 100 : 0);
            $comparativaTexto = $countTipoAnterior > 0
                ? ($comparativa > 0 ? "+$comparativa%" : "$comparativa%") . " respecto al mes anterior"
                : ($countTipo > 0 ? "+100% respecto al mes anterior" : "Sin variación");
            $tiposEspacio[] = [
                'nombre' => $nombreTipo,
                'utilizacion' => $porcentaje,
                'comparativa' => $comparativaTexto
            ];
        }
        $total_tipos = count($tiposEspacio);
        $promedio_utilizacion = $total_tipos > 0 ? round($sumaUtilizacion / $total_tipos, 1) . '%' : '0%';
        $mayor_utilizacion = $mayorUtilizacion ?? '-';
        $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');
        return view('reporteria.tipo-espacio', compact(
            'tiposEspacio',
            'fecha_generacion',
            'total_tipos',
            'promedio_utilizacion',
            'mayor_utilizacion',
            'fechaInicio',
            'fechaFin',
            'piso',
            'tipoUsuario',
            'tiposUsuario',
            'pisos'
        ));
    }

    public function exportTipoEspacio($format) {
        // Obtener todos los tipos de espacio distintos
        $tipos = \App\Models\Espacio::select('tipo_espacio')->distinct()->pluck('tipo_espacio');
        $totalReservas = \App\Models\Reserva::where('estado', 'activa')->count();
        $tiposEspacio = [];
        foreach ($tipos as $tipo) {
            $reservasTipo = \App\Models\Reserva::whereHas('espacio', function($q) use ($tipo) {
                $q->where('tipo_espacio', $tipo);
            })->where('estado', 'activa')->count();
            $utilizacion = $totalReservas > 0 ? round(($reservasTipo / $totalReservas) * 100) : 0;
            $mesAnterior = now()->subMonth();
            $reservasTipoMesAnterior = \App\Models\Reserva::whereHas('espacio', function($q) use ($tipo) {
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
        $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reporteria.pdf.tipo-espacio', compact('tiposEspacio', 'fecha_generacion'));
            $filename = 'tipo_espacio_qr_' . date('Y-m-d_H-i-s') . '.pdf';
            return $pdf->download($filename);
        }
        // Excel: implementar si se requiere
        return back();
    }

    // 3. Accesos registrados
    public function accesos(Request $request) {
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
    public function limpiarFiltrosAccesos() {
        return redirect()->route('reporteria.accesos')->with('success', 'Filtros limpiados correctamente');
    }

    public function exportAccesos($format) {
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

    public function exportAccesosConFiltros(Request $request, $format) {
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

    public function getDetallesAcceso($id) {
        $reserva = Reserva::with(['user', 'espacio.piso.facultad', 'espacio.piso.facultad.sede.universidad'])
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
    public function unidadAcademica(Request $request) {
        return view('reporteria.unidad-academica');
    }
    public function exportUnidadAcademica($format) {
    }

    /**
     * Obtener accesos registrados con filtros
     */
    private function obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $espacio = null)
    {
        $query = Reserva::with(['user', 'espacio.piso.facultad'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('estado', 'activa')
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc');

        // Filtrar por piso
        if (!empty($piso)) {
            $query->whereHas('espacio.piso', function($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }

        // Filtrar por tipo de usuario
        if (!empty($tipoUsuario)) {
            $query->whereHas('user', function($q) use ($tipoUsuario) {
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
            $query->whereHas('espacio', function($q) use ($espacio) {
                $q->where('nombre_espacio', 'like', '%' . $espacio . '%');
            });
        }

        return $query->get()->map(function($reserva) {
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
        return \App\Models\Piso::whereHas('facultad', function($query) {
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
        return \App\Models\Espacio::whereHas('piso.facultad', function($query) {
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
            $filename = 'nombre_registrados_qr_' . date('Y-m-d_H-i-s') . '.xlsx';
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

            $pdf = Pdf::loadView('reporteria.pdf.accesos', $data);
            $filename = 'nombre_registrados_qr_' . date('Y-m-d_H-i-s') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }
} 