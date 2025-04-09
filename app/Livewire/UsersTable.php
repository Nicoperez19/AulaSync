<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public function mount()
    {
    }

    public function render()
    {
        $users = cache()->remember('users_search_' . md5($this->search), 60, function () {
            return User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('run', 'like', '%' . $this->search . '%')
                ->select('id', 'name', 'email', 'run', 'celular', 'direccion', 'fecha_nacimiento', 'anio_ingreso')
                ->paginate(8);
        });

        return view('livewire.users-table', compact('users'));
    }



}
