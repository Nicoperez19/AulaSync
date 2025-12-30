<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semanal - Clases No Realizadas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
            position: relative;
        }
        
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 80px;
            height: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 40px;
            padding-bottom: 10px;
            border-bottom: 3px solid #DC2626;
        }
        
        .header h1 {
            font-size: 18px;
            color: #DC2626;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .periodo-info {
            background: #F3F4F6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .periodo-info p {
            margin: 3px 0;
        }
        
        .estadisticas-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            width: 24%;
            margin-right: 1%;
            vertical-align: top;
        }
        
        .stat-box:last-child {
            margin-right: 0;
        }
        
        .stat-box .label {
            font-size: 8px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
        }
        
        .stat-box.danger .value {
            color: #DC2626;
        }
        
        .stat-box.success .value {
            color: #10B981;
        }
        
        .profesor-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .profesor-header {
            background: #EF4444;
            color: white;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .profesor-header h3 {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .profesor-header .info {
            font-size: 9px;
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table th {
            background: #F3F4F6;
            color: #374151;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #D1D5DB;
        }
        
        table td {
            padding: 6px;
            border: 1px solid #E5E7EB;
            font-size: 9px;
        }
        
        table tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .estado-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-no-realizada {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .estado-justificado {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #D1D5DB;
            text-align: center;
            font-size: 8px;
            color: #6B7280;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6B7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Logo -->
    @if(file_exists(public_path('images/Logo-UCSC-Color-Horizontal.png')))
        <img src="{{ public_path('images/Logo-UCSC-Color-Horizontal.png') }}" alt="Logo UCSC" class="logo">
    @endif
    
    <!-- Header -->
    <div class="header">
        <h1>REPORTE SEMANAL DE CLASES NO REALIZADAS</h1>
        <div class="subtitle">Gestor de Aulas IT - Sistema de Gestión Académica</div>
    </div>

    <!-- Información del Período -->
    <div class="periodo-info">
        <p><strong>Período:</strong> {{ $periodo['inicio'] }} - {{ $periodo['fin'] }}</p>
        <p><strong>Semana:</strong> {{ $periodo['semana'] }} del año {{ $periodo['anio'] }}</p>
        <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Estadísticas Generales -->
    <div class="estadisticas-grid">
        <div class="stat-box danger">
            <div class="label">Total Clases No Realizadas</div>
            <div class="value">{{ $estadisticas['total_clases_no_realizadas'] }}</div>
        </div>
        <div class="stat-box danger">
            <div class="label">No Realizadas</div>
            <div class="value">{{ $estadisticas['total_no_realizadas'] }}</div>
        </div>
        <div class="stat-box success">
            <div class="label">Justificadas</div>
            <div class="value">{{ $estadisticas['total_justificadas'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Profesores Afectados</div>
            <div class="value">{{ $estadisticas['profesores_afectados'] }}</div>
        </div>
    </div>

    @if(count($profesores) > 0)
        <!-- Detalle por Profesor -->
        @foreach($profesores as $profesor)
            <div class="profesor-section">
                <div class="profesor-header">
                    <h3>{{ $profesor['profesor'] }}</h3>
                    <div class="info">
                        RUN: {{ $profesor['run'] }} | 
                        Total Ausencias: {{ $profesor['total_ausencias'] }} | 
                        No Realizadas: {{ $profesor['no_realizadas'] }} | 
                        Justificadas: {{ $profesor['justificadas'] }}
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 12%;">Fecha</th>
                            <th style="width: 12%;">Día</th>
                            <th style="width: 28%;">Asignatura</th>
                            <th style="width: 10%;">Espacio</th>
                            <th style="width: 10%;">Módulo</th>
                            <th style="width: 13%;">Estado</th>
                            <th style="width: 15%;">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profesor['clases'] as $clase)
                            <tr>
                                <td>{{ $clase['fecha'] }}</td>
                                <td>{{ ucfirst($clase['dia_semana']) }}</td>
                                <td>
                                    <strong>{{ $clase['asignatura'] }}</strong><br>
                                    <small style="color: #6B7280;">{{ $clase['codigo_asignatura'] }}</small>
                                </td>
                                <td>{{ $clase['espacio'] }}</td>
                                <td>{{ $clase['modulo'] }}</td>
                                <td>
                                    <span class="estado-badge estado-{{ $clase['estado'] === 'no_realizada' ? 'no-realizada' : 'justificado' }}">
                                        {{ $clase['estado'] === 'no_realizada' ? 'No Realizada' : 'Justificado' }}
                                    </span>
                                </td>
                                <td style="font-size: 8px;">{{ $clase['motivo'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div class="no-data">
            <p>✓ No se registraron clases no realizadas durante esta semana</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Gestor de Aulas IT</p>
        <p>Documento confidencial - Uso interno exclusivo</p>
    </div>
</body>
</html>
