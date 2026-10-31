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
        // La columna ya existe en algunas bases (creada antes de registrar la migración)
        if (Schema::hasColumn('reservas', 'es_reemplazo')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->boolean('es_reemplazo')->default(false)->after('run_profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('reservas', 'es_reemplazo')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('es_reemplazo');
        });
    }
};
