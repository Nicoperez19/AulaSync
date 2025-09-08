<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;

class LiberarEspaciosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espacios:liberar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera todos los espacios ocupados para el día siguiente a las 12 de la noche';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cambiar todos los espacios ocupados a disponibles
        $espaciosLiberados = Espacio::where('estado', 'Ocupado')
            ->update(['estado' => 'disponible']);

        $this->info("Se liberaron {$espaciosLiberados} espacios para el día siguiente.");

        return 0;
    }
}
