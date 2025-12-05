<?php

namespace App\Console\Commands;

use App\Models\Modulo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActualizarHorasAtrasos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atrasos:actualizar-horas 
                            {--dry-run : Solo mostrar lo que se haría sin ejecutar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza hora_programada y minutos_atraso en registros de profesor_atrasos basándose en los módulos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios reales.');
        }

        $this->info('Cargando módulos...');

        // Obtener todos los módulos con sus horas de inicio
        $modulos = Modulo::all()->keyBy('id_modulo');
        $this->info("Módulos cargados: {$modulos->count()}");

        // Obtener registros de profesor_atrasos que tienen hora_programada NULL o minutos_atraso 0
        $atrasos = DB::table('profesor_atrasos')
            ->where(function ($query) {
                $query->whereNull('hora_programada')
                      ->orWhere('minutos_atraso', 0);
            })
            ->get();

        $this->info("Registros a procesar: {$atrasos->count()}");

        if ($atrasos->isEmpty()) {
            $this->info('No hay registros para actualizar.');
            return 0;
        }

        $this->newLine();
        $this->output->progressStart($atrasos->count());

        $actualizados = 0;
        $errores = 0;
        $sinModulo = 0;

        foreach ($atrasos as $atraso) {
            // Obtener el primer módulo (el que determina la hora de inicio)
            $modulosStr = $atraso->id_modulo;
            $primerModuloId = null;

            if (!empty($modulosStr)) {
                $modulosArray = explode(',', $modulosStr);
                $primerModuloId = trim($modulosArray[0]);
            }

            $horaProgramada = null;

            if ($primerModuloId && isset($modulos[$primerModuloId])) {
                $horaProgramada = $modulos[$primerModuloId]->hora_inicio;
            } else {
                $sinModulo++;
                $this->output->progressAdvance();
                continue;
            }

            // Calcular minutos de atraso
            $minutosAtraso = 0;
            if ($horaProgramada && $atraso->hora_llegada) {
                $programada = Carbon::parse($horaProgramada);
                $llegada = Carbon::parse($atraso->hora_llegada);

                // Si llegó después de la hora programada
                if ($llegada->gt($programada)) {
                    $minutosAtraso = $programada->diffInMinutes($llegada);
                }
            }

            if (!$dryRun) {
                try {
                    DB::table('profesor_atrasos')
                        ->where('id', $atraso->id)
                        ->update([
                            'hora_programada' => $horaProgramada,
                            'minutos_atraso' => $minutosAtraso,
                            'updated_at' => now(),
                        ]);

                    $actualizados++;
                } catch (\Exception $e) {
                    $errores++;
                    $this->newLine();
                    $this->error("Error en ID {$atraso->id}: " . $e->getMessage());
                }
            } else {
                $actualizados++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();

        $this->info('=== Resumen ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Actualizados', $actualizados],
                ['Sin módulo encontrado', $sinModulo],
                ['Errores', $errores],
            ]
        );

        if ($dryRun) {
            $this->warn('Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }
}
