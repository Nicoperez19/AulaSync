<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema de fallas e incidencias en espacios (BD de cada sede).
 *
 * Lo usan tanto la migración como IncidenciaEspacioService::asegurarTablas(), de modo que
 * cualquier sede (Concepción, Chillán, Cañete, Los Ángeles, Talcahuano) tenga las tablas
 * aunque no se haya ejecutado `php artisan tenants:migrate` en ella. Es idempotente.
 */
class IncidenciasSchema
{
    /** Evita repetir hasTable() en la misma petición, por BD de sede. */
    protected static array $verificadas = [];

    public static function asegurar(): void
    {
        $db = (string) config('database.connections.tenant.database');

        if (isset(static::$verificadas[$db])) {
            return;
        }

        static::crear();

        static::$verificadas[$db] = true;
    }

    public static function crear(): void
    {
        $schema = Schema::connection('tenant');

        if (! $schema->hasTable('incidencias_espacios')) {
            $schema->create('incidencias_espacios', function (Blueprint $table) {
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

        if (! $schema->hasTable('incidencias_espacios_eventos')) {
            $schema->create('incidencias_espacios_eventos', function (Blueprint $table) {
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

        // La tabla del Centro de Notificaciones también debe existir en la sede.
        if (! $schema->hasTable('notificaciones')) {
            $schema->create('notificaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_usuario');
                $table->string('tipo');
                $table->string('titulo');
                $table->text('mensaje');
                $table->string('url')->nullable();
                $table->boolean('leida')->default(false);
                $table->timestamp('fecha_lectura')->nullable();
                $table->json('datos_adicionales')->nullable();
                $table->timestamps();

                $table->index(['run_usuario', 'leida']);
                $table->index('tipo');
                $table->index('created_at');
            });
        }
    }
}
