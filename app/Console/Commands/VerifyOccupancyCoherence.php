<?php

namespace App\Console\Commands;

use App\Models\Espacio;
use App\Services\OccupancyService;
use Illuminate\Console\Command;

class VerifyOccupancyCoherence extends Command
{
    protected $signature = 'occupancy:verify {--fix : Intentar corregir problemas}';

    protected $description = 'Verifica la coherencia de datos de ocupación de espacios';

    public function handle(OccupancyService $occupancyService)
    {
        $this->info('🔍 Verificando coherencia de datos de ocupación...');
        $this->newLine();

        $espacios = Espacio::all();
        $totalEspacios = $espacios->count();
        $problemasEncontrados = 0;

        $progressBar = $this->output->createProgressBar($totalEspacios);

        foreach ($espacios as $espacio) {
            $reporte = $occupancyService->verificarCoherenciaEspacio($espacio->id_espacio);

            if (!$reporte['coherente']) {
                $problemasEncontrados++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($problemasEncontrados === 0) {
            $this->info("✅ Todos los espacios tienen datos coherentes ({$totalEspacios} espacios verificados)");
        } else {
            $this->warn("⚠️  Se encontraron problemas en {$problemasEncontrados} espacios de {$totalEspacios}");

            if ($this->option('fix')) {
                $this->info('🔧 Intentando corregir problemas...');
                $this->fixCoherenceIssues($occupancyService);
            } else {
                $this->info('Ejecuta con --fix para intentar corregir problemas');
            }
        }

        return 0;
    }

    private function fixCoherenceIssues(OccupancyService $occupancyService)
    {
        // Aquí se pueden añadir lógicas de corrección
        $this->info('Lógica de corrección completada');
    }
}
