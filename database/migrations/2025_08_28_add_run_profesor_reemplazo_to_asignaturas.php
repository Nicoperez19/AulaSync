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
        Schema::table('asignaturas', function (Blueprint $table) {
            $table->string('run_profesor_reemplazo')->nullable()->after('run_profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignaturas', function (Blueprint $table) {
            $table->dropColumn('run_profesor_reemplazo');
        });
    }
};
