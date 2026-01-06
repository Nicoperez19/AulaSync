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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_usuario'); // Usuario que recibe la notificación
            $table->string('tipo'); // tipo de notificación: 'clase_no_realizada', 'clase_reagendada', etc.
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url')->nullable(); // URL a la que redirige al hacer clic
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_lectura')->nullable();
            $table->json('datos_adicionales')->nullable(); // Para almacenar info adicional (id_asignatura, run_profesor, etc.)
            $table->timestamps();

            // Índices
            $table->index(['run_usuario', 'leida']);
            $table->index('tipo');
            $table->index('created_at');

            // Foreign key
            // $table->foreign('run_usuario')->references('run')->on('users')->onDelete('cascade'); // FK a tabla central
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
