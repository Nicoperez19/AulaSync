<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uso_espacios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('llave_id')->constrained('llaves')->onDelete('cascade');

            $table->unsignedBigInteger('run');
            $table->foreign('run')->references('run')->on('users')->onDelete('cascade');

            $table->string('id_espacio');
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');
            
            $table->timestamp('inicio_uso')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('fin_uso')->nullable();
            $table->enum('estado', ['Activa', 'Finalizada'])->default('Activa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uso_espacios');
    }
};
