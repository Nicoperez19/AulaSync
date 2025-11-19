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
        Schema::table('profesores_colaboradores', function (Blueprint $table) {
            $table->integer('cantidad_inscritos')->unsigned()->default(0)->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesores_colaboradores', function (Blueprint $table) {
            $table->dropColumn('cantidad_inscritos');
        });
    }
};
