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
        Schema::create('licencias_profesores', function (Blueprint $table) {
            $table->id('id_licencia');
            $table->unsignedBigInteger('run_profesor');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['activa', 'finalizada', 'cancelada'])->default('activa');
            $table->boolean('genera_recuperacion')->default(true); // Si genera clases a recuperar
            $table->unsignedBigInteger('created_by')->nullable(); // Usuario que creó la licencia
            $table->timestamps();

            // Foreign keys
            $table->foreign('run_profesor')->references('run_profesor')->on('profesors')->onDelete('cascade');
            $table->foreign('created_by')->references('run')->on('users')->onDelete('set null');
            
            // Índices para mejorar consultas
            $table->index(['run_profesor', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licencias_profesores');
    }
};
