<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClaseNoRealizada;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;

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

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'periodo' => ['except' => ''],
    ];

    protected $listeners = [
        'updateClase',
        'confirmDelete'
    ];

    public function mount()
    {
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
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
        if (empty($estado) || !in_array($estado, ['pendiente', 'justificado', 'confirmado'])) {
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
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al actualizar la clase: ' . $e->getMessage()]);
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
            
            // Cambiar al siguiente estado en secuencia: pendiente -> justificado -> confirmado -> pendiente
            $estados = ['pendiente', 'justificado', 'confirmado'];
            $estadoActualIndex = array_search($clase->estado, $estados);
            $nuevoEstadoIndex = ($estadoActualIndex + 1) % count($estados);
            $nuevoEstado = $estados[$nuevoEstadoIndex];
            
            $clase->update(['estado' => $nuevoEstado]);
            
            session()->flash('message', "Estado cambiado a: " . ucfirst($nuevoEstado));
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

        return [
            'total' => $query->count(),
            'pendientes' => $query->clone()->where('estado', 'pendiente')->count(),
            'justificados' => $query->clone()->where('estado', 'justificado')->count(),
            'confirmados' => $query->clone()->where('estado', 'confirmado')->count(),
        ];
    }

    public function render()
    {
        $query = ClaseNoRealizada::with(['asignatura', 'profesor', 'espacio']);

        if ($this->search) {
            $query->whereHas('profesor', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('asignatura', function($q) {
                $q->where('nombre_asignatura', 'like', '%' . $this->search . '%');
            })
            ->orWhere('id_espacio', 'like', '%' . $this->search . '%');
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

        $query->orderBy($this->sortField, $this->sortDirection);

        $clasesNoRealizadas = $query->paginate($this->perPage);
        $estadisticas = $this->getEstadisticas();

        return view('livewire.clases-no-realizadas-table', [
            'clasesNoRealizadas' => $clasesNoRealizadas,
            'estadisticas' => $estadisticas,
        ]);
    }
}
