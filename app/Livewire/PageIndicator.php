<?php

namespace App\Livewire;

use Livewire\Component;

class PageIndicator extends Component
{
    public $paginaActual = 1;
    public $totalPaginas = 1;

    protected $listeners = ['actualizarPagina'];

    public function mount()
    {
        // Inicializar con valores por defecto
        $this->paginaActual = 1;
        $this->totalPaginas = 1;
    }

    public function actualizarPagina($pagina, $total)
    {
        $this->paginaActual = $pagina;
        $this->totalPaginas = $total;
    }

    public function render()
    {
        return view('livewire.page-indicator');
    }
}
