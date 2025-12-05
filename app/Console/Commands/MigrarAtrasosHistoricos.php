<?php

namespace App\Console\Commands;

use App\Models\ClaseNoRealizada;
use App\Models\Planificacion_Asignatura;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MigrarAtrasosHistoricos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atrasos:migrar-historicos 
                            {--dry-run : Solo mostrar lo que se haría sin ejecutar cambios}
                            {--backup : Crear backup de los registros antes de migrar}
                            {--no-backup : No crear backup (omitir pregunta)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra registros de clases_no_realizadas con Auto-corregido a la tabla profesor_atrasos y elimina registros duplicados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando registros con [Auto-corregido] en observaciones...');

        // Buscar registros que tienen "[Auto-corregido" en observaciones (estado justificado)
        $registrosAutocorregidos = ClaseNoRealizada::where(function($q) {
                $q->where('observaciones', 'like', '%[Auto-corregido%')
                  ->orWhere('observaciones', 'like', '%auto-corregido%')
                  ->orWhere('observaciones', 'like', '%autocorregido%')
                  ->orWhere('observaciones', 'like', '%profesor registró entrada tarde%');
            })
            ->get();

        $this->info("Se encontraron {$registrosAutocorregidos->count()} registros autocorregidos para procesar.");

        if ($registrosAutocorregidos->isEmpty()) {
            $this->info('No hay registros para migrar.');
            return 0;
        }

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizarán cambios reales.');
        }

        // Manejar backup (solo si no es dry-run)
        if (!$dryRun) {
            $hacerBackup = $this->determinarBackup();
            
            if ($hacerBackup) {
                $this->crearBackup($registrosAutocorregidos);
            }
        }

        $migrados = 0;
        $eliminados = 0;
        $errores = 0;

        // Agrupar por asignatura + espacio + fecha para procesar juntos
        $agrupados = $registrosAutocorregidos->groupBy(function($r) {
            return $r->id_asignatura . '-' . $r->id_espacio . '-' . $r->fecha_clase->format('Y-m-d');
        });

        $this->info("Procesando {$agrupados->count()} clases únicas...");
        $this->output->progressStart($agrupados->count());

        foreach ($agrupados as $key => $registrosGrupo) {
            try {
                $primerRegistro = $registrosGrupo->first();
                
                // Buscar TODOS los registros relacionados (incluyendo los "no_realizada")
                $todosRegistros = ClaseNoRealizada::where('id_asignatura', $primerRegistro->id_asignatura)
                    ->where('id_espacio', $primerRegistro->id_espacio)
                    ->where('fecha_clase', $primerRegistro->fecha_clase)
                    ->get();

                $nombreAsignatura = $primerRegistro->asignatura->nombre_asignatura ?? 'N/A';
                $fechaClase = $primerRegistro->fecha_clase->format('d/m/Y');
                $totalRegistros = $todosRegistros->count();

                $this->newLine();
                $this->line("  📋 Clase: {$nombreAsignatura} - Fecha: {$fechaClase}");
                $this->line("     Registros encontrados: {$totalRegistros} (no_realizada + justificado)");

                // Buscar la planificación correspondiente
                $planificacion = Planificacion_Asignatura::where('id_asignatura', $primerRegistro->id_asignatura)
                    ->where('id_espacio', $primerRegistro->id_espacio)
                    ->first();

                // Intentar extraer hora de llegada de la reserva
                $horaLlegada = null;
                $reserva = DB::table('reservas')
                    ->where('id_espacio', $primerRegistro->id_espacio)
                    ->where('fecha_reserva', $primerRegistro->fecha_clase)
                    ->whereNotNull('hora')
                    ->first();

                if ($reserva) {
                    $horaLlegada = $reserva->hora;
                }

                // Obtener hora programada del módulo
                $horaProgramada = null;
                if ($primerRegistro->modulo) {
                    $horaProgramada = $primerRegistro->modulo->hora_inicio;
                }

                // Calcular minutos de atraso
                $minutosAtraso = 0;
                if ($horaLlegada && $horaProgramada) {
                    $minutosAtraso = Carbon::parse($horaProgramada)->diffInMinutes(Carbon::parse($horaLlegada));
                }

                if (!$dryRun) {
                    // Verificar si ya existe en profesor_atrasos
                    $existeAtraso = DB::table('profesor_atrasos')
                        ->where('id_asignatura', $primerRegistro->id_asignatura)
                        ->where('id_espacio', $primerRegistro->id_espacio)
                        ->where('fecha', $primerRegistro->fecha_clase)
                        ->exists();

                    if (!$existeAtraso) {
                        // Insertar en profesor_atrasos (solo una vez por clase)
                        DB::table('profesor_atrasos')->insert([
                            'id_planificacion' => $planificacion ? $planificacion->id : 0,
                            'id_asignatura' => $primerRegistro->id_asignatura,
                            'id_espacio' => $primerRegistro->id_espacio,
                            'id_modulo' => $primerRegistro->id_modulo,
                            'run_profesor' => $primerRegistro->run_profesor,
                            'fecha' => $primerRegistro->fecha_clase,
                            'hora_programada' => $horaProgramada,
                            'hora_llegada' => $horaLlegada,
                            'minutos_atraso' => $minutosAtraso,
                            'periodo' => $primerRegistro->periodo,
                            'observaciones' => 'Migrado desde clases_no_realizadas - Profesor llegó tarde pero realizó la clase',
                            'justificado' => false,
                            'justificacion' => null,
                            'created_at' => $primerRegistro->created_at ?? now(),
                            'updated_at' => now(),
                        ]);
                        $migrados++;
                        $this->line("     ✅ Atraso registrado ({$minutosAtraso} min)");
                    } else {
                        $this->line("     ⏭️  Ya existe atraso, solo eliminando registros");
                    }

                    // Eliminar TODOS los registros relacionados de clases_no_realizadas
                    foreach ($todosRegistros as $reg) {
                        $reg->delete();
                        $eliminados++;
                    }
                    $this->line("     🗑️  Eliminados {$todosRegistros->count()} registros de clases_no_realizadas");
                } else {
                    $this->line("     [DRY-RUN] Se crearía atraso y eliminarían {$todosRegistros->count()} registros");
                }

            } catch (\Exception $e) {
                $errores++;
                Log::error("Error migrando clase $key: " . $e->getMessage());
                $this->error("Error en clase $key: " . $e->getMessage());
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine(2);
        $this->info("═══════════════════════════════════════");
        $this->info("✅ Atrasos creados: $migrados");
        $this->info("🗑️  Registros eliminados de clases_no_realizadas: $eliminados");
        
        if ($errores > 0) {
            $this->error("❌ Errores: $errores");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Este fue un DRY-RUN. Ejecute sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }

    /**
     * Determinar si se debe hacer backup basado en opciones o pregunta interactiva
     */
    private function determinarBackup(): bool
    {
        // Si se especificó --backup, hacer backup
        if ($this->option('backup')) {
            return true;
        }

        // Si se especificó --no-backup, no hacer backup
        if ($this->option('no-backup')) {
            return false;
        }

        // Si no se especificó ninguna opción, preguntar interactivamente
        return $this->confirm('¿Desea crear un backup de los registros antes de migrar?', true);
    }

    /**
     * Crear backup de los registros a migrar en formato JSON
     */
    private function crearBackup($registrosAutocorregidos): void
    {
        $this->info('📦 Creando backup de registros...');

        // Buscar todos los registros relacionados (incluyendo los no_realizada)
        $todosRegistros = collect();
        
        $agrupados = $registrosAutocorregidos->groupBy(function($r) {
            return $r->id_asignatura . '-' . $r->id_espacio . '-' . $r->fecha_clase->format('Y-m-d');
        });

        foreach ($agrupados as $key => $grupo) {
            $primerRegistro = $grupo->first();
            $relacionados = ClaseNoRealizada::where('id_asignatura', $primerRegistro->id_asignatura)
                ->where('id_espacio', $primerRegistro->id_espacio)
                ->where('fecha_clase', $primerRegistro->fecha_clase)
                ->get();
            
            $todosRegistros = $todosRegistros->merge($relacionados);
        }

        // Preparar datos para backup
        $backupData = [
            'fecha_backup' => now()->toIso8601String(),
            'total_registros' => $todosRegistros->count(),
            'descripcion' => 'Backup antes de migrar registros autocorregidos a tabla profesor_atrasos',
            'registros' => $todosRegistros->map(function($r) {
                return [
                    'id' => $r->id,
                    'id_asignatura' => $r->id_asignatura,
                    'id_espacio' => $r->id_espacio,
                    'id_modulo' => $r->id_modulo,
                    'run_profesor' => $r->run_profesor,
                    'fecha_clase' => $r->fecha_clase->format('Y-m-d'),
                    'periodo' => $r->periodo,
                    'motivo' => $r->motivo,
                    'observaciones' => $r->observaciones,
                    'estado' => $r->estado,
                    'hora_deteccion' => $r->hora_deteccion ? $r->hora_deteccion->toIso8601String() : null,
                    'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
                    'updated_at' => $r->updated_at ? $r->updated_at->toIso8601String() : null,
                ];
            })->toArray(),
        ];

        // Generar nombre de archivo con timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backups/clases_no_realizadas_backup_{$timestamp}.json";

        // Guardar en storage
        Storage::put($filename, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $rutaCompleta = storage_path("app/{$filename}");
        $this->info("✅ Backup creado: {$rutaCompleta}");
        $this->info("   Total registros respaldados: {$todosRegistros->count()}");
        $this->newLine();
    }
}
