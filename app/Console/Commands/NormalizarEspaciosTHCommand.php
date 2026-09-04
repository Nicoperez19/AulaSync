<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Helpers\EspacioAliasHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NormalizarEspaciosTHCommand extends Command
{
    protected $signature = 'espacios:normalizar-th 
                            {--dry-run : Solo simula las correcciones sin aplicarlas}
                            {--reconciliar : Ejecuta también la reconciliación de reservas espontáneas}
                            {--revertir : Revierte los cambios aplicados usando el último archivo de respaldo}
                            {--archivo= : Archivo específico de respaldo a revertir}';

    protected $description = 'Normaliza los identificadores de espacios de la sede Talcahuano (TH-30 -> TH-L09 y TH-09 -> TH-L08 para construcción) y reasigna planificaciones';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $ejecutarReconciliar = $this->option('reconciliar');
        $revertir = $this->option('revertir');

        // 1. Buscar tenant de Talcahuano
        $tenant = Tenant::where('sede_id', 'TH')
            ->orWhere('domain', 'th')
            ->orWhere('prefijo_espacios', 'TH')
            ->first();

        if (!$tenant) {
            $this->error('No se encontró el tenant para la sede Talcahuano (TH).');
            return 1;
        }

        $tenant->makeCurrent();

        // Si se pide revertir
        if ($revertir) {
            return $this->revertirNormalizacion($tenant);
        }

        $this->info("=== NORMALIZACIÓN DE ESPACIOS TALCAHUANO (TH) ===" . ($dryRun ? ' (MODO SIMULACIÓN)' : ''));
        $this->line("Tenant encontrado: {$tenant->name} ({$tenant->domain})");

        // Estructura de respaldo
        $backup = [
            'created_at' => Carbon::now()->toDateTimeString(),
            'tenant_id'  => $tenant->id,
            'planificaciones_regulares' => [],
            'planificaciones_colaboradores' => [],
        ];

        // 2. Verificar y asegurar que existan los espacios oficiales en la BD
        $this->info("\n1. Verificando espacios físicos en BD...");
        $this->asegurarEspaciosOficiales($dryRun);

        // 3. Normalizar planificaciones regulares (planificacion_asignaturas)
        $this->info("\n2. Normalizando planificaciones regulares...");
        $this->normalizarPlanificacionesRegulares($dryRun, $backup);

        // 4. Normalizar planificaciones de colaboradores
        $this->info("\n3. Normalizando planificaciones de profesores colaboradores...");
        $this->normalizarPlanificacionesColaboradores($dryRun, $backup);

        // Guardar archivo de respaldo si se aplicaron cambios
        if (!$dryRun) {
            $backupDir = storage_path('app/backups');
            if (!\Illuminate\Support\Facades\File::exists($backupDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($backupDir, 0755, true);
            }
            $filename = 'normalizacion_th_backup_' . Carbon::now()->format('Y-m-d_His') . '.json';
            $fullPath = $backupDir . DIRECTORY_SEPARATOR . $filename;
            \Illuminate\Support\Facades\File::put($fullPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->newLine();
            $this->info("==================================================================");
            $this->info("🛡  RESPALDO GENERADO AUTOMÁTICAMENTE:");
            $this->info("   Archivo: {$fullPath}");
            $this->info("   Si necesitas revertir estos cambios en cualquier momento, ejecuta:");
            $this->line("   <fg=yellow>php artisan espacios:normalizar-th --revertir</>");
            $this->info("==================================================================");
        }

        // 5. Reconciliación opcional de reservas
        if ($ejecutarReconciliar) {
            $this->info("\n4. Ejecutando reconciliación de reservas espontáneas...");
            $this->call('reservas:reconciliar-espontaneas', [
                '--desde' => Carbon::now()->subMonths(2)->toDateString(),
                '--dry-run' => $dryRun,
            ]);
        } else {
            $this->newLine();
            $this->line("<fg=yellow>Nota: Para regularizar reservas históricas pasadas, ejecuta:</>");
            $this->line("<fg=cyan>php artisan reservas:reconciliar-espontaneas --desde=" . Carbon::now()->subMonth()->startOfMonth()->toDateString() . "</>");
        }

        $this->info("\nProceso de normalización finalizado con éxito.");
        return 0;
    }

    /**
     * Asegura la existencia de TH-L09 y TH-L08 en la tabla espacios
     */
    private function asegurarEspaciosOficiales(bool $dryRun): void
    {
        // Obtener ID de piso 1
        $piso1 = DB::connection('tenant')->table('pisos')
            ->where('numero_piso', 1)
            ->first();
        $pisoId = $piso1 ? $piso1->id : 1;

        // Caso TH-L09 (Laboratorio Termodinámica / Refrigeración)
        $lab09 = Espacio::withoutGlobalScope('tenant')->find('TH-L09');
        if (!$lab09) {
            // Verificar si existía como TH-30 o TH-LAB09
            $antiguo09 = Espacio::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])
                ->first();

            if ($antiguo09) {
                $this->line("  → Renombrando espacio {$antiguo09->id_espacio} a TH-L09...");
                if (!$dryRun) {
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::connection('tenant')->table('espacios')
                        ->where('id_espacio', $antiguo09->id_espacio)
                        ->update([
                            'id_espacio' => 'TH-L09',
                            'nombre_espacio' => 'Laboratorio Termodinámica',
                            'tipo_espacio' => 'Laboratorio',
                        ]);
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } else {
                $this->line("  → Creando espacio TH-L09 (Laboratorio Termodinámica)...");
                if (!$dryRun) {
                    $nuevo = Espacio::create([
                        'id_espacio' => 'TH-L09',
                        'nombre_espacio' => 'Laboratorio Termodinámica',
                        'piso_id' => $pisoId,
                        'tipo_espacio' => 'Laboratorio',
                        'estado' => 'Disponible',
                        'puestos_disponibles' => 38,
                        'capacidad_maxima' => 38,
                    ]);
                    try {
                        $nuevo->generateQR();
                    } catch (\Exception $e) {}
                }
            }
        } else {
            $this->line("  ✓ Espacio TH-L09 ya existe.");
            if (!$dryRun) {
                Espacio::withoutGlobalScope('tenant')->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])->delete();
            }
        }

        // Caso TH-L08 (Taller de Construcción)
        $lab08 = Espacio::withoutGlobalScope('tenant')->find('TH-L08');
        if (!$lab08) {
            $antiguo08 = Espacio::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])
                ->first();

            if ($antiguo08) {
                $this->line("  → Renombrando espacio {$antiguo08->id_espacio} a TH-L08...");
                if (!$dryRun) {
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::connection('tenant')->table('espacios')
                        ->where('id_espacio', $antiguo08->id_espacio)
                        ->update([
                            'id_espacio' => 'TH-L08',
                            'nombre_espacio' => 'Taller de Construcción',
                            'tipo_espacio' => 'Taller',
                        ]);
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } else {
                $this->line("  → Creando espacio TH-L08 (Taller de Construcción)...");
                if (!$dryRun) {
                    $nuevo = Espacio::create([
                        'id_espacio' => 'TH-L08',
                        'nombre_espacio' => 'Taller de Construcción',
                        'piso_id' => $pisoId,
                        'tipo_espacio' => 'Taller',
                        'estado' => 'Disponible',
                        'puestos_disponibles' => 30,
                        'capacidad_maxima' => 30,
                    ]);
                    try {
                        $nuevo->generateQR();
                    } catch (\Exception $e) {}
                }
            }
        } else {
            $this->line("  ✓ Espacio TH-L08 ya existe.");
            if (!$dryRun) {
                Espacio::withoutGlobalScope('tenant')->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])->delete();
            }
        }
    }

    /**
     * Normaliza las planificaciones en planificacion_asignaturas
     */
    private function normalizarPlanificacionesRegulares(bool $dryRun, array &$backup): void
    {
        // 1. Reasignar TH-30 y TH-LAB09 -> TH-L09
        $planificaciones30 = Planificacion_Asignatura::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])
            ->get();

        $this->line("  → Planificaciones en TH-30 / TH-LAB09 a mover a TH-L09: " . $planificaciones30->count());
        foreach ($planificaciones30 as $p) {
            $backup['planificaciones_regulares'][] = [
                'id' => $p->id ?? $p->id_planificacion,
                'id_asignatura' => $p->id_asignatura,
                'id_modulo' => $p->id_modulo,
                'id_espacio_anterior' => $p->id_espacio,
                'id_espacio_nuevo' => 'TH-L09',
            ];
        }

        if (!$dryRun && $planificaciones30->isNotEmpty()) {
            Planificacion_Asignatura::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])
                ->update(['id_espacio' => 'TH-L09']);
        }

        // 2. Reasignar TH-LA8 y TH-LAB08 -> TH-L08
        $planificaciones08Antiguas = Planificacion_Asignatura::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])
            ->get();

        $this->line("  → Planificaciones en TH-LA8 / TH-LAB08 a mover a TH-L08: " . $planificaciones08Antiguas->count());
        foreach ($planificaciones08Antiguas as $p) {
            $backup['planificaciones_regulares'][] = [
                'id' => $p->id ?? $p->id_planificacion,
                'id_asignatura' => $p->id_asignatura,
                'id_modulo' => $p->id_modulo,
                'id_espacio_anterior' => $p->id_espacio,
                'id_espacio_nuevo' => 'TH-L08',
            ];
        }

        if (!$dryRun && $planificaciones08Antiguas->isNotEmpty()) {
            Planificacion_Asignatura::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])
                ->update(['id_espacio' => 'TH-L08']);
        }

        // 3. Reasignar clases de Construcción que quedaron en TH-09
        $planificacionesTH09 = Planificacion_Asignatura::withoutGlobalScope('tenant')
            ->with(['asignatura.carrera'])
            ->where('id_espacio', 'TH-09')
            ->get();

        $movidasConstruccion = 0;
        foreach ($planificacionesTH09 as $plan) {
            $carreraNombre = $plan->asignatura?->carrera?->nombre ?? '';
            $asigNombre = $plan->asignatura?->nombre_asignatura ?? '';

            $espacioResuelto = EspacioAliasHelper::normalizar('TH-09', 'TH', [
                'carrera' => $carreraNombre,
                'asignatura' => $asigNombre,
            ]);

            if ($espacioResuelto === 'TH-L08') {
                $this->line("    • Moviendo: {$asigNombre} ({$carreraNombre}) -> TH-L08");
                $movidasConstruccion++;

                $backup['planificaciones_regulares'][] = [
                    'id' => $plan->id ?? $plan->id_planificacion,
                    'id_asignatura' => $plan->id_asignatura,
                    'id_modulo' => $plan->id_modulo,
                    'id_espacio_anterior' => 'TH-09',
                    'id_espacio_nuevo' => 'TH-L08',
                ];

                if (!$dryRun) {
                    $plan->id_espacio = 'TH-L08';
                    $plan->save();
                }
            }
        }

        $this->line("  → Clases de Construcción en TH-09 reasignadas a TH-L08: {$movidasConstruccion}");
    }

    /**
     * Normaliza las planificaciones de profesores colaboradores
     */
    private function normalizarPlanificacionesColaboradores(bool $dryRun, array &$backup): void
    {
        $ppc30 = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])
            ->get();

        if ($ppc30->isNotEmpty()) {
            $this->line("  → Planificaciones de colaboradores en TH-30 / TH-LAB09 a mover a TH-L09: " . $ppc30->count());
            foreach ($ppc30 as $p) {
                $backup['planificaciones_colaboradores'][] = [
                    'id' => $p->id,
                    'id_espacio_anterior' => $p->id_espacio,
                    'id_espacio_nuevo' => 'TH-L09',
                ];
            }
            if (!$dryRun) {
                PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                    ->whereIn('id_espacio', ['TH-30', 'TH-LAB09'])
                    ->update(['id_espacio' => 'TH-L09']);
            }
        }

        $ppc08 = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])
            ->get();

        if ($ppc08->isNotEmpty()) {
            $this->line("  → Planificaciones de colaboradores en TH-LA8 / TH-LAB08 a mover a TH-L08: " . $ppc08->count());
            foreach ($ppc08 as $p) {
                $backup['planificaciones_colaboradores'][] = [
                    'id' => $p->id,
                    'id_espacio_anterior' => $p->id_espacio,
                    'id_espacio_nuevo' => 'TH-L08',
                ];
            }
            if (!$dryRun) {
                PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                    ->whereIn('id_espacio', ['TH-LA8', 'TH-LAB08'])
                    ->update(['id_espacio' => 'TH-L08']);
            }
        }
    }

    /**
     * Revierte los cambios aplicados según un archivo de respaldo
     */
    private function revertirNormalizacion(Tenant $tenant): int
    {
        $archivo = $this->option('archivo');

        if (!$archivo) {
            $backupDir = storage_path('app/backups');
            if (!\Illuminate\Support\Facades\File::exists($backupDir)) {
                $this->error("No se encontró el directorio de respaldos en: {$backupDir}");
                return 1;
            }

            $archivos = \Illuminate\Support\Facades\File::glob($backupDir . DIRECTORY_SEPARATOR . 'normalizacion_th_backup_*.json');
            if (empty($archivos)) {
                $this->error("No se encontraron archivos de respaldo en {$backupDir}.");
                return 1;
            }

            rsort($archivos);
            $archivo = $archivos[0];
        }

        if (!\Illuminate\Support\Facades\File::exists($archivo)) {
            $this->error("El archivo de respaldo especificado no existe: {$archivo}");
            return 1;
        }

        $this->warn("==================================================================");
        $this->warn(" REVERSIÓN DE NORMALIZACIÓN DE ESPACIOS TH");
        $this->warn(" Archivo de respaldo: {$archivo}");
        $this->warn("==================================================================");

        $contenido = json_decode(\Illuminate\Support\Facades\File::get($archivo), true);
        if (!$contenido || !isset($contenido['planificaciones_regulares'])) {
            $this->error("El archivo de respaldo no tiene una estructura válida.");
            return 1;
        }

        $revertidasRegulares = 0;
        foreach ($contenido['planificaciones_regulares'] as $item) {
            $q = Planificacion_Asignatura::withoutGlobalScope('tenant');
            if (!empty($item['id'])) {
                $q->where('id', $item['id']);
            } else {
                $q->where('id_asignatura', $item['id_asignatura'])
                  ->where('id_modulo', $item['id_modulo'])
                  ->where('id_espacio', $item['id_espacio_nuevo']);
            }

            $actualizado = $q->update(['id_espacio' => $item['id_espacio_anterior']]);
            if ($actualizado) {
                $revertidasRegulares++;
            }
        }

        $revertidasColab = 0;
        foreach ($contenido['planificaciones_colaboradores'] ?? [] as $item) {
            $actualizado = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                ->where('id', $item['id'])
                ->update(['id_espacio' => $item['id_espacio_anterior']]);
            if ($actualizado) {
                $revertidasColab++;
            }
        }

        $this->info("✓ Planificaciones regulares revertidas: {$revertidasRegulares}");
        $this->info("✓ Planificaciones de colaboradores revertidas: {$revertidasColab}");
        $this->info("Reversión completada exitosamente.");

        return 0;
    }
}
