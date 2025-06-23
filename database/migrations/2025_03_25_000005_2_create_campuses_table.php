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
<<<<<<<< HEAD:database/migrations/2025_03_25_000005_2_create_campuses_table.php
        Schema::create('campuses', function (Blueprint $table) {
            $table->string('id_campus', 20)->primary();
            $table->string('nombre_campus', 100);
            $table->string('id_sede', 20); 
            $table->foreign('id_sede')->references('id_sede')->on('sedes')->onDelete('cascade');
========
        Schema::create('horarios', function (Blueprint $table) {
            $table->string('id_horario',20)->primary(); 
            $table->string('nombre');  
            $table->string('id_espacio'); 
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');

            $table->string('id_modulo');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulos')->onDelete('cascade');
           
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('seccions')->onDelete('cascade');
            
>>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841:database/migrations/2025_04_31_000014_create_horarios_table.php
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
