<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campos para soportar asistencia de alumnos mediante escáner QR:
     * - hora_salida: hora de salida del alumno (para reservas espontáneas)
     * - tipo_entrada: indica si la entrada es por clase planificada o espontánea
     * - estado: estado de la asistencia (presente, finalizado)
     */
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Hora de salida del alumno (nullable, solo se usa en reservas espontáneas)
            if (!Schema::hasColumn('asistencias', 'hora_salida')) {
                $table->time('hora_salida')->nullable()->after('hora_llegada');
            }
            
            // Tipo de entrada: planificada (clase con reserva) o espontanea (sin reserva previa)
            if (!Schema::hasColumn('asistencias', 'tipo_entrada')) {
                $table->enum('tipo_entrada', ['planificada', 'espontanea'])->default('planificada')->after('hora_salida');
            }
            
            // Estado de la asistencia: presente (aún en sala) o finalizado (salió)
            if (!Schema::hasColumn('asistencias', 'estado')) {
                $table->enum('estado', ['presente', 'finalizado'])->default('presente')->after('tipo_entrada');
            }

            // ID del espacio (para consultas directas sin pasar por reserva)
            if (!Schema::hasColumn('asistencias', 'id_espacio')) {
                $table->string('id_espacio', 20)->nullable()->after('id_reserva');
                
                $table->foreign('id_espacio')
                      ->references('id_espacio')
                      ->on('espacios')
                      ->onDelete('set null');
                      
                $table->index('id_espacio');
            }

            // Índices para mejorar consultas de asistencia QR
            $table->index(['rut_asistente', 'estado'], 'idx_alumno_estado');
            $table->index(['id_espacio', 'estado', 'created_at'], 'idx_espacio_estado_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex('idx_alumno_estado');
            $table->dropIndex('idx_espacio_estado_fecha');
            
            // Eliminar foreign key de espacio
            if (Schema::hasColumn('asistencias', 'id_espacio')) {
                $table->dropForeign(['id_espacio']);
                $table->dropIndex(['id_espacio']);
                $table->dropColumn('id_espacio');
            }
            
            // Eliminar columnas
            if (Schema::hasColumn('asistencias', 'hora_salida')) {
                $table->dropColumn('hora_salida');
            }
            if (Schema::hasColumn('asistencias', 'tipo_entrada')) {
                $table->dropColumn('tipo_entrada');
            }
            if (Schema::hasColumn('asistencias', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
