<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Reserva;
use App\Models\Asistencia;
use App\Helpers\EspacioAliasHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NormalizarEspaciosTHCommand extends Command
{
    protected $signature = 'espacios:normalizar-th 
                            {--dry-run : Solo simula las correcciones sin aplicarlas}
                            {--reconciliar : Ejecuta también la reconciliación de reservas espontáneas}';

    protected $description = 'Normaliza los identificadores de espacios de la sede Talcahuano (TH-30 -> TH-LAB09 y TH-09 -> TH-LAB08 para construcción) y reasigna planificaciones';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $ejecutarReconciliar = $this->option('reconciliar');

        $this->info("=== NORMALIZACIÓN DE ESPACIOS TALCAHUANO (TH) ===" . ($dryRun ? ' (MODO SIMULACIÓN)' : ''));

        // 1. Buscar tenant de Talcahuano
        $tenant = Tenant::where('sede_id', 'TH')
            ->orWhere('domain', 'th')
            ->orWhere('prefijo_espacios', 'TH')
            ->first();

        if (!$tenant) {
            $this->error('No se encontró el tenant para la sede Talcahuano (TH).');
            return 1;
        }

        $this->line("Tenant encontrado: {$tenant->name} ({$tenant->domain})");
        $tenant->makeCurrent();

        // 2. Verificar y asegurar que existan los espacios oficiales en la BD
        $this->info("\n1. Verificando espacios físicos en BD...");
        $this->asegurarEspaciosOficiales($dryRun);

        // 3. Normalizar planificaciones regulares (planificacion_asignaturas)
        $this->info("\n2. Normalizando planificaciones regulares...");
        $this->normalizarPlanificacionesRegulares($dryRun);

        // 4. Normalizar planificaciones de colaboradores
        $this->info("\n3. Normalizando planificaciones de profesores colaboradores...");
        $this->normalizarPlanificacionesColaboradores($dryRun);

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
     * Asegura la existencia de TH-LAB09 y TH-LAB08 en la tabla espacios
     */
    private function asegurarEspaciosOficiales(bool $dryRun): void
    {
        // Obtener ID de piso 1
        $piso1 = DB::connection('tenant')->table('pisos')
            ->where('numero_piso', 1)
            ->first();
        $pisoId = $piso1 ? $piso1->id : 1;

        // Caso TH-LAB09 (Laboratorio Termodinámica / Refrigeración)
        $lab09 = Espacio::withoutGlobalScope('tenant')->find('TH-LAB09');
        if (!$lab09) {
            // Verificar si existía con nombre antiguo
            $antiguo09 = Espacio::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-L09', 'TH-30'])
                ->first();

            if ($antiguo09) {
                $this->line("  → Renombrando espacio {$antiguo09->id_espacio} a TH-LAB09...");
                if (!$dryRun) {
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::connection('tenant')->table('espacios')
                        ->where('id_espacio', $antiguo09->id_espacio)
                        ->update([
                            'id_espacio' => 'TH-LAB09',
                            'nombre_espacio' => 'Laboratorio Termodinámica',
                            'tipo_espacio' => 'Laboratorio',
                        ]);
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } else {
                $this->line("  → Creando nuevo espacio TH-LAB09 (Laboratorio Termodinámica)...");
                if (!$dryRun) {
                    $nuevo = Espacio::create([
                        'id_espacio' => 'TH-LAB09',
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
            $this->line("  ✓ Espacio TH-LAB09 ya existe.");
        }

        // Caso TH-LAB08 (Taller de Construcción)
        $lab08 = Espacio::withoutGlobalScope('tenant')->find('TH-LAB08');
        if (!$lab08) {
            $antiguo08 = Espacio::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-LA8', 'TH-L08'])
                ->first();

            if ($antiguo08) {
                $this->line("  → Renombrando espacio {$antiguo08->id_espacio} a TH-LAB08...");
                if (!$dryRun) {
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::connection('tenant')->table('espacios')
                        ->where('id_espacio', $antiguo08->id_espacio)
                        ->update([
                            'id_espacio' => 'TH-LAB08',
                            'nombre_espacio' => 'Taller de Construcción',
                            'tipo_espacio' => 'Taller',
                        ]);
                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } else {
                $this->line("  → Creando nuevo espacio TH-LAB08 (Taller de Construcción)...");
                if (!$dryRun) {
                    $nuevo = Espacio::create([
                        'id_espacio' => 'TH-LAB08',
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
            $this->line("  ✓ Espacio TH-LAB08 ya existe.");
        }
    }

    /**
     * Normaliza las planificaciones en planificacion_asignaturas
     */
    private function normalizarPlanificacionesRegulares(bool $dryRun): void
    {
        // 1. Reasignar TH-30 y TH-L09 -> TH-LAB09
        $planificaciones30 = Planificacion_Asignatura::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-30', 'TH-L09'])
            ->get();

        $this->line("  → Planificaciones en TH-30 / TH-L09 a mover a TH-LAB09: " . $planificaciones30->count());
        if (!$dryRun && $planificaciones30->isNotEmpty()) {
            Planificacion_Asignatura::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-30', 'TH-L09'])
                ->update(['id_espacio' => 'TH-LAB09']);
        }

        // 2. Reasignar TH-L08 y TH-LA8 -> TH-LAB08
        $planificaciones08Antiguas = Planificacion_Asignatura::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-L08', 'TH-LA8'])
            ->get();

        $this->line("  → Planificaciones en TH-L08 / TH-LA8 a mover a TH-LAB08: " . $planificaciones08Antiguas->count());
        if (!$dryRun && $planificaciones08Antiguas->isNotEmpty()) {
            Planificacion_Asignatura::withoutGlobalScope('tenant')
                ->whereIn('id_espacio', ['TH-L08', 'TH-LA8'])
                ->update(['id_espacio' => 'TH-LAB08']);
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

            if ($espacioResuelto === 'TH-LAB08') {
                $this->line("    • Moviendo: {$asigNombre} ({$carreraNombre}) -> TH-LAB08");
                $movidasConstruccion++;

                if (!$dryRun) {
                    $plan->id_espacio = 'TH-LAB08';
                    $plan->save();
                }
            }
        }

        $this->line("  → Clases de Construcción en TH-09 reasignadas a TH-LAB08: {$movidasConstruccion}");
    }

    /**
     * Normaliza las planificaciones de profesores colaboradores
     */
    private function normalizarPlanificacionesColaboradores(bool $dryRun): void
    {
        $ppc30 = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-30', 'TH-L09'])
            ->count();

        if ($ppc30 > 0) {
            $this->line("  → Planificaciones de colaboradores en TH-30 / TH-L09 a mover a TH-LAB09: {$ppc30}");
            if (!$dryRun) {
                PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                    ->whereIn('id_espacio', ['TH-30', 'TH-L09'])
                    ->update(['id_espacio' => 'TH-LAB09']);
            }
        }

        $ppc08 = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
            ->whereIn('id_espacio', ['TH-L08', 'TH-LA8'])
            ->count();

        if ($ppc08 > 0) {
            $this->line("  → Planificaciones de colaboradores en TH-L08 / TH-LA8 a mover a TH-LAB08: {$ppc08}");
            if (!$dryRun) {
                PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                    ->whereIn('id_espacio', ['TH-L08', 'TH-LA8'])
                    ->update(['id_espacio' => 'TH-LAB08']);
            }
        }
    }
}
