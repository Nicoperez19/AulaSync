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
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'id_sede')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('id_sede', 20)->nullable()->after('id_area_academica');

            if (Schema::hasTable('sedes')) {
                $table->foreign('id_sede')->references('id_sede')->on('sedes')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'id_sede')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasTable('sedes')) {
                $table->dropForeign(['id_sede']);
            }

            $table->dropColumn('id_sede');
        });
    }
};
