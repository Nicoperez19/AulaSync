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
        // Agregar sede_id a la tabla profesors
        Schema::table('profesors', function (Blueprint $table) {
            $table->string('sede_id', 20)->nullable()->after('id_area_academica');
            $table->foreign('sede_id')->references('id_sede')->on('sedes')->onDelete('set null');
        });

        // Agregar sede_id a la tabla espacios (a través de piso->facultad->sede)
        // Los espacios ya tienen relación indirecta a través de piso
        
        // Agregar sede_id a la tabla mapas (a través de piso->facultad->sede)
        // Los mapas ya tienen relación indirecta a través de piso

        // Agregar sede_id a la tabla pisos (a través de facultad->sede)
        // Los pisos ya tienen relación indirecta a través de facultad
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesors', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropColumn('sede_id');
        });
    }
};
