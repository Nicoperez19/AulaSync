<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'run' => '12345678-9',
            'name' => 'Admin',
            'email' => 'admin@ucsc.cl',
            'password' => bcrypt('password'),
        ])->assignRole('admin');

        User::create([
            'run' => '19812524-5',
            'name' => 'Nicolas',
            'email' => 'nperez@ucsc.cl',
            'password' => bcrypt('password'),
        ])->assignRole(roles: 'user');
    }
}
