<?php

namespace App\Livewire;

use Spatie\Permission\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionsTable extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $permissions = Permission::where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.permissions-table', compact('permissions'));
    }
}
