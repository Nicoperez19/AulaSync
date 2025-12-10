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
        Schema::create('facultades', function (Blueprint $table) {
            $table->string('id_facultad', 20)->primary();
            $table->string('nombre_facultad', 100);

            $table->string('id_universidad', 20);
            // Foreign key removed - universidad is in landlord database

            $table->string('id_sede', 20);
            // Foreign key removed - sede is in landlord database

            $table->string('id_campus', 20)->nullable();
            $table->foreign('id_campus')->references('id_campus')->on('campuses')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facultades');
    }
};
