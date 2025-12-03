<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ClasesComparativaExport implements WithMultipleSheets
{
    protected $datos;
    protected $periodo;

    public function __construct($datos, $periodo)
    {
        $this->datos = $datos;
        $this->periodo = $periodo;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Sheet 1: Resumen General
        $sheets[] = new ClasesResumenExport($this->datos, $this->periodo);
        
        // Sheet 2: Clases Realizadas
        $sheets[] = new ClasesRealizadasExport($this->datos, $this->periodo);
        
        // Sheet 3: Clases No Realizadas
        $sheets[] = new ClasesNoRealizadasExport($this->datos, $this->periodo);
        
        return $sheets;
    }
}
