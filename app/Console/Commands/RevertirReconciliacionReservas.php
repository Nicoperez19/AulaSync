<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Asistencia;
use App\Models\ClaseNoRealizada;
use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class RevertirReconciliacionReservas extends Command
{
    protected $signature = 'reservas:revertir-reconciliacion 
                            {--archivo= : Ruta específica al archivo de respaldo JSON generado por la reconciliación}';

    protected $description = 'Revierte los cambios aplicados por el comando reservas:reconciliar-espontaneas utilizando el archivo de respaldo JSON';

    public function handle()
    {
        $archivo = $this->option('archivo');

        if (!$archivo) {
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                $this->error("No se encontró el directorio de respaldos en: {$backupDir}");
                return 1;
            }

            $archivos = File::glob($backupDir . DIRECTORY_SEPARATOR . 'reconciliacion_backup_*.json');
            if (empty($archivos)) {
                $this->error("No se encontraron archivos de respaldo en {$backupDir}.");
                return 1;
            }

            // Ordenar para tomar el más reciente
            rsort($archivos);
            $archivo = $archivos[0];
        }

        if (!File::exists($archivo)) {
            $this->error("El archivo de respaldo especificado no existe: {$archivo}");
            return 1;
        }

        $this->warn("==================================================================");
        $this->warn(" REVERSIÓN DE RECONCILIACIÓN DE RESERVAS");
        $this->warn(" Archivo de respaldo: {$archivo}");
        $this->warn("==================================================================");

        $contenido = json_decode(File::get($archivo), true);
        if (!$contenido || !isset($contenido['tenants'])) {
            $this->error("El archivo de respaldo no tiene una estructura válida o está corrupto.");
            return 1;
        }

        $fechaBackup = $contenido['created_at'] ?? 'desconocida';
        $desdeFiltro = $contenido['desde'] ?? 'desconocido';
        $this->line("Respaldo creado el: {$fechaBackup} (Rango desde: {$desdeFiltro})");

        if (!$this->confirm("¿Estás seguro de que deseas revertir todos los cambios de este respaldo?", true)) {
            $this->info("Operación cancelada por el usuario.");
            return 0;
        }

        foreach ($contenido['tenants'] as $tenantId => $data) {
            $tenantName = $data['tenant_name'] ?? "Tenant #{$tenantId}";
            $this->info("\nProcesando reversión para: {$tenantName}...");

            $tenant = Tenant::find($tenantId);
            if (!$tenant && !empty($data['tenant_database'])) {
                $tenant = Tenant::where('database', $data['tenant_database'])->first();
            }

            if (!$tenant) {
                $this->error("  ⚠ No se pudo encontrar el tenant con ID {$tenantId}. Se omite este tenant.");
                continue;
            }

            try {
                $tenant->makeCurrent();

                $reservasRevertidas = 0;
                $asistenciasRevertidas = 0;
                $clasesRestauradas = 0;

                // 1. Revertir Reservas
                if (!empty($data['reservas'])) {
                    foreach ($data['reservas'] as $r) {
                        $reserva = Reserva::withoutGlobalScopes()->where('id_reserva', $r['id_reserva'])->first();
                        if ($reserva) {
                            $reserva->update([
                                'tipo_reserva'  => $r['tipo_reserva'],
                                'id_asignatura' => $r['id_asignatura'],
                                'observaciones' => $r['observaciones'],
                            ]);
                            $reservasRevertidas++;
                        }
                    }
                }

                // 2. Revertir Asistencias
                if (!empty($data['asistencias_ids'])) {
                    $asistenciasRevertidas = Asistencia::withoutGlobalScopes()
                        ->whereIn('id', $data['asistencias_ids'])
                        ->update(['id_asignatura' => null]);
                }

                // 3. Restaurar Clases No Realizadas que habían sido eliminadas
                if (!empty($data['clases_no_realizadas'])) {
                    foreach ($data['clases_no_realizadas'] as $claseData) {
                        // Verificar que no exista ya para no duplicar
                        $existe = ClaseNoRealizada::withoutGlobalScopes()
                            ->where('id_espacio', $claseData['id_espacio'])
                            ->where('fecha_clase', $claseData['fecha_clase'])
                            ->where('id_asignatura', $claseData['id_asignatura'])
                            ->where('id_modulo', $claseData['id_modulo'] ?? null)
                            ->exists();

                        if (!$existe) {
                            ClaseNoRealizada::withoutGlobalScopes()->insert($claseData);
                            $clasesRestauradas++;
                        }
                    }
                }

                $this->info("  ✓ Reservas devueltas a su estado previo: {$reservasRevertidas}");
                $this->info("  ✓ Asistencias restauradas (id_asignatura = null): {$asistenciasRevertidas}");
                $this->info("  ✓ Registros de clases no realizadas reinsertados: {$clasesRestauradas}");

            } catch (\Exception $e) {
                $this->error("  ⚠ Error al revertir en {$tenantName}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Reversión finalizada con éxito.");
        return 0;
    }
}
