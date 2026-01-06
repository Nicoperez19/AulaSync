<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Corrige los tipos de datos en la tabla profesor_atrasos:
     * - id_espacio: de BIGINT a VARCHAR (los espacios son strings como 'TH-L04')
     * - id_modulo: aumentar tamaño para soportar múltiples módulos
     * - hora_programada: permitir null
     * - hora_llegada: permitir null
     */
    public function up(): void
    {
        // Primero eliminar el índice que usa id_espacio
        Schema::table('profesor_atrasos', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'id_espacio']);
        });

        // Modificar las columnas
        Schema::table('profesor_atrasos', function (Blueprint $table) {
            // Cambiar id_espacio a string
            $table->string('id_espacio', 50)->change();
            
            // Aumentar tamaño de id_modulo para soportar múltiples módulos
            $table->string('id_modulo', 100)->change();
            
            // Hacer nullable hora_programada y hora_llegada
            $table->time('hora_programada')->nullable()->change();
            $table->time('hora_llegada')->nullable()->change();
        });

        // Recrear el índice
        Schema::table('profesor_atrasos', function (Blueprint $table) {
            $table->index(['fecha', 'id_espacio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesor_atrasos', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'id_espacio']);
        });

        Schema::table('profesor_atrasos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_espacio')->change();
            $table->string('id_modulo', 20)->change();
            $table->time('hora_programada')->nullable(false)->change();
            $table->time('hora_llegada')->nullable(false)->change();
        });

        Schema::table('profesor_atrasos', function (Blueprint $table) {
            $table->index(['fecha', 'id_espacio']);
        });
    }
};
