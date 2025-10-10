<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantillaCorreo;
use App\Models\TipoCorreoMasivo;
use App\Models\User;

class PlantillasCorreosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar un usuario admin para asignar como creador
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'Administrador');
        })->first();

        if (!$admin) {
            $this->command->info('No se encontró un usuario Administrador. Saltando seeder de plantillas.');
            return;
        }

        // Obtener algunos tipos de correos
        $tipoInforme = TipoCorreoMasivo::where('codigo', 'informe_semanal')->first();
        $tipoAlerta = TipoCorreoMasivo::where('codigo', 'alerta_no_realizada')->first();

        // Plantilla 1: Informe Semanal Profesional
        PlantillaCorreo::create([
            'nombre' => 'Informe Semanal - Diseño Profesional',
            'asunto' => 'Informe Semanal de Clases - {{periodo}}',
            'tipo_correo_masivo_id' => $tipoInforme?->id,
            'contenido_html' => '<div style="font-size: 16px; margin-bottom: 20px;">
    Estimado/a <strong>{{nombre}}</strong>,
</div>

<p>Le enviamos el resumen semanal de sus clases correspondiente al período <strong>{{periodo}}</strong>.</p>

<div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <h3 style="margin-top: 0; color: #667eea;">📈 Estadísticas del Período</h3>
    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e0e0e0;">
        <span style="font-weight: bold; color: #555;">Total de Clases Programadas:</span>
        <span style="color: #667eea; font-weight: bold;">{{total_clases}}</span>
    </div>
    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e0e0e0;">
        <span style="font-weight: bold; color: #555;">Clases No Realizadas:</span>
        <span style="color: #667eea; font-weight: bold;">{{clases_no_realizadas}}</span>
    </div>
    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
        <span style="font-weight: bold; color: #555;">Porcentaje de Cumplimiento:</span>
        <span style="color: #667eea; font-weight: bold;">{{porcentaje}}%</span>
    </div>
</div>

<p>Este informe ha sido generado automáticamente el <strong>{{fecha}}</strong>.</p>

<p>Si tiene alguna consulta o necesita más información, no dude en contactarnos.</p>

<p style="margin-top: 30px;">
    Saludos cordiales,<br>
    <strong>Equipo Académico</strong>
</p>',
            'contenido_texto' => 'Estimado/a {{nombre}},

Le enviamos el resumen semanal de sus clases correspondiente al período {{periodo}}.

ESTADÍSTICAS DEL PERÍODO:
- Total de Clases Programadas: {{total_clases}}
- Clases No Realizadas: {{clases_no_realizadas}}
- Porcentaje de Cumplimiento: {{porcentaje}}%

Este informe ha sido generado automáticamente el {{fecha}}.

Si tiene alguna consulta o necesita más información, no dude en contactarnos.

Saludos cordiales,
Equipo Académico',
            'variables_disponibles' => json_encode([
                'nombre', 'email', 'fecha', 'periodo', 'total_clases', 'clases_no_realizadas', 'porcentaje'
            ]),
            'activo' => true,
            'creado_por' => $admin->run,
        ]);

        // Plantilla 2: Alerta Clases No Realizadas
        PlantillaCorreo::create([
            'nombre' => 'Alerta - Clases No Realizadas',
            'asunto' => '⚠️ Alerta: Clases sin realizar - Acción requerida',
            'tipo_correo_masivo_id' => $tipoAlerta?->id,
            'contenido_html' => '<p>Estimado/a <strong>{{nombre}}</strong>,</p>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Clases</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: #fff3f3;
            border: 2px solid #ff6b6b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-box h3 {
            color: #c92a2a;
            margin-top: 0;
        }
        .warning-text {
            color: #c92a2a;
            font-weight: bold;
            font-size: 16px;
        }
        .action-required {
            background-color: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">⚠️</div>
            <h1>Alerta de Clases No Realizadas</h1>
        </div>
        
        <div class="content">
            <p>Estimado/a <strong>{{nombre}}</strong>,</p>
            
            <div class="alert-box">
                <h3>⚠️ Atención Requerida</h3>
                <p>Se ha detectado que tiene <strong>{{clases_no_realizadas}}</strong> clase(s) sin realizar de un total de <strong>{{total_clases}}</strong> clases programadas.</p>
                <p class="warning-text">Porcentaje de cumplimiento: {{porcentaje}}%</p>
            </div>
            
            <div class="action-required">
                <h4 style="margin-top: 0;">📋 Acción Requerida</h4>
                <p>Por favor, revise las clases pendientes y tome las acciones necesarias para regularizar la situación.</p>
            </div>
            
            <p>Fecha de notificación: <strong>{{fecha}}</strong></p>
            <p>Período: <strong>{{periodo}}</strong></p>
            
            <p style="margin-top: 30px;">
                Para más información, contacte con su supervisor o la administración académica.
            </p>
            
            <p>
                Saludos cordiales,<br>
                <strong>Equipo AulaSync</strong>
            </p>
        </div>
        
        <div class="footer">
            <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
            <p>&copy; 2025 AulaSync. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>',
            'contenido_texto' => '⚠️ ALERTA DE CLASES NO REALIZADAS

Estimado/a {{nombre}},

Se ha detectado que tiene {{clases_no_realizadas}} clase(s) sin realizar de un total de {{total_clases}} clases programadas.

Porcentaje de cumplimiento: {{porcentaje}}%

ACCIÓN REQUERIDA:
Por favor, revise las clases pendientes y tome las acciones necesarias para regularizar la situación.

Fecha de notificación: {{fecha}}
Período: {{periodo}}

Para más información, contacte con su supervisor o la administración académica.

Saludos cordiales,
Equipo AulaSync

---
Este es un correo electrónico automático, por favor no responda a este mensaje.
© 2025 AulaSync. Todos los derechos reservados.',
            'variables_disponibles' => json_encode([
                'nombre', 'email', 'fecha', 'periodo', 'total_clases', 'clases_no_realizadas', 'porcentaje'
            ]),
            'activo' => true,
            'creado_por' => $admin->run,
        ]);

        // Plantilla 3: Plantilla Simple/Básica
        PlantillaCorreo::create([
            'nombre' => 'Plantilla Básica - Sin Diseño',
            'asunto' => 'Notificación del Sistema',
            'tipo_correo_masivo_id' => null,
            'contenido_html' => '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Hola {{nombre}},</h2>
    
    <p>Este es un correo de ejemplo con diseño simple.</p>
    
    <p><strong>Información del período:</strong></p>
    <ul>
        <li>Fecha: {{fecha}}</li>
        <li>Período: {{periodo}}</li>
        <li>Total de clases: {{total_clases}}</li>
        <li>Clases no realizadas: {{clases_no_realizadas}}</li>
    </ul>
    
    <p>Saludos,<br>Equipo AulaSync</p>
</body>
</html>',
            'contenido_texto' => 'Hola {{nombre}},

Este es un correo de ejemplo con diseño simple.

Información del período:
- Fecha: {{fecha}}
- Período: {{periodo}}
- Total de clases: {{total_clases}}
- Clases no realizadas: {{clases_no_realizadas}}

Saludos,
Equipo AulaSync',
            'variables_disponibles' => json_encode([
                'nombre', 'fecha', 'periodo', 'total_clases', 'clases_no_realizadas'
            ]),
            'activo' => true,
            'creado_por' => $admin->run,
        ]);

        $this->command->info('✓ Plantillas de correos creadas exitosamente');
    }
}
