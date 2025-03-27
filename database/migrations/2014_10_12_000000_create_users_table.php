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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('run');
            $table->string('contrasena');
            $table->string('nombre');
            $table->string('correo');
            $table->integer('celular');
            $table->string('direccion');
            $table->date('fecha_nacimiento');
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('anio_ingreso')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
