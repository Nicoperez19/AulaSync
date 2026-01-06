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
        Schema::create('profesors', function (Blueprint $table) {
            $table->unsignedBigInteger('run_profesor')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('celular')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->year('anio_ingreso')->nullable();
            $table->enum('tipo_profesor', ['Profesor Colaborador', 'Profesor Responsable', 'Ayudante'])->default('Profesor Colaborador');
            $table->string('id_universidad', 20)->nullable();
            $table->string('id_facultad', 20)->nullable();
            $table->string('id_carrera', 20)->nullable();
            $table->string('id_area_academica', 20)->nullable();
            $table->timestamps();

            // Foreign keys comentadas porque estas tablas están en la BD central
            // Las validaciones deben hacerse a nivel de aplicación
            // $table->foreign('id_universidad')->nullable()->references('id_universidad')->on('universidades')->onDelete('set null'); // FK a tabla central
            // $table->foreign('id_facultad')->nullable()->references('id_facultad')->on('facultades')->onDelete('set null'); // FK a tabla central
            // $table->foreign('id_carrera')->nullable()->references('id_carrera')->on('carreras')->onDelete('set null'); // FK a tabla central
            // $table->foreign('id_area_academica')->nullable()->references('id_area_academica')->on('area_academicas')->onDelete('set null'); // FK a tabla central
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesors');
    }
};
