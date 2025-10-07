<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoCorreoMasivo;
use Carbon\Carbon;

class TiposCorreosMasivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposCorreos = [
            [
                'nombre' => 'Informe Semanal de Clases No Realizadas',
                'codigo' => 'informe_semanal_clases_no_realizadas',
                'descripcion' => 'Informe semanal enviado a jefes de carrera y directivos con el resumen de clases no realizadas en la semana.',
                'tipo' => 'sistema',
                'frecuencia' => 'semanal',
                'activo' => true,
                'configuracion' => json_encode([
                    'dia_envio' => 'lunes',
                    'hora_envio' => '08:00',
                    'incluye_graficos' => true,
                    'incluye_detalle' => true,
                ]),
            ],
            [
                'nombre' => 'Informe Mensual de Clases No Realizadas',
                'codigo' => 'informe_mensual_clases_no_realizadas',
                'descripcion' => 'Informe mensual enviado a directores y subdirectores con estadísticas consolidadas del mes.',
                'tipo' => 'sistema',
                'frecuencia' => 'mensual',
                'activo' => true,
                'configuracion' => json_encode([
                    'dia_envio' => 'primer_lunes_mes',
                    'hora_envio' => '09:00',
                    'incluye_graficos' => true,
                    'incluye_comparativa' => true,
                ]),
            ],
            [
                'nombre' => 'Notificación de Clase No Realizada',
                'codigo' => 'notificacion_clase_no_realizada',
                'descripcion' => 'Notificación inmediata cuando se registra una clase como no realizada.',
                'tipo' => 'sistema',
                'frecuencia' => 'manual',
                'activo' => true,
                'configuracion' => json_encode([
                    'envio_inmediato' => true,
                    'incluye_motivo' => true,
                ]),
            ],
            [
                'nombre' => 'Alerta de Clases No Justificadas',
                'codigo' => 'alerta_clases_no_justificadas',
                'descripcion' => 'Alerta diaria de clases no realizadas que aún no han sido justificadas.',
                'tipo' => 'sistema',
                'frecuencia' => 'diario',
                'activo' => true,
                'configuracion' => json_encode([
                    'hora_envio' => '16:00',
                    'umbral_dias' => 2,
                ]),
            ],
            [
                'nombre' => 'Reporte de Ocupación de Espacios',
                'codigo' => 'reporte_ocupacion_espacios',
                'descripcion' => 'Reporte semanal sobre la ocupación y utilización de espacios físicos.',
                'tipo' => 'sistema',
                'frecuencia' => 'semanal',
                'activo' => true,
                'configuracion' => json_encode([
                    'dia_envio' => 'viernes',
                    'hora_envio' => '14:00',
                    'incluye_graficos' => true,
                ]),
            ],
            [
                'nombre' => 'Resumen de Reservas Semanales',
                'codigo' => 'resumen_reservas_semanales',
                'descripcion' => 'Resumen semanal de reservas realizadas y pendientes.',
                'tipo' => 'sistema',
                'frecuencia' => 'semanal',
                'activo' => true,
                'configuracion' => json_encode([
                    'dia_envio' => 'domingo',
                    'hora_envio' => '18:00',
                ]),
            ],
            [
                'nombre' => 'Comunicados Administrativos',
                'codigo' => 'comunicados_administrativos',
                'descripcion' => 'Correos masivos para comunicados generales de la administración.',
                'tipo' => 'sistema',
                'frecuencia' => 'manual',
                'activo' => true,
                'configuracion' => json_encode([
                    'requiere_aprobacion' => true,
                    'permite_adjuntos' => true,
                ]),
            ],
        ];

        foreach ($tiposCorreos as $tipo) {
            TipoCorreoMasivo::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }

        $this->command->info('✓ Tipos de correos masivos creados exitosamente');
    }
}
