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
        Schema::table('pisos', function (Blueprint $table) {
            $table->string('nombre_piso', 255)->nullable()->after('numero_piso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pisos', function (Blueprint $table) {
            $table->dropColumn('nombre_piso');
        });
    }
};
