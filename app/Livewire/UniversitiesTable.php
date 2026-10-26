<?php

namespace App\Livewire;

use App\Models\Universidad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

class UniversitiesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_universidad';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_universidad'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Columnas realmente existentes en la tabla (evita errores SQL si el esquema
     * no tiene telefono_universidad / direccion_universidad).
     */
    protected function columnasDisponibles(): array
    {
        try {
            return Schema::connection('mysql')->getColumnListing('universidades');
        } catch (\Throwable $e) {
            return ['id_universidad', 'nombre_universidad'];
        }
    }

    public function sortBy($field)
    {
        if (!in_array($field, $this->columnasDisponibles(), true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = trim($this->search);
        $columnas = $this->columnasDisponibles();

        $sortField = in_array($this->sortField, $columnas, true) ? $this->sortField : 'id_universidad';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $universidades = Universidad::query()
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $columnas) {
                $columnasBusqueda = array_values(array_intersect(
                    ['id_universidad', 'nombre_universidad', 'direccion_universidad', 'telefono_universidad'],
                    $columnas
                ));

                // La colación utf8mb4_unicode_ci ya ignora mayúsculas y tildes
                $query->where(function ($q) use ($columnasBusqueda, $searchTerm) {
                    foreach ($columnasBusqueda as $columna) {
                        $q->orWhere($columna, 'like', '%' . $searchTerm . '%');
                    }
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10);

        return view('livewire.universities-table', compact('universidades'));
    }
}
