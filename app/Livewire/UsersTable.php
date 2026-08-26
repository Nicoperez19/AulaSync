<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
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

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = trim($this->search);

        $query = User::query()
            ->with(['roles', 'sede'])
            ->when($this->roleFilter !== '', function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->where('name', $this->roleFilter);
                });
            })
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Limpiar RUN si contiene números/K (ej: 12.345.678-9 -> 123456789)
                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);
                
                // Dividir en palabras para búsquedas compuestas (ej: "Juan Perez", "Admin Juan", etc.)
                $words = array_values(array_filter(explode(' ', $searchTerm)));

                $query->where(function ($q) use ($words, $searchTerm, $cleanRun) {
                    // 1. Coincidencia por RUN directo o limpio
                    if (!empty($cleanRun) && strlen($cleanRun) >= 2) {
                        $q->where('run', 'like', '%' . $cleanRun . '%')
                          ->orWhereRaw("REPLACE(REPLACE(run, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
                    }

                    // 2. Coincidencia por frase completa en nombre o email
                    $q->orWhere('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%');

                    // 3. Coincidencia por cada palabra ingresada
                    if (count($words) > 1) {
                        $q->orWhere(function ($wordQuery) use ($words) {
                            foreach ($words as $word) {
                                $wordQuery->where(function ($subQ) use ($word) {
                                    $cleanWordRun = preg_replace('/[^0-9Kk]/', '', $word);
                                    if (!empty($cleanWordRun) && strlen($cleanWordRun) >= 2) {
                                        $subQ->where('run', 'like', '%' . $cleanWordRun . '%')
                                             ->orWhereRaw("REPLACE(REPLACE(run, '.', ''), '-', '') LIKE ?", ['%' . $cleanWordRun . '%']);
                                    }

                                    $subQ->orWhere('name', 'like', '%' . $word . '%')
                                         ->orWhere('email', 'like', '%' . $word . '%')
                                         ->orWhereHas('roles', function ($rq) use ($word) {
                                             $rq->where('name', 'like', '%' . $word . '%');
                                         })
                                         ->orWhereHas('sede', function ($sq) use ($word) {
                                             $sq->where('nombre_sede', 'like', '%' . $word . '%');
                                         });
                                });
                            }
                        });
                    } else {
                        // Búsqueda por rol si es una sola palabra
                        $q->orWhereHas('roles', function ($rq) use ($searchTerm) {
                            $rq->where('name', 'like', '%' . $searchTerm . '%');
                        });
                        // Búsqueda por sede si es una sola palabra
                        $q->orWhereHas('sede', function ($sq) use ($searchTerm) {
                            $sq->where('nombre_sede', 'like', '%' . $searchTerm . '%');
                        });
                    }
                });
            });

        $roles = Role::orderBy('name')->get();
        $users = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.users-table', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}

