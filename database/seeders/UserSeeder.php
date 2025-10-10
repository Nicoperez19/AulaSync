<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profesor;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo crear usuarios para profesores, no los usuarios base
        // Los usuarios base deben ser creados manualmente o en otro seeder
        
        // Crear usuarios automáticamente para todos los profesores
        $profesores = Profesor::all();
        
        foreach ($profesores as $profesor) {
            // Verificar si ya existe un usuario con ese RUN
            $existingUser = User::where('run', $profesor->run_profesor)->first();
            
            if (!$existingUser) {
                $newUser = User::create([
                    'run' => $profesor->run_profesor,
                    'name' => $profesor->name,
                    'email' => $profesor->email,
                    'password' => bcrypt($profesor->run_profesor), // Contraseña es el mismo RUN
                    'celular' => $profesor->celular,
                    'direccion' => $profesor->direccion,
                    'fecha_nacimiento' => $profesor->fecha_nacimiento,
                    'id_universidad' => $profesor->id_universidad,
                    'id_facultad' => $profesor->id_facultad,
                    'id_carrera' => $profesor->id_carrera,
                    'id_area_academica' => $profesor->id_area_academica,
                ]);
                
                // Asignar rol Profesor si existe
                if (\Spatie\Permission\Models\Role::where('name', 'Profesor')->exists()) {
                    $newUser->assignRole('Profesor');
                }
            }
        }
    }
}
