<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClaseNoRealizada;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\ClasesNoRealizadasReportService;

class ClasesNoRealizadasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $periodo = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    private $autoRefresh = true; // Auto-refresh siempre activo
    public $lastRecordCount = 0;
    
    // Cache para estadísticas (evitar múltiples consultas)
    private $cachedEstadisticas = null;

    public $reagendar_id = null; // ID de clase para abrir modal automáticamente

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'periodo' => ['except' => ''],
        'reagendar_id' => ['except' => ''],
    ];

    protected $listeners = [
        'updateClase',
        'confirmDelete',
        'reagendarClase',
        'marcarComoRecuperada'
    ];

    public function exportarPDFSemanal()
    {
        try {
            $reportService = new ClasesNoRealizadasReportService();
            
            // Usar las fechas del filtro o la semana actual
            $fechaInicio = $this->fecha_inicio ? Carbon::parse($this->fecha_inicio) : Carbon::now()->startOfWeek();
            $fechaFin = $this->fecha_fin ? Carbon::parse($this->fecha_fin) : Carbon::now()->endOfWeek();
            
            $pdf = $reportService->generarPDFSemanal($fechaInicio, $fechaFin);
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'Reporte_Semanal_Clases_No_Realizadas.pdf');
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDF semanal: ' . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function exportarPDFMensual()
    {
        try {
            $reportService = new ClasesNoRealizadasReportService();
            
            // Usar el mes actual o el del filtro
            $fecha = $this->fecha_inicio ? Carbon::parse($this->fecha_inicio) : Carbon::now();
            $mes = $fecha->month;
            $anio = $fecha->year;
            
            $pdf = $reportService->generarPDFMensual($mes, $anio);
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'Reporte_Mensual_Clases_No_Realizadas.pdf');
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDF mensual: ' . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function mount()
    {
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Inicializar el conteo de registros usando cache
        $this->lastRecordCount = $this->getEstadisticasOptimizadas()['total'];
        
        // Si viene un reagendar_id desde URL, abrir modal automáticamente
        if ($this->reagendar_id) {
            $this->dispatch('auto-open-reagendar', ['id' => $this->reagendar_id]);
        }
    }

    public function refresh()
    {
        // Método para refrescar manualmente los datos
        $this->cachedEstadisticas = null; // Limpiar cache
        $this->resetPage();
    }

    public function aplicarFiltros()
    {
        // Validar fechas si ambas están establecidas
        if ($this->fecha_inicio && $this->fecha_fin) {
            if (Carbon::parse($this->fecha_inicio)->gt(Carbon::parse($this->fecha_fin))) {
                $this->dispatch('show-error', ['message' => 'La fecha inicio no puede ser mayor que la fecha fin']);
                return;
            }
        }
        
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->estado = '';
        $this->periodo = '';
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->cachedEstadisticas = null; // Limpiar cache
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function showEditModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        $this->dispatch('show-edit-modal', [
            'id' => $id,
            'estado' => $clase->estado,
            'observaciones' => $clase->observaciones ?? '',
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha' => $clase->fecha_clase->format('d/m/Y'),
            'espacio' => $clase->id_espacio,
        ]);
    }

    public function updateClase($id, $estado, $observaciones)
    {
        // Validar los datos recibidos
        if (empty($estado) || !in_array($estado, ['no_realizada', 'justificado'])) {
            $this->dispatch('show-error', ['message' => 'El estado seleccionado no es válido']);
            return;
        }

        if (strlen($observaciones) > 1000) {
            $this->dispatch('show-error', ['message' => 'Las observaciones no pueden exceder 1000 caracteres']);
            return;
        }

        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            $clase->update([
                'estado' => $estado,
                'observaciones' => $observaciones,
            ]);
            
            $this->dispatch('show-success', ['message' => 'Clase actualizada exitosamente']);
            $this->refresh(); // Refrescar datos después de actualizar
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al actualizar la clase: ' . $e->getMessage()]);
        }
    }

    public function showReagendarModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        // Detectar automáticamente cuántos módulos no se realizaron
        // Contar todos los módulos programados para esta asignatura
        $totalModulosProgramados = \App\Models\Planificacion_Asignatura::where('id_asignatura', $clase->id_asignatura)
            ->count();
        
        // Log para debugging
        Log::info("Clase no realizada: {$clase->id}, Asignatura: {$clase->id_asignatura}, Total módulos programados: {$totalModulosProgramados}");

        $this->dispatch('show-reagendar-modal', [
            'id' => $id,
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha_original' => $clase->fecha_clase->format('d/m/Y'),
            'espacio_original' => $clase->id_espacio,
            'modulo_original' => $clase->id_modulo,
            'totalModulosProgramados' => $totalModulosProgramados,
        ]);
    }

    public function reagendarClase($id, $nuevaFecha, $nuevoEspacio, $nuevoModulo, $cantidadModulos = 1, $observaciones = '')
    {
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Convertir cantidadModulos a entero y validar
            $cantidadModulos = max(1, min(15, (int)$cantidadModulos));
            
            // Validar que la nueva fecha no sea anterior a hoy
            if (Carbon::parse($nuevaFecha)->lt(Carbon::today())) {
                $this->dispatch('show-error', ['message' => 'La nueva fecha no puede ser anterior a hoy']);
                return;
            }
            
            // Obtener el día de la nueva fecha
            $fechaParsed = Carbon::parse($nuevaFecha);
            $nombreDia = strtolower($fechaParsed->format('l'));
            
            // Mapear nombre del día en inglés a prefijo en BD (mayúsculas)
            $diasMap = [
                'monday' => 'LU',
                'tuesday' => 'MA',
                'wednesday' => 'MI',
                'thursday' => 'JU',
                'friday' => 'VI',
                'saturday' => 'SA',
                'sunday' => 'DO'
            ];
            $prefijoDia = $diasMap[$nombreDia] ?? 'LU';
            
            // Construir el id_modulo completo con todos los módulos seleccionados
            if ($cantidadModulos > 1) {
                // Múltiples módulos: construir array LU.1,LU.2,LU.3
                $modulosNuevos = [];
                for ($i = 0; $i < $cantidadModulos; $i++) {
                    $numeroModulo = $nuevoModulo + $i;
                    $modulosNuevos[] = "{$prefijoDia}.{$numeroModulo}";
                }
                $nuevoModuloId = implode(',', $modulosNuevos);
            } else {
                // Un solo módulo
                $nuevoModuloId = "{$prefijoDia}.{$nuevoModulo}";
            }
            
            // Crear observación completa indicando que debe recuperarse
            $observacionReagendamiento = "⚠️ CLASE PENDIENTE DE RECUPERACIÓN - ";
            $observacionReagendamiento .= "Reagendada desde {$clase->fecha_clase->format('d/m/Y')} ({$clase->id_espacio}, módulo {$clase->id_modulo}) ";
            $observacionReagendamiento .= "a {$nuevaFecha} ({$nuevoEspacio}, módulo {$nuevoModuloId}). ";
            $observacionReagendamiento .= "El profesor debe recuperar esta clase en la nueva fecha programada.";
            
            if ($observaciones) {
                $observacionReagendamiento .= " Motivo: {$observaciones}";
            }
            
            // Cambiar estado a PENDIENTE (esperando recuperación)
            $clase->update([
                'fecha_clase' => $nuevaFecha,
                'id_espacio' => $nuevoEspacio,
                'id_modulo' => $nuevoModuloId,
                'estado' => 'pendiente', // Estado específico para clases reagendadas
                'observaciones' => $observacionReagendamiento,
            ]);
            
            Log::info("Clase reagendada exitosamente", [
                'clase_id' => $id,
                'fecha_original' => $clase->fecha_clase->format('Y-m-d'),
                'fecha_nueva' => $nuevaFecha,
                'espacio_original' => $clase->id_espacio,
                'espacio_nuevo' => $nuevoEspacio,
                'modulo_original' => $clase->id_modulo,
                'modulo_nuevo' => $nuevoModuloId,
                'estado' => 'pendiente',
            ]);
            
            $this->dispatch('show-success', ['message' => 'Clase reagendada exitosamente. Quedará como PENDIENTE hasta que se confirme su realización.']);
            $this->refresh(); // Refrescar datos después de reagendar
        } catch (\Exception $e) {
            Log::error("Error al reagendar clase: " . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al reagendar la clase: ' . $e->getMessage()]);
        }
    }

    public function marcarComoRecuperada($id)
    {
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Verificar que esté en estado pendiente
            if ($clase->estado !== 'pendiente') {
                $this->dispatch('show-error', ['message' => 'Solo se pueden marcar como recuperadas las clases en estado PENDIENTE']);
                return;
            }
            
            // Actualizar observaciones agregando confirmación de recuperación
            $observacionAnterior = $clase->observaciones ?? '';
            $nuevaObservacion = $observacionAnterior . "\n\n✓ CLASE RECUPERADA - Confirmada el " . Carbon::now()->format('d/m/Y H:i');
            
            // Cambiar estado a justificado
            $clase->update([
                'estado' => 'justificado',
                'observaciones' => $nuevaObservacion,
            ]);
            
            Log::info("Clase marcada como recuperada", [
                'clase_id' => $id,
                'fecha_clase' => $clase->fecha_clase->format('Y-m-d'),
                'profesor' => $clase->profesor->name ?? 'N/A',
            ]);
            
            $this->dispatch('show-success', ['message' => 'Clase marcada como recuperada exitosamente']);
            $this->refresh();
        } catch (\Exception $e) {
            Log::error("Error al marcar clase como recuperada: " . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    public function showDeleteModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha' => $clase->fecha_clase->format('d/m/Y'),
        ]);
    }

    public function toggleEstado($id)
    {
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Cambiar entre los dos estados: no_realizada -> justificado -> no_realizada
            $nuevoEstado = $clase->estado === 'no_realizada' ? 'justificado' : 'no_realizada';
            
            $clase->update(['estado' => $nuevoEstado]);
            
            session()->flash('message', "Estado cambiado a: " . ($nuevoEstado === 'no_realizada' ? 'Clase no realizada' : 'Justificado'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            $clase->delete();
            
            $this->dispatch('show-success', ['message' => 'Registro eliminado exitosamente']);
            $this->refresh(); // Refrescar datos después de eliminar
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }

    public function getEstadisticas()
    {
        return $this->getEstadisticasOptimizadas();
    }

    /**
     * Obtener estadísticas de forma optimizada usando una sola consulta con agregación
     */
    private function getEstadisticasOptimizadas()
    {
        // Retornar cache si existe
        if ($this->cachedEstadisticas !== null) {
            return $this->cachedEstadisticas;
        }

        $hoy = Carbon::now()->toDateString();
        
        // Una sola consulta con agregación condicional, excluyendo atrasos
        $stats = DB::table('clases_no_realizadas')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN estado = 'no_realizada' THEN 1 ELSE 0 END) as no_realizadas"),
                DB::raw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes"),
                DB::raw("SUM(CASE WHEN estado = 'justificado' THEN 1 ELSE 0 END) as justificados"),
            ])
            // Excluir registros que tienen un atraso correspondiente
            ->whereNotExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('profesor_atrasos')
                    ->whereColumn('profesor_atrasos.id_asignatura', 'clases_no_realizadas.id_asignatura')
                    ->whereColumn('profesor_atrasos.id_espacio', 'clases_no_realizadas.id_espacio')
                    ->whereColumn('profesor_atrasos.id_modulo', 'clases_no_realizadas.id_modulo')
                    ->whereColumn('profesor_atrasos.fecha', 'clases_no_realizadas.fecha_clase');
            })
            ->when($this->periodo, function($q) {
                $q->where('periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function($q) {
                $q->whereBetween('fecha_clase', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->where(function($q) use ($hoy) {
                $q->where('estado', 'pendiente')
                    ->orWhereDate('fecha_clase', '<', $hoy)
                    ->orWhereDate('fecha_clase', $hoy);
            })
            ->first();

        $this->cachedEstadisticas = [
            'total' => (int) ($stats->total ?? 0),
            'no_realizadas' => (int) ($stats->no_realizadas ?? 0),
            'pendientes' => (int) ($stats->pendientes ?? 0),
            'justificados' => (int) ($stats->justificados ?? 0),
        ];

        return $this->cachedEstadisticas;
    }

    /**
     * Construir la query base con filtros aplicados (reutilizable)
     */
    private function buildBaseQuery()
    {
        $hoy = Carbon::now()->toDateString();
        
        $query = ClaseNoRealizada::query()
            ->select('clases_no_realizadas.*')
            // Excluir registros que tienen un atraso correspondiente
            ->whereNotExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('profesor_atrasos')
                    ->whereColumn('profesor_atrasos.id_asignatura', 'clases_no_realizadas.id_asignatura')
                    ->whereColumn('profesor_atrasos.id_espacio', 'clases_no_realizadas.id_espacio')
                    ->whereColumn('profesor_atrasos.id_modulo', 'clases_no_realizadas.id_modulo')
                    ->whereColumn('profesor_atrasos.fecha', 'clases_no_realizadas.fecha_clase');
            })
            ->when($this->periodo, function($q) {
                $q->where('clases_no_realizadas.periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function($q) {
                $q->whereBetween('clases_no_realizadas.fecha_clase', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->when($this->estado, function($q) {
                $q->where('clases_no_realizadas.estado', $this->estado);
            })
            ->where(function($q) use ($hoy) {
                $q->where('clases_no_realizadas.estado', 'pendiente')
                    ->orWhereDate('clases_no_realizadas.fecha_clase', '<', $hoy)
                    ->orWhereDate('clases_no_realizadas.fecha_clase', $hoy);
            });

        // Optimizar búsqueda usando JOIN solo con asignaturas
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->leftJoin('asignaturas', 'clases_no_realizadas.id_asignatura', '=', 'asignaturas.id_asignatura')
                  ->where(function($q) use ($searchTerm) {
                      $q->where('asignaturas.nombre_asignatura', 'like', $searchTerm)
                        ->orWhere('asignaturas.codigo_asignatura', 'like', $searchTerm)
                        ->orWhere('clases_no_realizadas.run_profesor', 'like', $searchTerm);
                  });
        }

        return $query;
    }

    public function render()
    {        
        // Limpiar cache de estadísticas para este render
        $this->cachedEstadisticas = null;
        
        $query = $this->buildBaseQuery();

        // Ordenamiento con prefijo de tabla para evitar ambigüedad
        $sortField = $this->sortField;
        if (!str_contains($sortField, '.')) {
            $sortField = 'clases_no_realizadas.' . $sortField;
        }
        $query->orderBy($sortField, $this->sortDirection);

        // Eager loading optimizado
        $clasesNoRealizadas = $query->with([
            'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura',
            'profesor:run_profesor,name',
            'espacio:id_espacio,nombre_espacio'
        ])->paginate($this->perPage);
        
        $estadisticas = $this->getEstadisticasOptimizadas();

        // Detectar cambios en los datos para notificaciones
        $currentTotal = $estadisticas['total'];
        if ($this->lastRecordCount > 0 && $currentTotal !== $this->lastRecordCount && $this->autoRefresh) {
            if ($currentTotal > $this->lastRecordCount) {
                $nuevos = $currentTotal - $this->lastRecordCount;
                $this->dispatch('show-info', [
                    'message' => "Se detectaron {$nuevos} nueva(s) clase(s) no realizada(s)"
                ]);
            }
        }
        $this->lastRecordCount = $currentTotal;

        return view('livewire.clases-no-realizadas-table', [
            'clasesNoRealizadas' => $clasesNoRealizadas,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Filtrar clases que ya terminaron para el día de hoy
     * Solo ocultar las clases de hoy que aún no han terminado su horario
     */
    private function filtrarClasesFinalizadasDeHoy($query)
    {
        // Obtener el módulo actual
        $moduloActual = $this->obtenerModuloActual();
        
        if (!$moduloActual) {
            // Si no estamos en horario de clases (fuera de módulos), mostrar todo
            return;
        }

        // Para clases de HOY, solo mostrar las que:
        // 1. Ya pasó su último módulo programado (la clase terminó su horario)
        // 2. O que pasaron más de 20 minutos desde el inicio del primer módulo
        $query->where(function($q) use ($moduloActual) {
            // Opción 1: La clase ya terminó su horario
            // Manejar tanto módulos simples "LU.1" como múltiples "LU.1,LU.2,LU.3"
            $q->where(function($subQ) use ($moduloActual) {
                // Si id_modulo contiene comas, extraer el último módulo
                $subQ->whereRaw("CASE 
                    WHEN id_modulo LIKE '%,%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(id_modulo, ',', -1), '.', -1) AS UNSIGNED)
                    ELSE CAST(SUBSTRING_INDEX(id_modulo, '.', -1) AS UNSIGNED)
                END < ?", [$moduloActual['numero']]);
            })
            // Opción 2: O han pasado más de 20 minutos desde la detección
            ->orWhere(function($subQ) {
                $subQ->where('hora_deteccion', '<=', Carbon::now()->subMinutes(20));
            });
        });
    }

    /**
     * Obtener el módulo actual basado en la hora y día actual
     */
    private function obtenerModuloActual()
    {
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[Carbon::now()->dayOfWeek];
        $horaActual = Carbon::now()->format('H:i:s');

        // Si es fin de semana, no hay módulos
        if ($diaActual === 'domingo' || $diaActual === 'sabado') {
            return null;
        }

        // Horarios de módulos (mismo array que en ModulosActualesTable)
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];

        $horariosDelDia = $horariosModulos[$diaActual] ?? null;
        if (!$horariosDelDia) {
            return null;
        }

        // Buscar en qué módulo estamos
        foreach ($horariosDelDia as $numeroModulo => $modulo) {
            if ($horaActual >= $modulo['inicio'] && $horaActual < $modulo['fin']) {
                return [
                    'numero' => $numeroModulo,
                    'inicio' => $modulo['inicio'],
                    'fin' => $modulo['fin'],
                    'tipo' => 'modulo'
                ];
            }
        }

        // Si no estamos en un módulo, buscar el próximo módulo (estamos en break)
        foreach ($horariosDelDia as $numeroModulo => $modulo) {
            if ($horaActual < $modulo['inicio']) {
                return [
                    'numero' => $numeroModulo,
                    'inicio' => $modulo['inicio'],
                    'fin' => $modulo['fin'],
                    'tipo' => 'break',
                    'mensaje' => 'Próximo Módulo'
                ];
            }
        }

        return null;
    }
}
