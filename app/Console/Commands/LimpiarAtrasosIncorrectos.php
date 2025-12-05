<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LimpiarAtrasosIncorrectos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atrasos:limpiar-incorrectos 
                            {--dry-run : Solo mostrar lo que se haría sin ejecutar cambios}
                            {--actualizar : Actualizar hora_programada y minutos_atraso en registros válidos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina registros de profesor_atrasos donde el profesor llegó a tiempo o antes de la hora programada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $actualizar = $this->option('actualizar');

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios reales.');
        }

        $this->info('Analizando registros de profesor_atrasos...');
        $this->newLine();

        // Obtener todos los módulos con sus horas de inicio
        $modulos = \App\Models\Modulo::all()->keyBy('id_modulo');
        
        // Obtener todos los registros de atrasos
        $atrasos = DB::table('profesor_atrasos')->get();

        $this->info("Total de registros encontrados: {$atrasos->count()}");
        $this->newLine();

        $eliminados = 0;
        $actualizados = 0;
        $validos = 0;
        $sinDatos = 0;

        $this->output->progressStart($atrasos->count());

        foreach ($atrasos as $atraso) {
            $this->output->progressAdvance();

            // Obtener el primer módulo para determinar la hora programada
            $modulosStr = $atraso->id_modulo;
            $primerModuloId = null;
            
            if (!empty($modulosStr)) {
                $modulosArray = explode(',', $modulosStr);
                $primerModuloId = trim($modulosArray[0]);
            }

            // Obtener hora programada del módulo
            $horaProgramada = null;
            if ($primerModuloId && isset($modulos[$primerModuloId])) {
                $horaProgramada = $modulos[$primerModuloId]->hora_inicio;
            }

            // Si no hay hora de llegada o hora programada, marcar como sin datos
            if (!$atraso->hora_llegada || !$horaProgramada) {
                $sinDatos++;
                continue;
            }

            $horaLlegada = Carbon::parse($atraso->hora_llegada);
            $horaProgramadaCarbon = Carbon::parse($horaProgramada);

            // Verificar si realmente llegó tarde
            if ($horaLlegada->lte($horaProgramadaCarbon)) {
                // El profesor llegó A TIEMPO o ANTES - NO es un atraso real
                $this->newLine();
                $this->line(sprintf(
                    '  ❌ ID %d: Llegó a las %s, clase a las %s (llegó %d min ANTES) - ELIMINAR',
                    $atraso->id,
                    $horaLlegada->format('H:i'),
                    $horaProgramadaCarbon->format('H:i'),
                    $horaProgramadaCarbon->diffInMinutes($horaLlegada)
                ));

                if (!$dryRun) {
                    DB::table('profesor_atrasos')->where('id', $atraso->id)->delete();
                }
                $eliminados++;
            } else {
                // Es un atraso válido
                $minutosAtraso = $horaProgramadaCarbon->diffInMinutes($horaLlegada);
                $validos++;

                // Actualizar si la opción está activa y los valores son incorrectos
                if ($actualizar && ($atraso->hora_programada !== $horaProgramada || $atraso->minutos_atraso != $minutosAtraso)) {
                    if (!$dryRun) {
                        DB::table('profesor_atrasos')
                            ->where('id', $atraso->id)
                            ->update([
                                'hora_programada' => $horaProgramada,
                                'minutos_atraso' => $minutosAtraso,
                                'updated_at' => now(),
                            ]);
                    }
                    $actualizados++;
                    
                    $this->newLine();
                    $this->line(sprintf(
                        '  🔄 ID %d: Actualizado - Llegó %d min tarde (clase: %s, llegada: %s)',
                        $atraso->id,
                        $minutosAtraso,
                        $horaProgramadaCarbon->format('H:i'),
                        $horaLlegada->format('H:i')
                    ));
                }
            }
        }

        $this->output->progressFinish();
        $this->newLine();

        $this->info('=== RESUMEN ===');
        $this->table(
            ['Categoría', 'Cantidad'],
            [
                ['Registros analizados', $atrasos->count()],
                ['Atrasos válidos', $validos],
                ['Eliminados (no eran atrasos)', $eliminados],
                ['Actualizados', $actualizados],
                ['Sin datos suficientes', $sinDatos],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }
}
