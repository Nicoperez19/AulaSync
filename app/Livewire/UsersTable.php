<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $searchTerm = trim($this->search);

        $users = User::query()
            ->with('roles')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ü', 'Ü', 'ñ', 'Ñ'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'u', 'U', 'n', 'N'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                // Buscar usuarios que tengan el rol buscado (manejando multi-tenancy de forma segura)
                $roleUserRuns = [];
                try {
                    $roleUserRuns = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.name', 'like', '%' . $searchTerm . '%')
                        ->pluck('model_id')
                        ->toArray();
                } catch (\Throwable $e) {
                    // Si no está en contexto tenant o falla, ignorar
                }

                $query->where(function ($q) use ($terms, $cleanRun, $roleUserRuns) {
                    if (!empty($cleanRun)) {
                        $q->where('run', 'like', '%' . $cleanRun . '%')
                          ->orWhereRaw("REPLACE(REPLACE(run, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
                    }

                    foreach ($terms as $term) {
                        $q->orWhere('name', 'like', '%' . $term . '%')
                          ->orWhere('email', 'like', '%' . $term . '%');
                    }

                    if (!empty($roleUserRuns)) {
                        $q->orWhereIn('run', $roleUserRuns);
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.users-table', ['users' => $users]);
    }
}
