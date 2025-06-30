<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Actualizar estados de espacios cada 5 minutos
        $schedule->command('espacios:actualizar-estado')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->runInBackground();
        
        // Limpiar sesiones expiradas cada hora
        $schedule->command('sessions:clean')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
