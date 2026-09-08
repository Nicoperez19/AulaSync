<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PeriodoAcademico;
use App\Models\ClaseNoRealizada;
use App\Models\Asignatura;
use App\Models\Profesor;
use App\Helpers\SemesterHelper;
use App\Helpers\ModulosHelper;
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

    public function mount()
    {
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_fin = Carbon::today()->format('Y-m-d');
        
        $periodoActual = SemesterHelper::getPeriodoActual();
        if ($periodoActual && $periodoActual->fecha_inicio) {
            $this->fecha_inicio = Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d');
        } else {
            $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        
        // Si viene un reagendar_id desde URL, abrir modal automáticamente
        if ($this->reagendar_id) {
            $this->dispatch('auto-open-reagendar', ['id' => $this->reagendar_id]);
        }
    }

    public function updatingSearch()
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function updatingPeriodo()
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function updatedPeriodo($value)
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();

        if ($value) {
            $partes = explode('-', $value);
            if (count($partes) === 2) {
                $periodoModel = PeriodoAcademico::where('anio', (int)$partes[0])
                    ->where('semestre', (int)$partes[1])
                    ->first();
                if ($periodoModel) {
                    $this->fecha_inicio = Carbon::parse($periodoModel->fecha_inicio)->format('Y-m-d');
                    $fin = Carbon::parse($periodoModel->fecha_fin);
                    $this->fecha_fin = $fin->gt(Carbon::today()) ? Carbon::today()->format('Y-m-d') : $fin->format('Y-m-d');
                }
            }
        } else {
            $this->fecha_inicio = '';
            $this->fecha_fin = Carbon::today()->format('Y-m-d');
        }
    }

    public function updatingFechaInicio()
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function getEstadoNombreProperty()
    {
        return match($this->estado) {
            'no_realizada' => 'No registradas',
            'realizada' => 'Registradas',
            'justificado' => 'Justificadas',
            'pendiente' => 'Pendientes de recuperación',
            default => 'Todos los estados'
        };
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
        
        $this->cachedEstadisticas = null;
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->estado = '';
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_fin = Carbon::today()->format('Y-m-d');
        
        $periodoActual = SemesterHelper::getPeriodoActual();
        if ($periodoActual && $periodoActual->fecha_inicio) {
            $this->fecha_inicio = Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d');
        } else {
            $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }

        $this->cachedEstadisticas = null; // Limpiar cache
        $this->resetPage();
    }

    public function getHayFiltrosActivosProperty(): bool
    {
        $periodoActual = SemesterHelper::getPeriodoActual();
        $fechaInicioDefecto = $periodoActual && $periodoActual->fecha_inicio 
            ? Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d') 
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFinDefecto = Carbon::today()->format('Y-m-d');

        return !empty(trim($this->search ?? ''))
            || !empty($this->estado)
            || ($this->fecha_inicio && $this->fecha_inicio !== $fechaInicioDefecto)
            || ($this->fecha_fin && $this->fecha_fin !== $fechaFinDefecto);
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

    public function prepararAccion($accion, $claseData)
    {
        $id = $claseData['id'] ?? null;
        
        if (!$id) {
            // Reconstruir id_modulo (ej: "Lunes" -> "LU", "Martes" -> "MA")
            $diaStr = strtolower($claseData['dia'] ?? '');
            $prefijoDia = match($diaStr) {
                'lunes' => 'LU',
                'martes' => 'MA',
                'miércoles', 'miercoles' => 'MI',
                'jueves' => 'JU',
                'viernes' => 'VI',
                'sábado', 'sabado' => 'SA',
                'domingo' => 'DO',
                default => 'LU'
            };
            
            $idModulo = $prefijoDia . '.' . $claseData['modulo'];

            // Crear el registro físico para que los modales puedan interactuar con él
            $clase = ClaseNoRealizada::firstOrCreate(
                [
                    'id_asignatura' => $claseData['id_asignatura'],
                    'id_espacio' => $claseData['espacio'],
                    'id_modulo' => $idModulo,
                    'fecha_clase' => Carbon::parse($claseData['fecha'])->format('Y-m-d'),
                    'run_profesor' => $claseData['run_profesor']
                ],
                [
                    'periodo' => $claseData['periodo'] ?? SemesterHelper::getCurrentPeriod(),
                    'estado' => $claseData['estado'] === 'No Registrada' ? 'no_realizada' : 'realizada',
                    'motivo' => $claseData['motivo'] ?? 'Generado para acción manual',
                    'hora_deteccion' => Carbon::now(),
                ]
            );
            $id = $clase->id;
        }

        if ($accion === 'reagendar') {
            $this->showReagendarModal($id);
        } elseif ($accion === 'editar') {
            $this->showEditModal($id);
        } elseif ($accion === 'eliminar') {
            $this->showDeleteModal($id);
        } elseif ($accion === 'recuperada') {
            $this->marcarComoRecuperada($id);
        }
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
        if (empty($estado) || !in_array($estado, ['no_realizada', 'justificado', 'pendiente', 'realizada', 'registrada'])) {
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
            
            // Extraer el número del módulo si viene con el prefijo (ej: "LU.1" -> 1)
            $moduloBase = $nuevoModulo;
            if (is_string($nuevoModulo) && strpos($nuevoModulo, '.') !== false) {
                $moduloBase = (int) explode('.', $nuevoModulo)[1];
            } else {
                $moduloBase = (int) $nuevoModulo;
            }
            
            // Construir el id_modulo completo con todos los módulos seleccionados
            if ($cantidadModulos > 1) {
                // Múltiples módulos: construir array LU.1,LU.2,LU.3
                $modulosNuevos = [];
                for ($i = 0; $i < $cantidadModulos; $i++) {
                    $numeroModulo = $moduloBase + $i;
                    $modulosNuevos[] = "{$prefijoDia}.{$numeroModulo}";
                }
                $nuevoModuloId = implode(',', $modulosNuevos);
            } else {
                // Un solo módulo
                $nuevoModuloId = "{$prefijoDia}.{$moduloBase}";
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

    public function render()
    {        
        $periodosDisponibles = SemesterHelper::getPeriodosDisponibles();

        $periodoModel = null;
        if ($this->periodo) {
            $partes = explode('-', $this->periodo);
            if (count($partes) === 2) {
                $periodoModel = PeriodoAcademico::where('anio', (int)$partes[0])
                    ->where('semestre', (int)$partes[1])
                    ->first();
            }
        } else {
            $periodoModel = SemesterHelper::getPeriodoActual();
        }

        $periodoNoIniciado = $periodoModel && $periodoModel->noHaIniciado();
        
        if ($periodoNoIniciado) {
            return view('livewire.clases-no-realizadas-table', [
                'clasesNoRealizadas' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'estadisticas' => [
                    'total' => 0,
                    'no_realizadas' => 0,
                    'pendientes' => 0,
                    'justificados' => 0,
                    'realizadas' => 0,
                ],
                'periodoNoIniciado' => true,
                'nombrePeriodo' => $periodoModel->nombre_completo ?? 'Período',
                'periodosDisponibles' => $periodosDisponibles,
            ]);
        }
        
        // Usar el servicio para obtener todas las clases del rango (sin filtros de búsqueda/estado)
        // para poder calcular las estadísticas globales del periodo
        $servicio = new \App\Services\TodasClasesService();
        $todasLasClases = $servicio->obtenerTodasLasClases(
            $this->fecha_inicio,
            $this->fecha_fin,
            $this->periodo,
            null,
            null
        );

        // Aplicar filtro de estado en memoria
        if ($this->estado) {
            $estadoStr = match($this->estado) {
                'no_realizada' => 'No Registrada',
                'realizada', 'registrada' => 'Realizada',
                'justificado' => 'Justificada',
                'pendiente' => 'Pendiente de Recuperación',
                default => null
            };
            if ($estadoStr) {
                if ($estadoStr === 'Realizada') {
                    $todasLasClases = $todasLasClases->whereIn('estado', ['Realizada', 'Registrada']);
                } else {
                    $todasLasClases = $todasLasClases->where('estado', $estadoStr);
                }
            }
        }
        
        // Aplicar filtro de búsqueda en memoria
        if ($this->search) {
            $searchTerm = strtolower($this->search);
            $todasLasClases = $todasLasClases->filter(function($item) use ($searchTerm) {
                return str_contains(strtolower($item['profesor'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['asignatura'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['codigo_asignatura'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['run_profesor'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['espacio'] ?? ''), $searchTerm);
            });
        }

        // Calcular estadísticas a partir de la colección ya filtrada
        $estadisticas = [
            'total' => $todasLasClases->count(),
            'no_realizadas' => $todasLasClases->where('estado', 'No Registrada')->count(),
            'pendientes' => $todasLasClases->where('estado', 'Pendiente de Recuperación')->count(),
            'justificados' => $todasLasClases->where('estado', 'Justificada')->count(),
            'realizadas' => $todasLasClases->whereIn('estado', ['Realizada', 'Feriado/Justificado', 'Recuperada'])->count(),
        ];

        // Ordenamiento dinámico sobre la colección filtrada
        $sortField = $this->sortField;
        // Quitar prefijo si existe
        if (str_contains($sortField, '.')) {
            $sortField = explode('.', $sortField)[1];
        }

        // Mapear algunos nombres de campo si difieren entre tabla y array devuelto
        if ($sortField === 'fecha_clase') $sortField = 'fecha';
        
        if ($this->sortDirection === 'asc') {
            $todasLasClases = $todasLasClases->sortBy($sortField)->values();
        } else {
            $todasLasClases = $todasLasClases->sortByDesc($sortField)->values();
        }

        // Paginación manual
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $items = $todasLasClases->forPage($currentPage, $this->perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $todasLasClases->count(),
            $this->perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.clases-no-realizadas-table', [
            'clasesNoRealizadas' => $paginator,
            'estadisticas' => $estadisticas,
            'periodoNoIniciado' => false,
            'nombrePeriodo' => '',
            'periodosDisponibles' => $periodosDisponibles,
        ]);
    }
}
