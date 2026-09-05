<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PeriodoAcademico;
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
        'periodo' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acceso denegado. Función disponible solo para superadministradores.');
        }

        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_fin = Carbon::today()->format('Y-m-d');

        $periodoActual = SemesterHelper::getPeriodoActual();
        if ($periodoActual && $periodoActual->fecha_inicio) {
            $this->fecha_inicio = Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d');
        } else {
            $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
    }

    public function updatedPeriodo($value)
    {
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
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $this->fecha_fin = Carbon::today()->format('Y-m-d');
        
        $periodoActual = SemesterHelper::getPeriodoActual();
        if ($periodoActual && $periodoActual->fecha_inicio) {
            $this->fecha_inicio = Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d');
        } else {
            $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
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

        // Estadísticas rápidas según período seleccionado
        $statsQuery = ProfesorAtraso::query()
            ->when($this->periodo, function($q) {
                $q->where('periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function($q) {
                $q->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
            });

        $estadisticas = [
            'total' => (clone $statsQuery)->count(),
            'promedio' => round((clone $statsQuery)->avg('minutos_atraso') ?? 0),
        ];

        $periodosDisponibles = SemesterHelper::getPeriodosDisponibles();

        return view('livewire.profesor-atrasos-table', [
            'atrasos' => $atrasos,
            'estadisticas' => $estadisticas,
            'periodosDisponibles' => $periodosDisponibles,
        ]);
    }
}
