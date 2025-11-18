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

        // Actualizar estados de espacios cada 15 minutos
        $schedule->command('espacios:actualizar-estado')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->runInBackground();

        // Finalizar reservas al término de cada módulo (cada hora a las :00)
        // Los módulos terminan a las 09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00, 17:00, 18:00, 19:00, 20:00, 21:00, 22:00, 23:00
        $schedule->command('reservas:finalizar-expiradas')
                ->hourly()
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

        // Optimización de base de datos cada 6 horas
        $schedule->command('app:optimize-db')
                ->everySixHours()
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/optimization.log'));

        // Opcional: Verificación diaria a las 23:55 (5 minutos antes de liberar)
        $schedule->command('sistema:verificar-estado')
                ->dailyAt('23:55')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verificacion-sistema.log'));

        // Enviar reporte semanal de clases no realizadas cada lunes a las 8:00 AM
        $schedule->command('reportes:clases-no-realizadas-semanal')
                ->weeklyOn(1, '08:00') // Lunes a las 8:00 AM
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/reporte-semanal.log'));

        // Enviar reporte mensual de clases no realizadas el primer día del mes a las 9:00 AM
        $schedule->command('reportes:clases-no-realizadas-mensual')
                ->monthlyOn(1, '09:00') // Día 1 de cada mes a las 9:00 AM
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/reporte-mensual.log'));

        // Detectar clases no realizadas cada hora (después de que termine cada módulo)
        // Ejecutar a los :05 de cada hora para dar tiempo a que los profesores registren tardíamente
        $schedule->command('clases:detectar-no-realizadas')
                ->hourly()
                ->at('05')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/clases-no-realizadas.log'));
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
