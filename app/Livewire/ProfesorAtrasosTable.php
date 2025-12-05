<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProfesorAtraso;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;

class ProfesorAtrasosTable extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $periodo = '';
    public $perPage = 15;
    public $sortField = 'fecha';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
    ];

    public function mount()
    {
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc';
        }
        $this->sortField = $field;
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $query = ProfesorAtraso::query()
            ->with(['asignatura', 'profesor', 'espacio'])
            ->when($this->periodo, function($q) {
                $q->where('periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function($q) {
                $q->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->when($this->search, function($q) {
                $searchTerm = '%' . $this->search . '%';
                $q->where(function($subQ) use ($searchTerm) {
                    $subQ->whereHas('profesor', function($pq) use ($searchTerm) {
                        $pq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('asignatura', function($aq) use ($searchTerm) {
                        $aq->where('nombre_asignatura', 'like', $searchTerm);
                    })
                    ->orWhere('id_espacio', 'like', $searchTerm);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $atrasos = $query->paginate($this->perPage);

        // Estadísticas rápidas
        $estadisticas = [
            'total' => ProfesorAtraso::where('periodo', $this->periodo)->count(),
            'promedio' => round(ProfesorAtraso::where('periodo', $this->periodo)->avg('minutos_atraso') ?? 0),
        ];

        return view('livewire.profesor-atrasos-table', [
            'atrasos' => $atrasos,
            'estadisticas' => $estadisticas,
        ]);
    }
}
