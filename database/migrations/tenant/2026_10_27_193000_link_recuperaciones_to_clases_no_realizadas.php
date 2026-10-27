<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE recuperacion_clases MODIFY id_licencia BIGINT UNSIGNED NULL');

        Schema::table('recuperacion_clases', function (Blueprint $table) {
            $table->unsignedBigInteger('id_clase_no_realizada')->nullable()->after('id_licencia');
            $table->unique('id_clase_no_realizada', 'recuperacion_clase_no_realizada_unique');
            $table->foreign('id_clase_no_realizada', 'recuperacion_clase_no_realizada_foreign')
                ->references('id')
                ->on('clases_no_realizadas')
                ->nullOnDelete();
        });

        DB::table('clases_no_realizadas')
            ->where('observaciones', 'like', '%CLASE PENDIENTE DE RECUPERACIÓN%')
            ->orderBy('id')
            ->chunkById(100, function ($clases) {
                foreach ($clases as $clase) {
                    $pattern = '/Reagendada desde (\d{2}\/\d{2}\/\d{4}) \((.*?), módulo ([^)]+)\) a /u';
                    if (!preg_match($pattern, $clase->observaciones ?? '', $matches)) {
                        throw new RuntimeException("No se pudo recuperar el origen del reagendamiento de la clase {$clase->id}.");
                    }

                    $fechaOriginal = Carbon::createFromFormat('d/m/Y', $matches[1])->toDateString();
                    $espacioOriginal = trim($matches[2]);
                    $moduloOriginal = trim(explode(',', $matches[3])[0]);
                    $moduloReagendado = trim(explode(',', $clase->id_modulo)[0]);

                    $recuperacion = DB::table('recuperacion_clases')
                        ->where('run_profesor', $clase->run_profesor)
                        ->where('id_asignatura', $clase->id_asignatura)
                        ->where('id_espacio', $espacioOriginal)
                        ->whereDate('fecha_clase_original', $fechaOriginal)
                        ->where('id_modulo_original', $moduloOriginal)
                        ->first();

                    $datos = [
                        'id_clase_no_realizada' => $clase->id,
                        'run_profesor' => $clase->run_profesor,
                        'id_asignatura' => $clase->id_asignatura,
                        'id_espacio' => $espacioOriginal,
                        'fecha_clase_original' => $fechaOriginal,
                        'id_modulo_original' => $moduloOriginal,
                        'fecha_reagendada' => $clase->fecha_clase,
                        'id_modulo_reagendado' => $moduloReagendado,
                        'id_espacio_reagendado' => $clase->id_espacio,
                        'estado' => in_array($clase->estado, ['recuperada', 'realizada'], true) ? 'realizada' : 'reagendada',
                        'notificado' => false,
                        'fecha_notificacion' => null,
                        'notas' => $clase->observaciones,
                        'updated_at' => $clase->updated_at,
                    ];

                    if ($recuperacion) {
                        DB::table('recuperacion_clases')
                            ->where('id_recuperacion', $recuperacion->id_recuperacion)
                            ->update($datos);
                        continue;
                    }

                    $datos['id_licencia'] = null;
                    $datos['notificado'] = false;
                    $datos['created_at'] = $clase->created_at;
                    DB::table('recuperacion_clases')->insert($datos);
                }
            });
    }

    public function down(): void
    {
        Schema::table('recuperacion_clases', function (Blueprint $table) {
            $table->dropForeign('recuperacion_clase_no_realizada_foreign');
            $table->dropUnique('recuperacion_clase_no_realizada_unique');
            $table->dropColumn('id_clase_no_realizada');
        });
    }
};
