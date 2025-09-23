<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;

class VerificarDatosTabla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verificar-datos-tabla {--fix : Reparar automáticamente los datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar y reparar datos problemáticos en la tabla de módulos actuales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando datos de la tabla de módulos actuales...');

        $fix = $this->option('fix');
        $errores = 0;
        $reparaciones = 0;

        // Verificar espacios
        $this->info('📍 Verificando espacios...');
        $espacios = Espacio::all();
        foreach ($espacios as $espacio) {
            if (empty($espacio->id_espacio)) {
                $errores++;
                $this->error("⚠️  Espacio con ID vacío encontrado: " . $espacio->id);
                
                if ($fix) {
                    $espacio->id_espacio = 'ESP_' . $espacio->id;
                    $espacio->save();
                    $reparaciones++;
                    $this->info("✅ Reparado: ID asignado como ESP_" . $espacio->id);
                }
            }

            if (empty($espacio->nombre_espacio)) {
                $errores++;
                $this->error("⚠️  Espacio sin nombre: " . $espacio->id_espacio);
                
                if ($fix) {
                    $espacio->nombre_espacio = 'Espacio ' . $espacio->id_espacio;
                    $espacio->save();
                    $reparaciones++;
                    $this->info("✅ Reparado: Nombre asignado");
                }
            }
        }

        // Verificar pisos
        $this->info('🏢 Verificando pisos...');
        $pisos = Piso::all();
        foreach ($pisos as $piso) {
            if (empty($piso->nombre_piso)) {
                $errores++;
                $this->error("⚠️  Piso sin nombre: " . $piso->id);
                
                if ($fix) {
                    $piso->nombre_piso = 'Piso ' . $piso->id;
                    $piso->save();
                    $reparaciones++;
                    $this->info("✅ Reparado: Nombre de piso asignado");
                }
            }
        }

        // Verificar planificaciones con datos nulos
        $this->info('📚 Verificando planificaciones...');
        $planificaciones = Planificacion_Asignatura::with(['asignatura', 'asignatura.profesor'])->get();
        foreach ($planificaciones as $planificacion) {
            if (!$planificacion->asignatura) {
                $errores++;
                $this->error("⚠️  Planificación sin asignatura: ID " . $planificacion->id);
                
                if ($fix) {
                    // Eliminamos planificaciones huérfanas
                    $planificacion->delete();
                    $reparaciones++;
                    $this->info("✅ Eliminada planificación huérfana");
                }
            } elseif ($planificacion->asignatura && !$planificacion->asignatura->profesor) {
                $errores++;
                $this->error("⚠️  Asignatura sin profesor: " . ($planificacion->asignatura->nombre_asignatura ?? 'Sin nombre'));
            }
        }

        // Verificar reservas
        $this->info('📅 Verificando reservas...');
        $reservas = Reserva::all();
        foreach ($reservas as $reserva) {
            if (empty($reserva->id_espacio)) {
                $errores++;
                $this->error("⚠️  Reserva sin espacio asignado: ID " . $reserva->id);
                
                if ($fix) {
                    $reserva->delete();
                    $reparaciones++;
                    $this->info("✅ Eliminada reserva sin espacio");
                }
            }
        }

        // Resumen
        $this->newLine();
        if ($errores === 0) {
            $this->info('🎉 No se encontraron problemas en los datos');
        } else {
            if ($fix) {
                $this->info("🔧 Se encontraron {$errores} problemas y se repararon {$reparaciones}");
            } else {
                $this->warn("⚠️  Se encontraron {$errores} problemas. Ejecuta con --fix para repararlos automáticamente");
            }
        }

        return 0;
    }
}