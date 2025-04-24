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
    public $userIdToDelete = null; 

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
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

    public function confirmDelete($userId)
    {
        $this->userIdToDelete = $userId; 
        $this->dispatchBrowserEvent('swal:confirm', [
            'message' => '¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.',
        ]);
    }

    public function deleteUser()
    {
        $user = User::find($this->userIdToDelete);
        if ($user) {
            $user->delete();
            session()->flash('success', 'Usuario eliminado con éxito!');
            $this->users = User::all(); 
        } else {
            session()->flash('error', 'El usuario no fue encontrado.');
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('run', 'like', "%{$this->search}%");
                });
            })
            ->select('id', 'run', 'name', 'email', 'celular', 'direccion', 'fecha_nacimiento', 'anio_ingreso')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.users-table', ['users' => $users]);
    }
}
