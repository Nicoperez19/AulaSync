<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistema de reporte de fallas e incidencias en espacios.
     *
     * - incidencias_espacios: una fila por falla/incidencia (qué, dónde, quién, cuándo, estado actual).
     * - incidencias_espacios_eventos: bitácora inmutable con cada cambio (trazabilidad completa).
     *
     * Una incidencia con `bloquea_uso = true` es la razón de que un espacio esté en "Mantenimiento".
     */
    public function up(): void
    {
        if (! Schema::connection('tenant')->hasTable('incidencias_espacios')) {
            Schema::connection('tenant')->create('incidencias_espacios', function (Blueprint $table) {
                $table->id();

                $table->string('id_espacio')->index();

                // proyector, climatizacion, mobiliario, insumos, iluminacion, electricidad,
                // conectividad, computadores, infraestructura, limpieza, otro
                $table->string('categoria', 40)->index();
                $table->string('severidad', 10)->default('media')->index(); // baja|media|alta|critica
                $table->text('descripcion');

                // Si es true, el espacio queda en "Mantenimiento" mientras la incidencia siga abierta.
                $table->boolean('bloquea_uso')->default(false)->index();
                $table->string('estado_espacio_previo', 20)->nullable();

                // abierta | en_proceso | resuelta | descartada
                $table->string('estado', 15)->default('abierta')->index();

                // Quién reportó (run del usuario; el nombre se guarda por si el usuario cambia)
                $table->string('reportado_por_run', 20)->nullable()->index();
                $table->string('reportado_por_nombre')->nullable();
                $table->timestamp('reportado_en')->useCurrent()->index();

                // Quién la atiende / resolvió
                $table->string('asignado_a')->nullable();
                $table->text('resolucion')->nullable();
                $table->string('resuelto_por_run', 20)->nullable();
                $table->string('resuelto_por_nombre')->nullable();
                $table->timestamp('resuelto_en')->nullable();

                $table->timestamps();

                $table->index(['id_espacio', 'estado']);
                $table->index(['estado', 'bloquea_uso']);
            });
        }

        if (! Schema::connection('tenant')->hasTable('incidencias_espacios_eventos')) {
            Schema::connection('tenant')->create('incidencias_espacios_eventos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('incidencia_id')->index();

                // creada | estado_cambiado | comentario | espacio_bloqueado | espacio_liberado
                $table->string('tipo', 30);
                $table->string('estado_anterior', 15)->nullable();
                $table->string('estado_nuevo', 15)->nullable();
                $table->text('detalle')->nullable();

                $table->string('usuario_run', 20)->nullable();
                $table->string('usuario_nombre')->nullable();

                $table->timestamps();

                $table->foreign('incidencia_id')
                    ->references('id')
                    ->on('incidencias_espacios')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('incidencias_espacios_eventos');
        Schema::connection('tenant')->dropIfExists('incidencias_espacios');
    }
};
