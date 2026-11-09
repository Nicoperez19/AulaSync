<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('auditoria_clases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_nombre', 150);
            $table->string('user_rol', 50)->nullable();
            $table->string('accion', 50)->index(); // justificar, justificar_masivo, registrar, registrar_masivo, cambiar_estado, reagendar, marcar_recuperada, eliminar, auto_feriado, auto_correccion
            $table->string('entidad_tipo', 80)->default('App\\Models\\ClaseNoRealizada');
            $table->unsignedBigInteger('entidad_id')->nullable()->index();
            $table->string('run_profesor', 20)->nullable()->index();
            $table->string('id_asignatura', 50)->nullable()->index();
            $table->string('id_espacio', 50)->nullable()->index();
            $table->string('id_modulo', 20)->nullable()->index();
            $table->date('fecha_clase')->nullable()->index();
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->uuid('lote_uuid')->nullable()->index();
            $table->string('origen', 20)->default('web'); // web, sistema, comando
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['run_profesor', 'fecha_clase']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('auditoria_clases');
    }
};
