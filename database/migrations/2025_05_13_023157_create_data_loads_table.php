<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataLoadsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_loads', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('tipo_carga');
            $table->integer('registros_cargados')->default(0);
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'error'])->default('pendiente');
            $table->text('error_mensaje')->nullable();
            $table->unsignedBigInteger('user_run');
            $table->foreign('user_run')->references('run')->on('users')->onDelete('cascade');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_loads');
    }
};
