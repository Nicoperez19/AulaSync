<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClasesNoRealizadasReportService;
use Carbon\Carbon;

class TestPDFReporte extends Command
{
    protected $signature = 'test:pdf-reporte {tipo=semanal}';
    protected $description = 'Genera un PDF de prueba para verificar los estilos';

    public function handle()
    {
        $tipo = $this->argument('tipo');
        $reportService = new ClasesNoRealizadasReportService();
        
        try {
            if ($tipo === 'semanal') {
                $fechaInicio = Carbon::now()->startOfWeek();
                $fechaFin = Carbon::now()->endOfWeek();
                $path = storage_path('app/test_semanal.pdf');
                
                $reportService->generarPDFSemanal($fechaInicio, $fechaFin, $path);
                $this->info("PDF semanal generado exitosamente en: {$path}");
            } else {
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfMonth();
                $path = storage_path('app/test_mensual.pdf');
                
                $reportService->generarPDFMensual($fechaInicio, $fechaFin, $path);
                $this->info("PDF mensual generado exitosamente en: {$path}");
            }
            
            $this->info("Puedes abrir el archivo para verificar los estilos.");
            
        } catch (\Exception $e) {
            $this->error("Error al generar el PDF: " . $e->getMessage());
            $this->error("Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
        }
    }
}
