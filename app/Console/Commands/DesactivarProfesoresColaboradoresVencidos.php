<?php

namespace App\Console\Commands;

use App\Models\ProfesorColaborador;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DesactivarProfesoresColaboradoresVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profesores-colaboradores:desactivar-vencidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Desactiva automáticamente los profesores colaboradores cuya fecha de término ha pasado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de desactivación de profesores colaboradores vencidos...');

        try {
            $hoy = Carbon::today();

            // Buscar profesores colaboradores activos cuya fecha de término haya pasado
            $profesoresVencidos = ProfesorColaborador::where('estado', 'activo')
                ->where('fecha_termino', '<', $hoy)
                ->get();

            if ($profesoresVencidos->isEmpty()) {
                $this->info('No se encontraron profesores colaboradores vencidos.');
                return Command::SUCCESS;
            }

            $total = $profesoresVencidos->count();
            $this->info("Se encontraron {$total} profesores colaboradores vencidos.");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $desactivados = 0;

            foreach ($profesoresVencidos as $profesor) {
                try {
                    $profesor->update(['estado' => 'inactivo']);
                    $desactivados++;

                    Log::info('Profesor colaborador desactivado automáticamente', [
                        'id' => $profesor->id,
                        'run_profesor' => $profesor->run_profesor_colaborador,
                        'asignatura' => $profesor->nombre_asignatura,
                        'fecha_termino' => $profesor->fecha_termino->format('Y-m-d'),
                    ]);

                    $bar->advance();
                } catch (\Exception $e) {
                    Log::error('Error al desactivar profesor colaborador', [
                        'id' => $profesor->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("\nError al desactivar profesor ID {$profesor->id}: {$e->getMessage()}");
                }
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("✓ Proceso completado exitosamente.");
            $this->info("Total procesados: {$total}");
            $this->info("Desactivados: {$desactivados}");

            if ($desactivados < $total) {
                $this->warn("Errores: " . ($total - $desactivados));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Error en comando desactivar-profesores-colaboradores-vencidos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error general: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
