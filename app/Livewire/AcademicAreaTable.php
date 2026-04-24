<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AreaAcademica;

class AcademicAreaTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $paginationOptions = [5, 10, 50];

    protected $updatesQueryString = ['search', 'perPage'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = trim($this->search);
        $universidadIds = collect();

        if ($searchTerm !== '') {
            $universidadIds = \App\Models\Universidad::where('nombre_universidad', 'like', '%' . $searchTerm . '%')
                ->pluck('id_universidad');
        }

        $areasAcademicas = AreaAcademica::query()
            ->where(function($q) use ($searchTerm, $universidadIds) {
                $q->where('nombre_area_academica', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('facultad', function ($query) use ($searchTerm) {
                      $query->where('nombre_facultad', 'like', '%' . $searchTerm . '%');
                  });
                
                if ($universidadIds->isNotEmpty()) {
                    $q->orWhereHas('facultad', function ($query) use ($universidadIds) {
                        $query->whereIn('id_universidad', $universidadIds);
                    });
                }
            })
            ->orderBy('nombre_area_academica', 'asc')
            ->paginate($this->perPage);
    
        return view('livewire.academic-area-table', compact('areasAcademicas'));
    }
}
