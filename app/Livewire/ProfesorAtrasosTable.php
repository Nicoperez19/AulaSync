<?php

namespace App\Livewire;

use App\Helpers\SemesterHelper;
use App\Models\PeriodoAcademico;
use App\Models\ProfesorAtraso;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ProfesorAtrasosTable extends Component
{

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
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Acceso denegado. Función disponible solo para superadministradores.');
        }

        $this->periodo = SemesterHelper::getCurrentPeriod();
        $limites = SemesterHelper::getLimitesPeriodo($this->periodo);
        $this->fecha_inicio = $limites['inicio']->format('Y-m-d');
        $this->fecha_fin = $limites['fin']->gt(Carbon::today())
            ? Carbon::today()->format('Y-m-d')
            : $limites['fin']->format('Y-m-d');
    }

    public function updatedPeriodo($value)
    {
        $this->resetPage();

        if ($value) {
            $limites = SemesterHelper::getLimitesPeriodo($value);
            $this->fecha_inicio = $limites['inicio']->format('Y-m-d');
            $this->fecha_fin = $limites['fin']->gt(Carbon::today())
                ? Carbon::today()->format('Y-m-d')
                : $limites['fin']->format('Y-m-d');
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
        $limites = SemesterHelper::getLimitesPeriodo($this->periodo);
        $this->fecha_inicio = $limites['inicio']->format('Y-m-d');
        $this->fecha_fin = $limites['fin']->gt(Carbon::today())
            ? Carbon::today()->format('Y-m-d')
            : $limites['fin']->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $query = ProfesorAtraso::query()
            ->with(['asignatura', 'profesor', 'espacio'])
            ->when($this->periodo, function ($q) {
                $q->where('periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function ($q) {
                $q->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->when($this->search, function ($q) {
                $rawSearch = trim($this->search);
                $cleanRun = preg_replace('/[^0-9kK]/', '', $rawSearch);
                $words = array_values(array_filter(explode(' ', $rawSearch), fn ($w) => mb_strlen(trim($w)) > 0));

                $q->where(function ($subQ) use ($rawSearch, $words, $cleanRun) {
                    if (! empty($words)) {
                        $subQ->whereHas('profesor', function ($pq) use ($words) {
                            foreach ($words as $word) {
                                $pq->where('name', 'like', '%'.$word.'%');
                            }
                        });
                    }
                    $subQ->orWhereHas('asignatura', function ($aq) use ($rawSearch) {
                        $aq->where('nombre_asignatura', 'like', '%'.$rawSearch.'%');
                    })
                        ->orWhere('id_espacio', 'like', '%'.$rawSearch.'%');

                    if (! empty($cleanRun)) {
                        $subQ->orWhereHas('profesor', function ($pq) use ($cleanRun) {
                            $pq->where('run_profesor', 'like', '%'.$cleanRun.'%');
                        });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $atrasos = $query->paginate($this->perPage);

        // Estadísticas rápidas según período seleccionado
        $statsQuery = ProfesorAtraso::query()
            ->when($this->periodo, function ($q) {
                $q->where('periodo', $this->periodo);
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function ($q) {
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
