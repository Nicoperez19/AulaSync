<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CrearUsuariosProfesores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crear:usuarios-profesores {--dry-run : No crea usuarios, solo muestra qué pasaría}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea usuarios en la tabla users para cada profesor en tabla profesors. Password = run sin dígito verificador.
    
    Uso:
    - php artisan crear:usuarios-profesores --dry-run  (para simular sin crear)
    - php artisan crear:usuarios-profesores            (para crear realmente)
    
    Requisitos:
    1. Debe haber profesores cargados en la tabla "profesors" (mediante carga masiva)
    2. Los profesores deben tener RUT válido en el campo "run_profesor"
    
    El comando:
    - Username: RUT completo del profesor (ej: 12345678-9)
    - Password: RUT sin dígito verificador (ej: 12345678)
    - Omite profesores que ya tienen usuario creado
    - Registra errores en logs/laravel.log';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Iniciando creación de usuarios para profesores...');

        $profesores = Profesor::all();
        $this->info('Profesores encontrados: ' . $profesores->count());

        $created = 0;
        $skipped = 0;

        foreach ($profesores as $prof) {
            $runProfesor = (string) $prof->run_profesor;

            if (empty($runProfesor)) {
                $this->warn("Profesor {$prof->name} sin RUN - se omite.");
                $skipped++;
                continue;
            }

            // Normalizar: quitar todo lo que no sea dígito o letra (por si hay guiones o puntos)
            $runLimpio = preg_replace('/[^0-9kK]/', '', $runProfesor);

            // Obtener sin dígito verificador: asumo que el último caracter es el DV
            $runSinDV = strlen($runLimpio) > 1 ? substr($runLimpio, 0, -1) : $runLimpio;

            // Verificar existencia de usuario
            $existe = User::where('run', $runProfesor)
                ->orWhere('run', $runLimpio)
                ->orWhere('run', $runSinDV)
                ->exists();

            if ($existe) {
                $this->line("Ya existe usuario para RUN: {$runProfesor} ({$prof->name}) - omitido.");
                $skipped++;
                continue;
            }

            $passwordPlain = $runSinDV;

            $this->line("Crear usuario -> run: {$runProfesor}, email: {$prof->email}, pass (sin DV): {$passwordPlain}");

            if (!$dryRun) {
                try {
                    User::create([
                        'run' => $runProfesor,
                        'name' => $prof->name,
                        'email' => $prof->email ?? "{$runSinDV}@no-email.local",
                        'password' => Hash::make($passwordPlain),
                        'celular' => $prof->celular ?? null,
                        'direccion' => $prof->direccion ?? null,
                        'fecha_nacimiento' => $prof->fecha_nacimiento ?? null,
                        'anio_ingreso' => $prof->anio_ingreso ?? null,
                        'id_universidad' => $prof->id_universidad ?? null,
                        'id_facultad' => $prof->id_facultad ?? null,
                        'id_carrera' => $prof->id_carrera ?? null,
                        'id_area_academica' => $prof->id_area_academica ?? null,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $this->error("Error creando usuario para {$runProfesor}: " . $e->getMessage());
                    Log::error('crear:usuarios-profesores error', ['run' => $runProfesor, 'error' => $e->getMessage()]);
                }
            }
        }

        $this->info("Proceso finalizado. Creados: {$created}. Omitidos: {$skipped}.");

        return 0;
    }
}
