<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionsTable extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
        \Log::info('Search updated to: "' . $this->search . '"');
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
        \Log::info('Search cleared');
    }

    public function render()
    {
        \Log::info('Render called with search: "' . $this->search . '"');
        
        if (empty($this->search)) {
            $permissions = Permission::paginate(10);
            \Log::info('No search - showing all: ' . $permissions->total());
        } else {
            $permissions = Permission::where('name', 'like', '%' . $this->search . '%')
                                   ->orWhere('id', 'like', '%' . $this->search . '%')
                                   ->paginate(10);
            \Log::info('With search "' . $this->search . '" - found: ' . $permissions->total());
        }
        
        $allPermissions = Permission::all();

        return view('livewire.permissions-table', compact('permissions', 'allPermissions'));
    }
}
