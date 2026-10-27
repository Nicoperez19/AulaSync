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
        if (Schema::hasTable('vetos_sala_estudio') && !Schema::hasColumn('vetos_sala_estudio', 'fecha_fin_veto')) {
            Schema::table('vetos_sala_estudio', function (Blueprint $table) {
                $table->timestamp('fecha_fin_veto')->nullable()->after('fecha_veto');
                $table->index(['estado', 'fecha_fin_veto'], 'idx_estado_fecha_fin_veto');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vetos_sala_estudio') && Schema::hasColumn('vetos_sala_estudio', 'fecha_fin_veto')) {
            Schema::table('vetos_sala_estudio', function (Blueprint $table) {
                $table->dropIndex('idx_estado_fecha_fin_veto');
                $table->dropColumn('fecha_fin_veto');
            });
        }
    }
};
