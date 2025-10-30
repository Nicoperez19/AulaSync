<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la columna ya existe antes de agregarla
        if (!Schema::hasColumn('espacios', 'capacidad_maxima')) {
            Schema::table('espacios', function (Blueprint $table) {
                // Agregar la nueva columna capacidad_maxima
                $table->integer('capacidad_maxima')->nullable()->after('puestos_disponibles');
            });
        }

        // Migrar los valores actuales de puestos_disponibles a capacidad_maxima
        // Si puestos_disponibles es null, usar 0
        DB::statement('UPDATE espacios SET capacidad_maxima = COALESCE(puestos_disponibles, 0) WHERE capacidad_maxima IS NULL OR capacidad_maxima = 0');

        // Asegurarse de que no haya valores null antes de cambiar a NOT NULL
        DB::statement('UPDATE espacios SET capacidad_maxima = 0 WHERE capacidad_maxima IS NULL');
        
        // Hacer la columna NOT NULL después de migrar los datos
        Schema::table('espacios', function (Blueprint $table) {
            $table->integer('capacidad_maxima')->nullable(false)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('espacios', function (Blueprint $table) {
            $table->dropColumn('capacidad_maxima');
        });
    }
};
