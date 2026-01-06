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
        Schema::create('vetos_sala_estudio', function (Blueprint $table) {
            $table->id();
            $table->string('run_vetado', 20);
            $table->enum('tipo_veto', ['individual', 'grupal'])->default('individual');
            $table->string('id_reserva_origen')->nullable(); // Cambiar a string para compatibilidad
            $table->text('observacion');
            $table->enum('estado', ['activo', 'liberado'])->default('activo');
            $table->string('vetado_por')->nullable(); // Usuario que aplicó el veto
            $table->string('liberado_por')->nullable(); // Usuario que liberó el veto
            $table->timestamp('fecha_veto');
            $table->timestamp('fecha_liberacion')->nullable();
            $table->timestamps();

            $table->index('run_vetado');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vetos_sala_estudio');
    }
};
