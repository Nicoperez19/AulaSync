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
        Schema::table('planificacion_asignaturas', function (Blueprint $table) {
            $table->integer('inscritos')->nullable()->after('id_espacio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planificacion_asignaturas', function (Blueprint $table) {
            $table->dropColumn('inscritos');
        });
    }
};
