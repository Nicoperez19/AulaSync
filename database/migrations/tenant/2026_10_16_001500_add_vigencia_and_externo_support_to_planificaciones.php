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
        if (Schema::connection('tenant')->hasTable('planificacion_asignaturas')) {
            Schema::connection('tenant')->table('planificacion_asignaturas', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('planificacion_asignaturas', 'fecha_inicio_vigencia')) {
                    $table->date('fecha_inicio_vigencia')->nullable()->after('data_load_id');
                }
                if (!Schema::connection('tenant')->hasColumn('planificacion_asignaturas', 'fecha_fin_vigencia')) {
                    $table->date('fecha_fin_vigencia')->nullable()->after('fecha_inicio_vigencia');
                }
                if (!Schema::connection('tenant')->hasColumn('planificacion_asignaturas', 'es_espacio_externo')) {
                    $table->boolean('es_espacio_externo')->default(false)->after('fecha_fin_vigencia');
                }
            });
        }

        if (Schema::connection('tenant')->hasTable('planificaciones_profesores_colaboradores')) {
            Schema::connection('tenant')->table('planificaciones_profesores_colaboradores', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('planificaciones_profesores_colaboradores', 'fecha_inicio_vigencia')) {
                    $table->date('fecha_inicio_vigencia')->nullable()->after('id_espacio');
                }
                if (!Schema::connection('tenant')->hasColumn('planificaciones_profesores_colaboradores', 'fecha_fin_vigencia')) {
                    $table->date('fecha_fin_vigencia')->nullable()->after('fecha_inicio_vigencia');
                }
            });
        }

        if (Schema::connection('tenant')->hasTable('espacios')) {
            Schema::connection('tenant')->table('espacios', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('espacios', 'es_externo')) {
                    $table->boolean('es_externo')->default(false)->after('estado');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('planificacion_asignaturas')) {
            Schema::connection('tenant')->table('planificacion_asignaturas', function (Blueprint $table) {
                $cols = [];
                foreach (['fecha_inicio_vigencia', 'fecha_fin_vigencia', 'es_espacio_externo'] as $col) {
                    if (Schema::connection('tenant')->hasColumn('planificacion_asignaturas', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::connection('tenant')->hasTable('planificaciones_profesores_colaboradores')) {
            Schema::connection('tenant')->table('planificaciones_profesores_colaboradores', function (Blueprint $table) {
                $cols = [];
                foreach (['fecha_inicio_vigencia', 'fecha_fin_vigencia'] as $col) {
                    if (Schema::connection('tenant')->hasColumn('planificaciones_profesores_colaboradores', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::connection('tenant')->hasTable('espacios')) {
            Schema::connection('tenant')->table('espacios', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('espacios', 'es_externo')) {
                    $table->dropColumn('es_externo');
                }
            });
        }
    }
};
