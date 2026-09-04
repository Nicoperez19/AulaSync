<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Asistencia;
use App\Models\ClaseNoRealizada;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\ProfesorColaborador;
use App\Models\Tenant;
use App\Helpers\ModulosHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReconciliarReservasEspontaneas extends Command
{
    protected $signature = 'reservas:reconciliar-espontaneas 
                            {--desde= : Fecha de inicio en formato YYYY-MM-DD (por defecto inicio del mes anterior)}
                            {--dry-run : Solo simular los cambios sin guardarlos}';

    protected $description = 'Reconcilia reservas históricas marcadas como espontáneas que corresponden a clases planificadas (regulares o colaboradores)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $desde = $this->option('desde') ?: Carbon::now()->subMonth()->startOfMonth()->toDateString();

        $this->info("Iniciando reconciliación de reservas desde {$desde}..." . ($dryRun ? ' (MODO SIMULACIÓN)' : ''));

        $tenants = Tenant::all();

        $backupData = [
            'created_at' => Carbon::now()->toDateTimeString(),
            'desde' => $desde,
            'tenants' => [],
        ];

        foreach ($tenants as $tenant) {
            $this->line("Procesando tenant: {$tenant->name} ({$tenant->domain})...");
            $this->processTenant($tenant, $desde, $dryRun, $backupData);
        }

        if (!$dryRun && !empty($backupData['tenants'])) {
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }
            $filename = 'reconciliacion_backup_' . Carbon::now()->format('Y-m-d_His') . '.json';
            $fullPath = $backupDir . DIRECTORY_SEPARATOR . $filename;
            File::put($fullPath, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->newLine();
            $this->info("==================================================================");
            $this->info("🛡  RESPALDO GENERADO AUTOMÁTICAMENTE:");
            $this->info("   Archivo: {$fullPath}");
            $this->info("   Si necesitas revertir estos cambios, ejecuta:");
            $this->line("   <fg=yellow>php artisan reservas:revertir-reconciliacion</>");
            $this->info("==================================================================");
        }

        $this->info("Proceso de reconciliación finalizado exitosamente.");
        return 0;
    }

    private function processTenant($tenant, string $desde, bool $dryRun, array &$backupData)
    {
        try {
            $tenant->makeCurrent();

            // Buscar reservas que quedaron como espontáneas o sin asignatura vinculada
            $reservas = Reserva::withoutGlobalScopes()
                ->where('fecha_reserva', '>=', $desde)
                ->whereNotNull('run_profesor')
                ->where(function ($q) {
                    $q->where('tipo_reserva', 'espontanea')
                      ->orWhereNull('id_asignatura');
                })
                ->get();

            $reconciliadas = 0;
            $asistenciasActualizadas = 0;
            $clasesLimpiadas = 0;

            $tenantBackup = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_database' => $tenant->database,
                'reservas' => [],
                'asistencias_ids' => [],
                'clases_no_realizadas' => [],
            ];

            foreach ($reservas as $reserva) {
                try {
                    $run = $reserva->run_profesor;
                    if (!$run || !$reserva->hora || !$reserva->fecha_reserva) {
                        continue;
                    }

                    $runLimpio = preg_replace('/[^0-9kK]/', '', $run);

                    $fechaCarbon = Carbon::parse($reserva->fecha_reserva);
                    $fechaStr = $fechaCarbon->format('Y-m-d');
                    $horaStr = Carbon::parse($reserva->hora)->format('H:i:s');

                    $diaSemana = $fechaCarbon->format('l');
                    $diasMap = [
                        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
                    ];
                    $diaEsp = $diasMap[$diaSemana] ?? $diaSemana;
                    $diaNorm = ModulosHelper::normalizarDia($diaEsp);
                    $diasPosibles = array_unique([strtolower($diaEsp), $diaNorm, 'miércoles', 'miercoles', 'sábado', 'sabado']);

                    $horaCarbon = Carbon::parse($fechaStr . ' ' . $horaStr);
                    $horaMaxInicio = $horaCarbon->copy()->addMinutes(60)->toTimeString();

                    $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($reserva->id_espacio, $tenant->sede_id ?? 'TH');

                    // 1. Buscar en planificación regular del profesor
                    $clasePlanificada = DB::connection('tenant')
                        ->table('planificacion_asignaturas as pa')
                        ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                        ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                        ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                        ->whereIn('pa.id_espacio', $espaciosEquiv)
                        ->where(function ($q) use ($run, $runLimpio) {
                            $q->where('h.run_profesor', $run)
                              ->orWhere('h.run_profesor', $runLimpio)
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(h.run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                        })
                        ->whereIn('m.dia', $diasPosibles)
                        ->where('m.hora_inicio', '<=', $horaMaxInicio)
                        ->where('m.hora_termino', '>=', $horaStr)
                        ->select('a.id_asignatura', 'a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino')
                        ->orderBy('m.hora_inicio', 'asc')
                        ->first();

                    // 2. Si no se encuentra, buscar en profesores colaboradores
                    if (!$clasePlanificada) {
                        $clasePlanificada = DB::connection('tenant')
                            ->table('planificaciones_profesores_colaboradores as ppc')
                            ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                            ->join('modulos as m', 'ppc.id_modulo', '=', 'm.id_modulo')
                            ->leftJoin('asignaturas as a', 'pc.id_asignatura', '=', 'a.id_asignatura')
                            ->whereIn('ppc.id_espacio', $espaciosEquiv)
                            ->where(function ($q) use ($run, $runLimpio) {
                                $q->where('pc.run_profesor_colaborador', $run)
                                  ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                            })
                            ->where('pc.estado', 'activo')
                            ->whereIn('m.dia', $diasPosibles)
                            ->where('m.hora_inicio', '<=', $horaMaxInicio)
                            ->where('m.hora_termino', '>=', $horaStr)
                            ->select(
                                DB::raw('COALESCE(a.id_asignatura, pc.id_asignatura) as id_asignatura'),
                                DB::raw('COALESCE(a.nombre_asignatura, pc.nombre_asignatura_temporal, "Clase Colaborador") as nombre_asignatura'),
                                'm.hora_inicio',
                                'm.hora_termino'
                            )
                            ->orderBy('m.hora_inicio', 'asc')
                            ->first();
                    }

                    // 3. Fallback adicional: verificar si es colaborador de la asignatura dictada en este espacio y horario
                    if (!$clasePlanificada) {
                        $clasePlanificada = DB::connection('tenant')
                            ->table('planificacion_asignaturas as pa')
                            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                            ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                            ->join('profesores_colaboradores as pc', 'a.id_asignatura', '=', 'pc.id_asignatura')
                            ->whereIn('pa.id_espacio', $espaciosEquiv)
                            ->where(function ($q) use ($run, $runLimpio) {
                                $q->where('pc.run_profesor_colaborador', $run)
                                  ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                            })
                            ->where('pc.estado', 'activo')
                            ->whereIn('m.dia', $diasPosibles)
                            ->where('m.hora_inicio', '<=', $horaMaxInicio)
                            ->where('m.hora_termino', '>=', $horaStr)
                            ->select('a.id_asignatura', 'a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino')
                            ->orderBy('m.hora_inicio', 'asc')
                            ->first();
                    }

                    if ($clasePlanificada && !empty($clasePlanificada->id_asignatura)) {
                        $this->line("  ✓ Reconciliando Reserva {$reserva->id_reserva} (Fecha: {$fechaStr}, Sala: {$reserva->id_espacio}) -> Asignatura: {$clasePlanificada->nombre_asignatura} ({$clasePlanificada->id_asignatura})");

                        if (!$dryRun) {
                            // 1. Guardar copia del estado original de la reserva
                            $tenantBackup['reservas'][] = [
                                'id_reserva'    => $reserva->id_reserva,
                                'tipo_reserva'  => $reserva->tipo_reserva,
                                'id_asignatura' => $reserva->id_asignatura,
                                'observaciones' => $reserva->observaciones,
                            ];

                            // 2. Guardar IDs de asistencias antes de modificar
                            $asistenciasIds = Asistencia::withoutGlobalScopes()
                                ->where('id_reserva', $reserva->id_reserva)
                                ->whereNull('id_asignatura')
                                ->pluck('id')
                                ->toArray();

                            if (!empty($asistenciasIds)) {
                                $tenantBackup['asistencias_ids'] = array_merge(
                                    $tenantBackup['asistencias_ids'],
                                    $asistenciasIds
                                );
                            }

                            // 3. Guardar registros de clases no realizadas antes de borrarlas
                            $clasesParaBorrar = ClaseNoRealizada::withoutGlobalScopes()
                                ->whereIn('id_espacio', $espaciosEquiv)
                                ->where('fecha_clase', $fechaStr)
                                ->where('id_asignatura', $clasePlanificada->id_asignatura)
                                ->get()
                                ->toArray();

                            if (!empty($clasesParaBorrar)) {
                                $tenantBackup['clases_no_realizadas'] = array_merge(
                                    $tenantBackup['clases_no_realizadas'],
                                    $clasesParaBorrar
                                );
                            }

                            // Actualizar reserva
                            $reserva->update([
                                'tipo_reserva'  => 'clase',
                                'id_asignatura' => $clasePlanificada->id_asignatura,
                                'observaciones' => trim(($reserva->observaciones ?? '') . "\n[Reconciliado con clase programada: {$clasePlanificada->nombre_asignatura}]"),
                            ]);

                            // Actualizar asistencias de estudiantes que estaban sin asignatura
                            $asistenciasAfectadas = Asistencia::withoutGlobalScopes()
                                ->where('id_reserva', $reserva->id_reserva)
                                ->whereNull('id_asignatura')
                                ->update(['id_asignatura' => $clasePlanificada->id_asignatura]);

                            $asistenciasActualizadas += $asistenciasAfectadas;

                            // Limpiar registros incorrectos de clases no realizadas si se habían generado por error
                            $limpiadas = ClaseNoRealizada::withoutGlobalScopes()
                                ->whereIn('id_espacio', $espaciosEquiv)
                                ->where('fecha_clase', $fechaStr)
                                ->where('id_asignatura', $clasePlanificada->id_asignatura)
                                ->delete();

                            $clasesLimpiadas += $limpiadas;
                        }

                        $reconciliadas++;
                    }
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Error al procesar reserva {$reserva->id_reserva}: " . $e->getMessage());
                }
            }

            if (!$dryRun && (!empty($tenantBackup['reservas']) || !empty($tenantBackup['clases_no_realizadas']))) {
                $backupData['tenants'][$tenant->id] = $tenantBackup;
            }

            $this->info("  Total en {$tenant->name}: {$reconciliadas} reservas reconciliadas, {$asistenciasActualizadas} asistencias corregidas, {$clasesLimpiadas} inasistencias falsas eliminadas.");

        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: " . $e->getMessage());
        }
    }
}
