<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public function render()
    {
        $users = User::select('id', 'run', 'name', 'email', 'celular', 'direccion', 'fecha_nacimiento', 'anio_ingreso')
                     ->orderBy('name')
                     ->paginate(10);

        return view('livewire.users-table', [
            'users' => $users
        ]);
    }
}
