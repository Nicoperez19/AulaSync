<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LimpiarPlanificacionesDuplicadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'planificaciones:limpiar-duplicados 
                            {--periodo= : Período específico a limpiar (ej: 2025-2)}
                            {--dry-run : Solo mostrar qué se eliminaría sin eliminar}
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta y elimina planificaciones duplicadas (misma asignatura, módulo, espacio y horario)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->option('periodo');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('===========================================');
        $this->info('  LIMPIEZA DE PLANIFICACIONES DUPLICADAS  ');
        $this->info('===========================================');
        $this->newLine();

        // Construir query para detectar duplicados
        $query = DB::table('planificacion_asignaturas as pa')
            ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
            ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
            ->join('espacios as e', 'pa.id_espacio', '=', 'e.id_espacio')
            ->select(
                'pa.id_asignatura',
                'pa.id_modulo',
                'pa.id_espacio',
                'pa.id_horario',
                DB::raw('COUNT(*) as veces_duplicada'),
                DB::raw('GROUP_CONCAT(pa.id ORDER BY pa.id) as ids'),
                DB::raw('MIN(pa.id) as id_a_mantener'),
                'a.nombre_asignatura',
                'a.codigo_asignatura',
                'm.dia',
                'm.hora_inicio',
                'e.nombre_espacio',
                'h.periodo'
            )
            ->groupBy('pa.id_asignatura', 'pa.id_modulo', 'pa.id_espacio', 'pa.id_horario')
            ->having(DB::raw('COUNT(*)'), '>', 1);

        if ($periodo) {
            $query->where('h.periodo', $periodo);
            $this->info("Filtrando por período: {$periodo}");
        } else {
            $this->info('Analizando todos los períodos...');
        }

        $duplicados = $query->get();

        if ($duplicados->isEmpty()) {
            $this->info('✅ No se encontraron duplicados.');

            return 0;
        }

        // Mostrar resumen
        $totalDuplicados = $duplicados->sum(function ($item) {
            return $item->veces_duplicada - 1; // -1 porque uno se mantiene
        });

        $this->warn("⚠️  Se encontraron {$duplicados->count()} grupos con duplicados");
        $this->warn("   Total de registros a eliminar: {$totalDuplicados}");
        $this->newLine();

        // Mostrar tabla de duplicados
        $this->info('Detalle de duplicados encontrados:');
        $this->newLine();

        $tableData = $duplicados->map(function ($item) {
            return [
                'Asignatura' => substr($item->nombre_asignatura, 0, 30),
                'Código' => $item->codigo_asignatura,
                'Día' => $item->dia,
                'Hora' => $item->hora_inicio,
                'Espacio' => $item->nombre_espacio,
                'Período' => $item->periodo,
                'Duplicados' => $item->veces_duplicada,
                'A eliminar' => $item->veces_duplicada - 1,
            ];
        })->toArray();

        $this->table(
            ['Asignatura', 'Código', 'Día', 'Hora', 'Espacio', 'Período', 'Duplicados', 'A eliminar'],
            $tableData
        );

        $this->newLine();

        if ($dryRun) {
            $this->info('🔍 Modo DRY-RUN: No se eliminará nada.');
            $this->info('   Ejecuta sin --dry-run para eliminar los duplicados.');

            return 0;
        }

        // Confirmar eliminación
        if (! $force && ! $this->confirm("¿Deseas eliminar {$totalDuplicados} registros duplicados?")) {
            $this->info('Operación cancelada.');

            return 0;
        }

        // Proceder con la eliminación
        $this->info('Eliminando duplicados...');
        $eliminados = 0;

        DB::beginTransaction();

        try {
            foreach ($duplicados as $duplicado) {
                $ids = explode(',', $duplicado->ids);
                $idMantener = $ids[0]; // Mantener el primero (más antiguo)
                $idsEliminar = array_slice($ids, 1);

                $deleted = DB::table('planificacion_asignaturas')
                    ->whereIn('id', $idsEliminar)
                    ->delete();

                $eliminados += $deleted;

                $this->line("  ✓ {$duplicado->nombre_asignatura} ({$duplicado->dia} {$duplicado->hora_inicio}): eliminados {$deleted} duplicados");
            }

            DB::commit();

            $this->newLine();
            $this->info("✅ Limpieza completada. Se eliminaron {$eliminados} registros duplicados.");

            // Log de la operación
            Log::info('Limpieza de planificaciones duplicadas completada', [
                'periodo' => $periodo ?? 'todos',
                'grupos_duplicados' => $duplicados->count(),
                'registros_eliminados' => $eliminados,
                'usuario' => auth()->user()->name ?? 'CLI',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error durante la eliminación: '.$e->getMessage());
            Log::error('Error en limpieza de duplicados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }

        return 0;
    }
}
