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
        // Usamos una sentencia SQL pura para asegurar la compatibilidad con el tipo ENUM
        DB::statement("ALTER TABLE profesors MODIFY COLUMN tipo_profesor ENUM('Profesor Colaborador', 'Profesor Responsable', 'Ayudante', 'Invitado', 'Colaborador') DEFAULT 'Profesor Colaborador'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE profesors MODIFY COLUMN tipo_profesor ENUM('Profesor Colaborador', 'Profesor Responsable', 'Ayudante') DEFAULT 'Profesor Colaborador'");
    }
};
