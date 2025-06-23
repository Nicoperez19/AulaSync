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
        Schema::create('users', function (Blueprint $table) {
            
            $table->unsignedBigInteger('run')->primary();
            $table->string('password');
            $table->string('name');
            $table->string('email')->nullable();
<<<<<<< HEAD
            $table->integer('celular')->nullable();
=======
            $table->string('celular')->nullable();
>>>>>>> Nperez
            $table->string('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->year('anio_ingreso')->nullable();
            $table->string('tipo_profesor')->nullable();
            
            $table->timestamp('email_verified_at')->nullable();
            
            $table->string('id_universidad', 20)->nullable();
            $table->string('id_facultad', 20)->nullable();
            $table->string('id_carrera', 20)->nullable();
            $table->string('id_area_academica', 20)->nullable();

            $table->foreign('id_universidad')->nullable()->references('id_universidad')->on('universidades')->onDelete('set null');
            $table->foreign('id_facultad')->nullable()->references('id_facultad')->on('facultades')->onDelete('set null');
            $table->foreign('id_carrera')->nullable()->references('id_carrera')->on('carreras')->onDelete('set null');
            $table->foreign('id_area_academica')->nullable()->references('id_area_academica')->on('area_academicas')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
