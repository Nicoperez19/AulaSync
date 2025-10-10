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
        Schema::create('tipos_correos_masivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Ej: "Informe Clases No Realizadas"
            $table->string('codigo')->unique(); // Ej: "informe_clases_no_realizadas"
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['sistema', 'custom'])->default('custom'); // Sistema = predefinido, Custom = creado por admin
            $table->enum('frecuencia', ['diario', 'semanal', 'mensual', 'manual'])->default('manual');
            $table->boolean('activo')->default(true);
            $table->json('configuracion')->nullable(); // Para guardar configuraciones adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_correos_masivos');
    }
};
