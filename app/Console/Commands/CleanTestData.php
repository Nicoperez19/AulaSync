<?php

namespace App\Console\Commands;

use App\Models\ClaseNoRealizada;
use Illuminate\Console\Command;

class CleanTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar datos de prueba de clases no realizadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = ClaseNoRealizada::count();
        
        if ($count > 0) {
            ClaseNoRealizada::query()->delete();
            $this->info("Se eliminaron {$count} registros de clases no realizadas.");
        } else {
            $this->info("No hay registros para eliminar.");
        }
        
        return 0;
    }
}
