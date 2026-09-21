<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clases_no_realizadas', function (Blueprint $table) {
            // Eliminar la clave única anterior que no incluía al profesor
            $table->dropUnique('unique_clase_no_realizada');

            // Crear la nueva clave única incluyendo run_profesor para permitir colaboradores / co-docentes
            $table->unique(
                ['id_asignatura', 'id_espacio', 'id_modulo', 'fecha_clase', 'run_profesor'],
                'unique_clase_no_realizada_profesor'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clases_no_realizadas', function (Blueprint $table) {
            $table->dropUnique('unique_clase_no_realizada_profesor');
            $table->unique(
                ['id_asignatura', 'id_espacio', 'id_modulo', 'fecha_clase'],
                'unique_clase_no_realizada'
            );
        });
    }
};
