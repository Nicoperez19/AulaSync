<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        $this->call(RoleSeeder::class);   
        $this->call(UserSeeder::class);   
        $this->call(AdministracionChileSeeder::class);   
        // $this->call(class: UniversidadSeeder::class); 
        // $this->call(class: FacultadSeeder::class);
        // $this->call(class: CarreraSeeder::class); 
        // // $this->call(class: AreaAcademicaSeeder::class); 
        // $this->call(class: PisoSeeder::class); 
        // $this->call(class: EspacioSeeder::class); 
        // $this->call(class: ReservasSeeder::class); 
        // $this->call(class: AsignaturasSeeder::class); 
=======
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AdministracionChileSeeder::class);
        $this->call(class: UniversidadSeeder::class);
        $this->call(class: SedeSeeder::class);
        $this->call(class: CampusSeeder::class);
        $this->call(class: FacultadSeeder::class);
        $this->call(class: AreaAcademicaSeeder::class);
        $this->call(class: PisoSeeder::class);
        $this->call(class: CarreraSeeder::class);
        $this->call(class: EspacioSeeder::class);



        $this->call(class: ModulosSeeder::class);


        // $this->call(class: ReservasSeeder::class); 
>>>>>>> Nperez
    }
}
