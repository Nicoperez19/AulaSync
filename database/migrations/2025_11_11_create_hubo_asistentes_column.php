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
        Schema::table('reservas', function (Blueprint $table) {
            // Agregar campo para registrar si hubo asistentes en la clase
            // NULL = no aplica (no es clase o no se devolvió en primer módulo)
            // true = sí hubo asistentes
            // false = no hubo asistentes
            $table->boolean('hubo_asistentes')->nullable()->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('hubo_asistentes');
        });
    }
};
