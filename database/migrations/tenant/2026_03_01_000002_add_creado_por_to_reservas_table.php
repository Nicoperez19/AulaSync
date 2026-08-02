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
        if (Schema::hasTable('reservas') && !Schema::hasColumn('reservas', 'creado_por')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->string('creado_por')->nullable()->after('run_solicitante');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reservas') && Schema::hasColumn('reservas', 'creado_por')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->dropColumn('creado_por');
            });
        }
    }
};
