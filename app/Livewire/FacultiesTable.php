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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = trim($this->search ?? '');

        $universidadIds = collect();
        $sedeIds = collect();
        $campusIds = collect();

        $facultades = Facultad::with(['universidad', 'sede', 'campus'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                try {
                    $universidadIds = Universidad::where(function($uq) use ($terms) {
                        foreach ($terms as $t) {
                            $uq->orWhere('nombre_universidad', 'like', '%' . $t . '%');
                        }
                    })->pluck('id_universidad');

                    $sedeIds = Sede::where(function($sq) use ($terms) {
                        foreach ($terms as $t) {
                            $sq->orWhere('nombre_sede', 'like', '%' . $t . '%');
                        }
                    })->pluck('id_sede');

                    $campusIds = Campus::where(function($cq) use ($terms) {
                        foreach ($terms as $t) {
                            $cq->orWhere('nombre_campus', 'like', '%' . $t . '%');
                        }
                    })->pluck('id_campus');
                } catch (\Throwable $e) {
                    $universidadIds = collect();
                    $sedeIds = collect();
                    $campusIds = collect();
                }

                // Apply tenant-side filters while honoring central search matches.
                $query->where(function ($innerQuery) use ($terms, $searchTerm, $universidadIds, $sedeIds, $campusIds) {
                    $innerQuery->where('id_facultad', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $innerQuery->orWhere('nombre_facultad', 'like', '%' . $t . '%');
                    }

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
