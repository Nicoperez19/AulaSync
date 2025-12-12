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
        // Add initialization fields to tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('is_initialized')->default(false)->after('is_default');
            $table->timestamp('initialized_at')->nullable()->after('is_initialized');
            $table->integer('initialization_step')->default(0)->after('initialized_at');
        });

        // Add logo field to sedes table
        Schema::table('sedes', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('prefijo_sala');
            $table->text('descripcion')->nullable()->after('logo');
            $table->string('direccion')->nullable()->after('descripcion');
            $table->string('telefono', 20)->nullable()->after('direccion');
            $table->string('email', 100)->nullable()->after('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['is_initialized', 'initialized_at', 'initialization_step']);
        });

        Schema::table('sedes', function (Blueprint $table) {
            $table->dropColumn(['logo', 'descripcion', 'direccion', 'telefono', 'email']);
        });
    }
};
