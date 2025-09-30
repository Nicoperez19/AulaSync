<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClaseNoRealizada;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'periodo' => ['except' => ''],
    ];

    protected $listeners = [
        'updateClase',
        'confirmDelete',
        'reagendarClase'
    ];

    public function mount()
    {
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Inicializar el conteo de registros
        $estadisticas = $this->getEstadisticas();
        $this->lastRecordCount = $estadisticas['total'];
        
        // Forzar refresh inicial
        $this->render();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingPeriodo()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function refresh()
    {
        // Método para refrescar manualmente los datos
        $this->resetPage();
        $this->render();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->estado = '';
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
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
        
        // Debug: Verificar conexión a base de datos y tabla espacios
        try {
            // Intentar diferentes consultas para obtener espacios
            $espacios = collect();
            
            // Primer intento: espacios activos
            $espacios = \App\Models\Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'estado')
                ->where('estado', 'activo')
                ->orderBy('nombre_espacio')
                ->get();

            // Si no hay espacios activos, probar con todos los espacios
            if ($espacios->isEmpty()) {
                $espacios = \App\Models\Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'estado')
                    ->orderBy('id_espacio')
                    ->get();
            }

            // Log para debugging
            Log::info('Espacios encontrados: ' . $espacios->count());
            
        } catch (\Exception $e) {
            Log::error('Error al cargar espacios: ' . $e->getMessage());
            $espacios = collect();
        }

        // Si aún no hay espacios, crear algunos espacios de ejemplo
        if ($espacios->isEmpty()) {
            Log::warning('No se encontraron espacios en la base de datos, usando fallback');
            $espacios = collect([
                (object)['id_espacio' => 'A101', 'nombre_espacio' => 'Aula A101', 'tipo_espacio' => 'Aula'],
                (object)['id_espacio' => 'A102', 'nombre_espacio' => 'Aula A102', 'tipo_espacio' => 'Aula'],
                (object)['id_espacio' => 'B201', 'nombre_espacio' => 'Aula B201', 'tipo_espacio' => 'Aula'],
                (object)['id_espacio' => 'LAB1', 'nombre_espacio' => 'Laboratorio 1', 'tipo_espacio' => 'Laboratorio'],
                (object)['id_espacio' => 'C301', 'nombre_espacio' => 'Aula C301', 'tipo_espacio' => 'Aula'],
            ]);
        }

        // Preparar los datos de espacios para el frontend
        $espaciosData = $espacios->map(function ($espacio) {
            return [
                'id_espacio' => $espacio->id_espacio,
                'nombre_espacio' => $espacio->nombre_espacio ?? $espacio->id_espacio,
                'tipo_espacio' => $espacio->tipo_espacio ?? 'Aula'
            ];
        })->toArray();

        // Log final para debugging
        Log::info('Espacios enviados al frontend: ' . json_encode($espaciosData));

        $this->dispatch('show-reagendar-modal', [
            'id' => $id,
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha_original' => $clase->fecha_clase->format('d/m/Y'),
            'espacio_original' => $clase->id_espacio,
            'modulo_original' => $clase->id_modulo,
            'espacios' => $espaciosData,
        ]);
    }

    public function reagendarClase($id, $nuevaFecha, $nuevoEspacio, $nuevoModulo, $observaciones = '')
    {
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Validar que la nueva fecha no sea anterior a hoy
            if (Carbon::parse($nuevaFecha)->lt(Carbon::today())) {
                $this->dispatch('show-error', ['message' => 'La nueva fecha no puede ser anterior a hoy']);
                return;
            }
            
            // Crear observación de reagendamiento
            $observacionReagendamiento = "Reagendada desde {$clase->fecha_clase->format('d/m/Y')} en {$clase->id_espacio} módulo {$clase->id_modulo} a {$nuevaFecha} en {$nuevoEspacio} módulo {$nuevoModulo}";
            
            if ($observaciones) {
                $observacionReagendamiento .= ". Motivo: {$observaciones}";
            }
            
            $clase->update([
                'fecha_clase' => $nuevaFecha,
                'id_espacio' => $nuevoEspacio,
                'id_modulo' => $nuevoModulo,
                'estado' => 'justificado',
                'observaciones' => $observacionReagendamiento,
            ]);
            
            $this->dispatch('show-success', ['message' => 'Clase reagendada exitosamente']);
            $this->refresh(); // Refrescar datos después de reagendar
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al reagendar la clase: ' . $e->getMessage()]);
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
        $query = ClaseNoRealizada::with(['asignatura', 'profesor', 'espacio']);

        if ($this->periodo) {
            $query->where('periodo', $this->periodo);
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('fecha_clase', [$this->fecha_inicio, $this->fecha_fin]);
        }

        // Aplicar el mismo filtro corregido para las estadísticas
        $hoy = Carbon::now()->toDateString();
        $query->where(function($q) use ($hoy) {
            // Mostrar TODOS los registros de días anteriores
            $q->whereDate('fecha_clase', '<', $hoy)
            // PARA registros de hoy, aplicar filtro especial
            ->orWhere(function($subQ) use ($hoy) {
                $subQ->whereDate('fecha_clase', $hoy);
                $this->filtrarClasesFinalizadasDeHoy($subQ);
            });
        });

        return [
            'total' => $query->count(),
            'no_realizadas' => $query->clone()->where('estado', 'no_realizada')->count(),
            'justificados' => $query->clone()->where('estado', 'justificado')->count(),
        ];
    }

    public function render()
    {        
        $query = ClaseNoRealizada::with(['asignatura', 'profesor', 'espacio']);

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('profesor', function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('asignatura', function($subQ) {
                    $subQ->where('nombre_asignatura', 'like', '%' . $this->search . '%');
                })
                ->orWhere('id_espacio', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->periodo) {
            $query->where('periodo', $this->periodo);
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('fecha_clase', [$this->fecha_inicio, $this->fecha_fin]);
        }

        // Solo aplicar filtro de clases finalizadas para registros de HOY
        // Los registros de días anteriores se mantienen para historial
        $hoy = Carbon::now()->toDateString();
        $query->where(function($q) use ($hoy) {
            // Mostrar TODOS los registros de días anteriores
            $q->whereDate('fecha_clase', '<', $hoy)
            // PARA registros de hoy, aplicar filtro especial
            ->orWhere(function($subQ) use ($hoy) {
                $subQ->whereDate('fecha_clase', $hoy);
                $this->filtrarClasesFinalizadasDeHoy($subQ);
            });
        });

        $query->orderBy($this->sortField, $this->sortDirection);

        $clasesNoRealizadas = $query->paginate($this->perPage);
        $estadisticas = $this->getEstadisticas();

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
     */
    private function filtrarClasesFinalizadasDeHoy($query)
    {
        // Obtener el módulo actual
        $moduloActual = $this->obtenerModuloActual();
        
        if (!$moduloActual) {
            return; // Si no hay módulo actual, no filtrar nada
        }

        // Filtrar clases que:
        // 1. No han terminado por horario (módulo actual < último módulo de la clase)
        // 2. Y no han terminado antes (sin reserva finalizada del profesor)
        $query->where(function($q) use ($moduloActual) {
            // Incluir clases cuyo último módulo aún no ha pasado
            $q->whereRaw("CAST(SUBSTRING_INDEX(id_modulo, '.', -1) AS UNSIGNED) >= ?", [$moduloActual['numero']])
            // Y que no tengan una reserva finalizada del profesor hoy
            ->whereNotExists(function($subQuery) {
                $subQuery->select('id_reserva')
                    ->from('reservas')
                    ->whereColumn('reservas.id_espacio', 'clases_no_realizadas.id_espacio')
                    ->where('reservas.fecha_reserva', Carbon::now()->toDateString())
                    ->whereNotNull('reservas.run_profesor')
                    ->where('reservas.estado', 'finalizada')
                    ->whereNotNull('reservas.hora_salida');
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
