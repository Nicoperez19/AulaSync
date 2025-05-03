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
        Schema::create('horarios', function (Blueprint $table) {
            $table->string('id_horario',20)->primary(); 
            $table->string('nombre');  
            $table->string('id_espacio'); 
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');

            $table->string('id_modulo');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulos')->onDelete('cascade');
           
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('seccions')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
