<?php

namespace App\Console\Commands;

use App\Models\LicenciaProfesor;
use App\Services\LicenciaRecuperacionService;
use Illuminate\Console\Command;

class GenerarClasesRecuperacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licencias:generar-recuperaciones {--all : Regenerar para todas las licencias activas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera clases de recuperación para licencias existentes';

    protected $service;

    /**
     * Create a new command instance.
     */
    public function __construct(LicenciaRecuperacionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando generación de clases de recuperación...');

        $query = LicenciaProfesor::where('genera_recuperacion', true);

        if ($this->option('all')) {
            $query->whereIn('estado', ['activa', 'finalizada']);
        } else {
            $query->where('estado', 'activa');
        }

        $licencias = $query->get();

        if ($licencias->isEmpty()) {
            $this->warn('No se encontraron licencias para procesar.');
            return 0;
        }

        $this->info("Se procesarán {$licencias->count()} licencias.");

        $totalClasesGeneradas = 0;
        $bar = $this->output->createProgressBar($licencias->count());

        foreach ($licencias as $licencia) {
            try {
                $clasesGeneradas = $this->service->generarClasesARecuperar($licencia);
                $totalClasesGeneradas += $clasesGeneradas;
                
                $this->newLine();
                $this->info("Licencia ID {$licencia->id_licencia} - Profesor: {$licencia->profesor->name} - {$clasesGeneradas} clases generadas");
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error en licencia ID {$licencia->id_licencia}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Proceso completado: {$totalClasesGeneradas} clases de recuperación generadas en total.");

        return 0;
    }
}
