<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar el ENUM del campo estado en la tabla espacios
        DB::statement("ALTER TABLE espacios MODIFY COLUMN estado ENUM('Disponible', 'Ocupado', 'Reservado', 'Mantenimiento') NOT NULL DEFAULT 'Disponible'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el cambio - remover 'Mantenimiento' del ENUM
        // Primero, cambiar cualquier espacio en mantenimiento a disponible
        DB::table('espacios')->where('estado', 'Mantenimiento')->update(['estado' => 'Disponible']);
        
        // Luego, actualizar el ENUM para remover 'Mantenimiento'
        DB::statement("ALTER TABLE espacios MODIFY COLUMN estado ENUM('Disponible', 'Ocupado', 'Reservado') NOT NULL DEFAULT 'Disponible'");
    }
};
