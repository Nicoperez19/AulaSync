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
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-box .label {
            font-size: 12px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .stat-box .value {
            font-size: 32px;
            font-weight: bold;
            color: #1F2937;
        }
        .stat-box.danger .value {
            color: #DC2626;
        }
        .stat-box.success .value {
            color: #10B981;
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
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            color: #6B7280;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background: #DC2626;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reporte Semanal</h1>
        <p>Clases No Realizadas - Semana {{ $datos['periodo']['semana'] }}</p>
        <p>{{ $datos['periodo']['inicio'] }} - {{ $datos['periodo']['fin'] }}</p>
    </div>

    <div class="content">
        <h2 style="color: #1F2937;">Resumen Ejecutivo</h2>
        
        <div class="stats">
            <div class="stat-box danger">
                <div class="label">Total No Realizadas</div>
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
            <div class="stat-box">
                <div class="label">Profesores Afectados</div>
                <div class="value">{{ $datos['estadisticas']['profesores_afectados'] }}</div>
            </div>
        </div>

        @if(count($datos['profesores']) > 0)
            <div class="info-section">
                <h3>🎓 Profesores con Clases No Realizadas</h3>
                <ul>
                    @foreach($datos['profesores'] as $profesor)
                        <li>
                            <strong>{{ $profesor['profesor'] }}</strong> - 
                            {{ $profesor['total_ausencias'] }} clase(s) no realizada(s)
                            @if($profesor['no_realizadas'] > 0)
                                <span style="color: #DC2626;">({{ $profesor['no_realizadas'] }} sin justificar)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="info-section">
                <h3>📎 Archivo Adjunto</h3>
                <p>Encontrará el reporte detallado completo en el archivo PDF adjunto a este correo.</p>
                <p>El reporte incluye:</p>
                <ul>
                    <li>Detalle completo por profesor</li>
                    <li>Fechas, horarios y espacios específicos</li>
                    <li>Motivos y observaciones de cada clase</li>
                    <li>Estado de justificación</li>
                </ul>
            </div>
        @else
            <div class="info-section">
                <h3>✅ Excelentes Noticias</h3>
                <p>No se registraron clases no realizadas durante esta semana.</p>
                <p>¡Felicitaciones por el 100% de cumplimiento!</p>
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
