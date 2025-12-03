<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - Clases Realizadas</title>
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
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #10B981;
        }
        
        .header h1 {
            font-size: 20px;
            color: #10B981;
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
        }
        
        .periodo-info p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .estadisticas-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stat-box {
            display: table-cell;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 12px;
            text-align: center;
            width: 25%;
        }
        
        .stat-box .label {
            font-size: 9px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #10B981;
        }
        
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .tabla th,
        .tabla td {
            border: 1px solid #E5E7EB;
            padding: 8px;
            text-align: center;
            font-size: 9px;
        }
        
        .tabla th {
            background: #10B981;
            color: white;
            font-weight: bold;
        }
        
        .tabla tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .chart-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .chart-section h3 {
            font-size: 14px;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .chart-placeholder {
            width: 100%;
            height: 200px;
            background: #F3F4F6;
            border: 1px dashed #9CA3AF;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6B7280;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Clases Realizadas</h1>
        <p class="subtitle">Sistema de Gestión de Espacios - AulaSync</p>
    </div>
    
    <div class="periodo-info">
        <p><strong>Período:</strong> {{ $periodo ?? 'No definido' }}</p>
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="estadisticas-grid">
        <div class="stat-box">
            <div class="label">Total Realizadas</div>
            <div class="value">{{ $totalRealizadas ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Clases</div>
            <div class="value">{{ $totalClases ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">% Realizadas</div>
            <div class="value">{{ $porcentajeRealizadas ?? 0 }}%</div>
        </div>
        <div class="stat-box">
            <div class="label">Promedio Diario</div>
            <div class="value">{{ $promedioDiario ?? 0 }}</div>
        </div>
    </div>
    
    @if(isset($datosGrafico) && count($datosGrafico) > 0)
    <div class="chart-section">
        <h3>Gráfico de Barras - Clases Realizadas por Día</h3>
        <div class="chart-placeholder">
            Gráfico de barras mostrando {{ count($datosGrafico) }} días de datos
        </div>
    </div>
    @endif
    
    @if(isset($datosGrafico) && count($datosGrafico) > 0)
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Clases Realizadas</th>
                <th>Clases No Realizadas</th>
                <th>Total</th>
                <th>% Realizadas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datosGrafico as $dia)
                <tr>
                    <td>{{ $dia['fecha'] }}</td>
                    <td style="color: #10B981; font-weight: bold;">{{ $dia['realizadas'] }}</td>
                    <td>{{ $dia['no_realizadas'] }}</td>
                    <td>{{ $dia['total'] }}</td>
                    <td>{{ $dia['porcentaje'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <div class="footer">
        <p>Generado por AulaSync - Sistema de Gestión de Espacios</p>
        <p>© {{ now()->year }} Todos los derechos reservados</p>
    </div>
</body>
</html>
