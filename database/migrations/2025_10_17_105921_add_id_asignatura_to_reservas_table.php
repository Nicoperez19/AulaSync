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
            $table->string('id_asignatura', 20)->nullable()->after('id_espacio');
            $table->foreign('id_asignatura')->references('id_asignatura')->on('asignaturas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['id_asignatura']);
            $table->dropColumn('id_asignatura');
        });
    }
};
