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
        Schema::create('profesores_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_profesor_colaborador');
            $table->string('id_asignatura')->nullable(); // Asignatura existente (opcional)
            $table->string('nombre_asignatura_temporal')->nullable(); // Nombre temporal si no hay asignatura
            $table->text('descripcion')->nullable();
            $table->integer('cantidad_inscritos')->unsigned()->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // Foreign keys
            $table->foreign('run_profesor_colaborador')
                ->references('run_profesor')
                ->on('profesors')
                ->onDelete('cascade');
            
            $table->foreign('id_asignatura')
                ->references('id_asignatura')
                ->on('asignaturas')
                ->onDelete('set null');

            // Índices para mejorar rendimiento
            $table->index(['fecha_inicio', 'fecha_termino']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesores_colaboradores');
    }
};
