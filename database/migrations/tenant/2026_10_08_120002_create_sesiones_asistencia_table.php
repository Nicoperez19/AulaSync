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
        Schema::create('sesiones_asistencia', function (Blueprint $table) {
            $table->id();
            $table->string('id_reserva', 50)->nullable();
            $table->string('id_espacio', 20)->nullable();
            $table->string('id_asignatura', 20)->nullable();
            $table->unsignedBigInteger('id_profesor_colaborador')->nullable();
            $table->date('fecha');
            $table->string('id_modulo', 20)->nullable();
            $table->string('run_profesor', 20);
            $table->enum('rol_docente', ['titular', 'reemplazo', 'colaborador'])->default('titular');
            $table->text('actividad')->nullable();
            $table->string('registrado_por_run', 20)->nullable();
            $table->boolean('es_prueba')->default(false);
            $table->timestamps();

            $table->foreign('id_reserva')->references('id_reserva')->on('reservas')->onDelete('set null');
            $table->foreign('id_asignatura')->references('id_asignatura')->on('asignaturas')->onDelete('set null');
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('set null');
            $table->foreign('id_profesor_colaborador')->references('id')->on('profesores_colaboradores')->onDelete('set null');

            $table->index('fecha');
            $table->index('run_profesor');
            $table->index('id_reserva');
            $table->index(['id_asignatura', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_asistencia');
    }
};
