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
        Schema::create('student_bans', function (Blueprint $table) {
            $table->id();
            $table->string('run'); // RUN del alumno/solicitante baneado
            $table->text('reason'); // Razón del baneo
            $table->timestamp('banned_until'); // Hasta cuándo está baneado
            $table->timestamps();
            
            // Índice para búsquedas rápidas por RUN
            $table->index('run');
            $table->index('banned_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_bans');
    }
};
