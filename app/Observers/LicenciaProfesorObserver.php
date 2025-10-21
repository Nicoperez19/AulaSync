<?php

namespace App\Observers;

use App\Models\LicenciaProfesor;
use App\Services\LicenciaRecuperacionService;
use Illuminate\Support\Facades\Log;

class LicenciaProfesorObserver
{
    protected $service;

    public function __construct(LicenciaRecuperacionService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the LicenciaProfesor "created" event.
     */
    public function created(LicenciaProfesor $licencia): void
    {
        // Generar clases a recuperar automáticamente después de crear la licencia
        if ($licencia->genera_recuperacion && $licencia->estado === 'activa') {
            try {
                $clasesGeneradas = $this->service->generarClasesARecuperar($licencia);
                Log::info("Licencia ID {$licencia->id_licencia} creada. {$clasesGeneradas} clases generadas automáticamente.");
            } catch (\Exception $e) {
                Log::error("Error al generar clases para licencia ID {$licencia->id_licencia}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the LicenciaProfesor "updated" event.
     */
    public function updated(LicenciaProfesor $licencia): void
    {
        // Si cambió el estado a 'cancelada' o 'genera_recuperacion' se desactivó
        if ($licencia->estado === 'cancelada' || !$licencia->genera_recuperacion) {
            try {
                // Eliminar solo las clases pendientes (no las que ya están gestionadas)
                $this->service->eliminarClasesARecuperar($licencia);
                Log::info("Clases pendientes eliminadas para licencia ID {$licencia->id_licencia}");
            } catch (\Exception $e) {
                Log::error("Error al eliminar clases para licencia ID {$licencia->id_licencia}: " . $e->getMessage());
            }
        }
        // Si cambió las fechas y sigue activa con recuperación habilitada
        elseif ($licencia->isDirty(['fecha_inicio', 'fecha_fin']) && $licencia->genera_recuperacion && $licencia->estado === 'activa') {
            try {
                $clasesGeneradas = $this->service->regenerarClasesARecuperar($licencia);
                Log::info("Licencia ID {$licencia->id_licencia} actualizada. {$clasesGeneradas} clases regeneradas.");
            } catch (\Exception $e) {
                Log::error("Error al regenerar clases para licencia ID {$licencia->id_licencia}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the LicenciaProfesor "deleted" event.
     */
    public function deleted(LicenciaProfesor $licencia): void
    {
        // Las clases se eliminan automáticamente por cascade en la base de datos
        Log::info("Licencia ID {$licencia->id_licencia} eliminada. Clases eliminadas por cascade.");
    }
}
