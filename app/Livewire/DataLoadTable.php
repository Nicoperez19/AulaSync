<?php

namespace App\Livewire;

use App\Models\DataLoad;
use Livewire\Component;
use Livewire\WithPagination;

class DataLoadTable extends Component
{
    use WithPagination;

    protected $listeners = ['fileUploaded' => '$refresh'];
    
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Para la selección masiva
    public $selected = [];
    public $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->selected = [];
        $this->selectAll = false;
    }

    // Al cambiar la casilla de "seleccionar todo"
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Obtener todos los IDs de los registros de la página actual
            $this->selected = $this->getCurrentPageIds();
        } else {
            $this->selected = [];
        }
    }

    // Al cambiar cualquier casilla individual
    public function updatedSelected()
    {
        $this->selected = array_map('strval', $this->selected);
        $currentPageIds = $this->getCurrentPageIds();
        if (count($currentPageIds) > 0 && count(array_intersect($currentPageIds, $this->selected)) === count($currentPageIds)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    // Helper para obtener los IDs de la página actual
    private function getCurrentPageIds()
    {
        return DataLoad::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('nombre_archivo', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('run', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10)
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    // Acción para eliminar masivamente
    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        $dataLoads = DataLoad::whereIn('id', $this->selected)->get();

        foreach ($dataLoads as $dataLoad) {
            if ($dataLoad->ruta_archivo && \Illuminate\Support\Facades\Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($dataLoad->ruta_archivo);
            }
            // Elimina el registro, y gracias al cascade delete, se eliminan las planificaciones
            $dataLoad->delete();
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('show-success', ['message' => 'Los registros seleccionados y su información asociada han sido eliminados correctamente.']);
    }

    // Acción para eliminar un solo registro vía Livewire
    public function deleteSingle($id)
    {
        $dataLoad = DataLoad::find($id);
        if ($dataLoad) {
            if ($dataLoad->ruta_archivo && \Illuminate\Support\Facades\Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($dataLoad->ruta_archivo);
            }
            $dataLoad->delete();
            
            // Quitar de los seleccionados si estaba
            $this->selected = array_values(array_diff($this->selected, [$id]));
            
            $this->dispatch('show-success', ['message' => 'El registro y su información asociada han sido eliminados correctamente.']);
        }
    }

    // Acción para eliminar todos los registros vía Livewire
    public function deleteAll()
    {
        $dataLoads = DataLoad::all();

        foreach ($dataLoads as $dataLoad) {
            if ($dataLoad->ruta_archivo && \Illuminate\Support\Facades\Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($dataLoad->ruta_archivo);
            }
            $dataLoad->delete();
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('show-success', ['message' => 'Todos los registros de carga y su información asociada fueron eliminados correctamente.']);
    }

    public function render()
    {
        $dataLoads = DataLoad::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('nombre_archivo', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('run', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.data-load-table', [
            'dataLoads' => $dataLoads
        ]);
    }
}
