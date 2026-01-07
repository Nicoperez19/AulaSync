<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Facultad;
use App\Models\Universidad;
use App\Models\Sede;
use App\Models\Campus;

class FacultiesTable extends Component
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
        $searchTerm = trim($this->search ?? '');

        $universidadIds = collect();
        $sedeIds = collect();
        $campusIds = collect();

        if ($searchTerm !== '') {
            $likeTerm = '%' . $searchTerm . '%';

            $universidadIds = Universidad::where('nombre_universidad', 'like', $likeTerm)
                ->pluck('id_universidad');

            $sedeIds = Sede::where('nombre_sede', 'like', $likeTerm)
                ->pluck('id_sede');

            $campusIds = Campus::where('nombre_campus', 'like', $likeTerm)
                ->pluck('id_campus');
        }

        $facultades = Facultad::with(['universidad', 'sede', 'campus'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $universidadIds, $sedeIds, $campusIds) {
                $likeTerm = '%' . $searchTerm . '%';

                // Apply tenant-side filters while honoring central search matches.
                $query->where(function ($innerQuery) use ($likeTerm, $universidadIds, $sedeIds, $campusIds) {
                    $innerQuery->where('nombre_facultad', 'like', $likeTerm)
                        ->orWhere('id_facultad', 'like', $likeTerm);

                    if ($universidadIds->isNotEmpty()) {
                        $innerQuery->orWhereIn('id_universidad', $universidadIds);
                    }

                    if ($sedeIds->isNotEmpty()) {
                        $innerQuery->orWhereIn('id_sede', $sedeIds);
                    }

                    if ($campusIds->isNotEmpty()) {
                        $innerQuery->orWhereIn('id_campus', $campusIds);
                    }
                });
            })
            ->orderBy('nombre_facultad', 'asc')
            ->paginate((int) $this->perPage);

        return view('livewire.faculties-table', compact('facultades'));
    }
}
