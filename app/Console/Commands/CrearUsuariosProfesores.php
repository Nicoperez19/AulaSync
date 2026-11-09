<?php

namespace App\Console\Commands;

use App\Models\Profesor;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CrearUsuariosProfesores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crear:usuarios-profesores 
                            {--sede= : ID de la sede a procesar (opcional)}
                            {--all : Procesar todas las sedes con tenants activos}
                            {--reset-passwords : Sobrescribir contraseña con el RUN}
                            {--dry-run : No realiza cambios, solo muestra qué se haría}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea o actualiza usuarios en la BD central para cada profesor del tenant, asignando id_sede, contraseña y rol Profesor.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $resetPasswords = $this->option('reset-passwords');
        $idSede = $this->option('sede');
        $all = $this->option('all');

        $this->info('🚀 Iniciando sincronización de usuarios para profesores...');

        // Asegurar que el rol Profesor existe en la BD central
        $roleProfesor = Role::firstOrCreate(['name' => 'Profesor', 'guard_name' => 'web']);

        $tenantsQuery = Tenant::where('is_active', true)->with('sede');
        if ($idSede) {
            $tenantsQuery->where('sede_id', $idSede);
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron sedes/tenants activos para procesar.');
            return 0;
        }

        $totalCreados = 0;
        $totalActualizados = 0;
        $totalOmitidos = 0;

        foreach ($tenants as $tenant) {
            $this->info("📍 Procesando Sede: {$tenant->sede?->nombre_sede} (Tenant ID: {$tenant->id}, Sede ID: {$tenant->sede_id})");

            $tenant->makeCurrent();

            try {
                $profesores = Profesor::all();
            } catch (\Exception $e) {
                $this->error("Error al consultar profesores en tenant {$tenant->name}: " . $e->getMessage());
                continue;
            }

            $this->line("   Profesores encontrados: " . $profesores->count());

            foreach ($profesores as $prof) {
                $runProfesor = trim((string)$prof->run_profesor);

                if (empty($runProfesor)) {
                    $totalOmitidos++;
                    continue;
                }

                // Normalizar RUN
                $runLimpio = preg_replace('/[^0-9kK]/', '', $runProfesor);
                $runSinDV = strlen($runLimpio) > 1 ? substr($runLimpio, 0, -1) : $runLimpio;
                $sedeIdActual = $tenant->sede_id;

                // Password de prueba es el mismo RUN
                $password = $runProfesor;

                $user = User::where('run', $runProfesor)
                    ->orWhere('run', $runLimpio)
                    ->first();

                if (!$user) {
                    $this->line("   [CREAR] Usuario para {$prof->name} (RUN: {$runProfesor}, Sede: {$sedeIdActual})");
                    if (!$dryRun) {
                        try {
                            $newUser = User::create([
                                'run' => $runProfesor,
                                'name' => $prof->name,
                                'email' => $prof->email ?? "{$runLimpio}@aulasync.local",
                                'password' => Hash::make($password),
                                'celular' => $prof->celular ?? null,
                                'direccion' => $prof->direccion ?? null,
                                'fecha_nacimiento' => $prof->fecha_nacimiento ?? null,
                                'id_universidad' => $prof->id_universidad ?? null,
                                'id_facultad' => $prof->id_facultad ?? null,
                                'id_carrera' => $prof->id_carrera ?? null,
                                'id_area_academica' => $prof->id_area_academica ?? null,
                                'id_sede' => $sedeIdActual,
                                'is_superuser' => false,
                            ]);

                            $newUser->assignRole($roleProfesor);
                            $totalCreados++;
                        } catch (\Exception $e) {
                            $this->error("   Error creando {$runProfesor}: " . $e->getMessage());
                            Log::error('Error creando usuario docente', ['run' => $runProfesor, 'error' => $e->getMessage()]);
                        }
                    } else {
                        $totalCreados++;
                    }
                } else {
                    // Actualizar si falta sede o rol
                    $cambios = [];
                    if (!$user->id_sede) {
                        $cambios['id_sede'] = $sedeIdActual;
                    }
                    if ($resetPasswords) {
                        $cambios['password'] = Hash::make($password);
                    }

                    if (!empty($cambios) || !$user->hasRole('Profesor')) {
                        $this->line("   [ACTUALIZAR] Usuario existente {$user->run} ({$user->name})");
                        if (!$dryRun) {
                            if (!empty($cambios)) {
                                $user->update($cambios);
                            }
                            if (!$user->hasRole('Profesor')) {
                                $user->assignRole($roleProfesor);
                            }
                        }
                        $totalActualizados++;
                    } else {
                        $totalOmitidos++;
                    }
                }
            }
        }

        $this->info("✨ Sincronización completada. Creados: {$totalCreados}, Actualizados: {$totalActualizados}, Omitidos/Sin cambios: {$totalOmitidos}.");
        return 0;
    }
}
