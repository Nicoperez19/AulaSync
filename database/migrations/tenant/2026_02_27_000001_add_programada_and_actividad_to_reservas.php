<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ampliar el estado de reservas para incluir 'programada'
     * y agregar campos para actividades de solicitantes externos.
     */
    public function up(): void
    {
        // Modificar el enum de estado para agregar 'programada'
        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('activa', 'finalizada', 'programada') DEFAULT 'activa'");

        // Agregar campos para actividades de solicitantes externos
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('nombre_actividad', 255)->nullable()->after('observaciones');
            $table->text('descripcion_actividad')->nullable()->after('nombre_actividad');
            $table->unsignedSmallInteger('modulo_inicio')->nullable()->after('descripcion_actividad');
            $table->unsignedSmallInteger('modulo_fin')->nullable()->after('modulo_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['nombre_actividad', 'descripcion_actividad', 'modulo_inicio', 'modulo_fin']);
        });

        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('activa', 'finalizada') DEFAULT 'activa'");
    }
};
