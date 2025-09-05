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

        // Liberar todos los espacios a las 12 de la noche para el día siguiente
        $schedule->command('espacios:liberar')
                ->dailyAt('00:00')
                ->withoutOverlapping()
                ->runInBackground();

        // Verificar inconsistencias del sistema cada 30 minutos
        $schedule->command('sistema:verificar-estado')
                ->everyThirtyMinutes()
                ->withoutOverlapping()
                ->runInBackground();

        // Opcional: Verificación diaria a las 23:55 (5 minutos antes de liberar)
        $schedule->command('sistema:verificar-estado')
                ->dailyAt('23:55')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verificacion-sistema.log'));
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
