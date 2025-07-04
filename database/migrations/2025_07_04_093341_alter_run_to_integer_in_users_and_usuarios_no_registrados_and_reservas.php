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
        // Cambiar run a integer en users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('run')->change();
        });
        // Cambiar run a integer en usuarios_no_registrados
        Schema::table('usuarios_no_registrados', function (Blueprint $table) {
            $table->unsignedBigInteger('run')->change();
        });
        // Cambiar run a integer en reservas
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('run')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver a string si fuera necesario (ajustar según el tipo anterior)
        Schema::table('users', function (Blueprint $table) {
            $table->string('run')->change();
        });
        Schema::table('usuarios_no_registrados', function (Blueprint $table) {
            $table->string('run')->change();
        });
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('run')->change();
        });
    }
};
