<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-box .label {
            font-size: 10px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .stat-box .value {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
        }
        .stat-box.danger .value {
            color: #DC2626;
        }
        .stat-box.success .value {
            color: #10B981;
        }
        .stat-box.warning .value {
            color: #F59E0B;
        }
        .info-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #DC2626;
        }
        .info-section h3 {
            margin-top: 0;
            color: #DC2626;
        }
        .info-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-section li {
            margin: 8px 0;
        }
        .highlight-box {
            background: #FEF3C7;
            border: 2px solid #F59E0B;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .highlight-box .big-number {
            font-size: 48px;
            font-weight: bold;
            color: #F59E0B;
            text-align: center;
            margin: 10px 0;
        }
        .highlight-box p {
            text-align: center;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            color: #6B7280;
            font-size: 12px;
        }
        .top-professors {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .top-professors table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-professors th {
            background: #F3F4F6;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        .top-professors td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reporte Mensual</h1>
        <p>Clases No Realizadas - {{ ucfirst($datos['periodo']['mes']) }} {{ $datos['periodo']['anio'] }}</p>
        <p>{{ $datos['periodo']['inicio'] }} - {{ $datos['periodo']['fin'] }}</p>
    </div>

    <div class="content">
        <h2 style="color: #1F2937;">Resumen Ejecutivo del Mes</h2>
        
        <div class="highlight-box">
            <p style="font-weight: bold; font-size: 14px;">Porcentaje de Clases No Realizadas</p>
            <div class="big-number">{{ $datos['estadisticas']['porcentaje_no_realizadas'] }}%</div>
            <p style="font-size: 12px;">
                {{ $datos['estadisticas']['total_clases_no_realizadas'] }} de aproximadamente 
                {{ $datos['estadisticas']['total_clases_programadas'] }} clases programadas
            </p>
        </div>

        <div class="stats">
            <div class="stat-box danger">
                <div class="label">Total</div>
                <div class="value">{{ $datos['estadisticas']['total_clases_no_realizadas'] }}</div>
            </div>
            <div class="stat-box danger">
                <div class="label">Sin Justificar</div>
                <div class="value">{{ $datos['estadisticas']['total_no_realizadas'] }}</div>
            </div>
            <div class="stat-box success">
                <div class="label">Justificadas</div>
                <div class="value">{{ $datos['estadisticas']['total_justificadas'] }}</div>
            </div>
            <div class="stat-box warning">
                <div class="label">Recuperadas</div>
                <div class="value">{{ $datos['estadisticas']['total_recuperadas'] }}</div>
            </div>
            <div class="stat-box">
                <div class="label">Profesores</div>
                <div class="value">{{ $datos['estadisticas']['profesores_afectados'] }}</div>
            </div>
            <div class="stat-box {{ $datos['estadisticas']['porcentaje_no_realizadas'] > 5 ? 'danger' : 'success' }}">
                <div class="label">% del Total</div>
                <div class="value">{{ $datos['estadisticas']['porcentaje_no_realizadas'] }}%</div>
            </div>
        </div>

        @if(count($datos['profesores']) > 0)
            <div class="top-professors">
                <h3 style="color: #DC2626; margin-top: 0;">🎓 Top Profesores con Más Ausencias</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th style="text-align: center;">Total</th>
                            <th style="text-align: center;">Sin Justificar</th>
                            <th style="text-align: center;">Recuperadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($datos['profesores'], 0, 5) as $profesor)
                            <tr>
                                <td><strong>{{ $profesor['profesor'] }}</strong></td>
                                <td style="text-align: center; font-weight: bold; color: #DC2626;">
                                    {{ $profesor['total_ausencias'] }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $profesor['no_realizadas'] }}
                                </td>
                                <td style="text-align: center; color: #10B981;">
                                    {{ $profesor['recuperadas'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="info-section">
                <h3>📈 Análisis del Mes</h3>
                <ul>
                    <li><strong>Clases Justificadas:</strong> {{ $datos['estadisticas']['total_justificadas'] }} 
                        ({{ round(($datos['estadisticas']['total_justificadas'] / max($datos['estadisticas']['total_clases_no_realizadas'], 1)) * 100, 1) }}%)
                    </li>
                    <li><strong>Clases Recuperadas:</strong> {{ $datos['estadisticas']['total_recuperadas'] }}
                        ({{ round(($datos['estadisticas']['total_recuperadas'] / max($datos['estadisticas']['total_clases_no_realizadas'], 1)) * 100, 1) }}%)
                    </li>
                    <li><strong>Profesores Afectados:</strong> {{ $datos['estadisticas']['profesores_afectados'] }} docentes</li>
                    @if($datos['estadisticas']['porcentaje_no_realizadas'] > 5)
                        <li style="color: #DC2626;"><strong>⚠️ Alerta:</strong> El porcentaje de clases no realizadas supera el 5%</li>
                    @else
                        <li style="color: #10B981;"><strong>✓ Buen Desempeño:</strong> El porcentaje de clases no realizadas está bajo control</li>
                    @endif
                </ul>
            </div>

            <div class="info-section">
                <h3>📎 Archivo Adjunto</h3>
                <p>Encontrará el reporte detallado completo en el archivo PDF adjunto a este correo.</p>
                <p>El reporte mensual incluye:</p>
                <ul>
                    <li>Resumen ejecutivo por profesor con estadísticas de cumplimiento</li>
                    <li>Detalle completo de cada clase no realizada</li>
                    <li>Indicadores de recuperación y justificación</li>
                    <li>Análisis de tendencias y porcentajes</li>
                    <li>Recomendaciones basadas en datos</li>
                </ul>
            </div>
        @else
            <div class="info-section">
                <h3>✅ Excelente Desempeño</h3>
                <p>No se registraron clases no realizadas durante este mes.</p>
                <p>¡Felicitaciones por el 100% de cumplimiento académico!</p>
            </div>
        @endif

        <div class="footer">
            <p><strong>Sistema de Gestión Académica - AulaSync</strong></p>
            <p>Este es un correo automático, por favor no responder.</p>
            <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
