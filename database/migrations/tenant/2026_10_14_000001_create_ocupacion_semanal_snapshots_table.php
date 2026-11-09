<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('ocupacion_semanal_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->date('semana_inicio')->index();
            $table->date('semana_fin');
            $table->unsignedSmallInteger('anio')->index();
            $table->unsignedSmallInteger('semana_iso')->index();
            $table->string('periodo', 20)->index();
            $table->string('tipo_filtro', 30)->default('todos')->index(); // todos, laboratorios, salas_clases, salas_estudio
            $table->unsignedInteger('total_espacios')->default(0);
            $table->json('datos'); // Matriz de ocupación [dia][modulo] => porcentaje
            $table->json('resumen')->nullable(); // promedios, pico, etc.
            $table->string('origen', 20)->default('automatico'); // automatico, manual, reconstruido
            $table->unsignedBigInteger('generado_por')->nullable()->index();
            $table->timestamps();

            $table->unique(['semana_inicio', 'tipo_filtro'], 'uniq_ocupacion_semana_tipo');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('ocupacion_semanal_snapshots');
    }
};
