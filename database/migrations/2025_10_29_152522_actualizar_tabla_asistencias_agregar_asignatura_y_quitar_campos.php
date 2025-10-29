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
        Schema::table('asistencias', function (Blueprint $table) {
            // Agregar columna de asignatura si no existe
            if (!Schema::hasColumn('asistencias', 'id_asignatura')) {
                $table->string('id_asignatura', 20)->nullable()->after('id_reserva');
                
                // Agregar foreign key para asignatura
                $table->foreign('id_asignatura')
                      ->references('id_asignatura')
                      ->on('asignaturas')
                      ->onDelete('set null');
                
                // Agregar índice para id_asignatura
                $table->index('id_asignatura');
            }
            
            // Si existe contenido_visto, renombrar a observaciones
            if (Schema::hasColumn('asistencias', 'contenido_visto')) {
                $table->renameColumn('contenido_visto', 'observaciones');
            } else if (!Schema::hasColumn('asistencias', 'observaciones')) {
                // Si no existe ninguna de las dos, crear observaciones
                $table->text('observaciones')->nullable()->after('hora_llegada');
            }
            
            // Eliminar columna hora_termino si existe
            if (Schema::hasColumn('asistencias', 'hora_termino')) {
                $table->dropColumn('hora_termino');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Eliminar índice
            $table->dropIndex(['id_asignatura']);
            
            // Eliminar foreign key
            $table->dropForeign(['id_asignatura']);
            
            // Eliminar columna id_asignatura
            $table->dropColumn('id_asignatura');
            
            // Restaurar nombre de columna
            $table->renameColumn('observaciones', 'contenido_visto');
            
            // Restaurar columna hora_termino
            $table->time('hora_termino')->nullable()->after('hora_llegada');
        });
    }
};
