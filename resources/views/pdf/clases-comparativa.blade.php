<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Comparativo - Clases Realizadas vs No Realizadas</title>
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
            border-bottom: 3px solid #6366F1;
        }
        
        .header h1 {
            font-size: 20px;
            color: #6366F1;
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
            padding: 10px;
            text-align: center;
            width: 20%;
        }
        
        .stat-box .label {
            font-size: 8px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .stat-box.success .value {
            color: #10B981;
        }
        
        .stat-box.danger .value {
            color: #EF4444;
        }
        
        .stat-box.info .value {
            color: #3B82F6;
        }
        
        .stat-box.warning .value {
            color: #F59E0B;
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
        
        .pie-chart-container {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .pie-legend {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        
        .legend-item {
            display: table-cell;
            text-align: center;
            padding: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        .legend-text {
            display: inline-block;
            vertical-align: middle;
            font-size: 9px;
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
            background: #6366F1;
            color: white;
            font-weight: bold;
        }
        
        .tabla tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .bar-chart {
            width: 100%;
            margin-top: 15px;
        }
        
        .bar-row {
            margin-bottom: 10px;
        }
        
        .bar-label {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .bar-container {
            width: 100%;
            height: 25px;
            background: #F3F4F6;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }
        
        .bar-fill-realizadas {
            height: 100%;
            background: linear-gradient(90deg, #10B981, #059669);
            float: left;
        }
        
        .bar-fill-no-realizadas {
            height: 100%;
            background: linear-gradient(90deg, #EF4444, #DC2626);
            float: left;
        }
        
        .bar-text {
            position: absolute;
            width: 100%;
            text-align: center;
            line-height: 25px;
            font-size: 8px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #E5E7EB;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Comparativo</h1>
        <p class="subtitle">Clases Realizadas vs No Realizadas - Sistema AulaSync</p>
    </div>
    
    <div class="periodo-info">
        <p><strong>Período:</strong> {{ $periodo ?? 'No definido' }}</p>
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="estadisticas-grid">
        <div class="stat-box">
            <div class="label">Total Clases</div>
            <div class="value">{{ $totalClases ?? 0 }}</div>
        </div>
        <div class="stat-box success">
            <div class="label">Realizadas</div>
            <div class="value">{{ $totalRealizadas ?? 0 }}</div>
        </div>
        <div class="stat-box danger">
            <div class="label">No Realizadas</div>
            <div class="value">{{ $totalNoRealizadas ?? 0 }}</div>
        </div>
        <div class="stat-box info">
            <div class="label">Recuperadas</div>
            <div class="value">{{ $clasesRecuperadas ?? 0 }}</div>
        </div>
        <div class="stat-box warning">
            <div class="label">Pendientes</div>
            <div class="value">{{ $clasesPendientes ?? 0 }}</div>
        </div>
    </div>
    
    <div class="chart-section">
        <h3 class="section-title">Distribución de Clases (Gráfico de Torta)</h3>
        <div class="pie-chart-container">
            <svg width="300" height="200" style="margin: 0 auto; display: block;">
                @php
                    $total = ($totalRealizadas ?? 0) + ($totalNoRealizadas ?? 0);
                    $porcentajeRealizadas = $total > 0 ? (($totalRealizadas ?? 0) / $total) * 100 : 0;
                    $porcentajeNoRealizadas = $total > 0 ? (($totalNoRealizadas ?? 0) / $total) * 100 : 0;
                    
                    // Calculate pie slices
                    $cx = 150;
                    $cy = 100;
                    $r = 80;
                    
                    $angleRealizadas = ($porcentajeRealizadas / 100) * 360;
                    $angleNoRealizadas = ($porcentajeNoRealizadas / 100) * 360;
                    
                    // Convert to radians
                    $radiansRealizadas = deg2rad($angleRealizadas);
                    $radiansNoRealizadas = deg2rad($angleNoRealizadas);
                    
                    // Calculate end points for first slice (realizadas)
                    $x1 = $cx + $r * cos(-pi()/2);
                    $y1 = $cy + $r * sin(-pi()/2);
                    $x2 = $cx + $r * cos(-pi()/2 + $radiansRealizadas);
                    $y2 = $cy + $r * sin(-pi()/2 + $radiansRealizadas);
                    
                    $largeArc1 = $angleRealizadas > 180 ? 1 : 0;
                    
                    // Path for realizadas
                    $pathRealizadas = "M $cx,$cy L $x1,$y1 A $r,$r 0 $largeArc1,1 $x2,$y2 Z";
                    
                    // Calculate end points for second slice (no realizadas)
                    $x3 = $x2;
                    $y3 = $y2;
                    $x4 = $x1;
                    $y4 = $y1;
                    
                    $largeArc2 = $angleNoRealizadas > 180 ? 1 : 0;
                    
                    // Path for no realizadas
                    $pathNoRealizadas = "M $cx,$cy L $x3,$y3 A $r,$r 0 $largeArc2,1 $x4,$y4 Z";
                @endphp
                
                @if($total > 0)
                    <!-- Realizadas slice (green) -->
                    <path d="{{ $pathRealizadas }}" fill="#10B981" stroke="white" stroke-width="2"/>
                    
                    <!-- No Realizadas slice (red) -->
                    <path d="{{ $pathNoRealizadas }}" fill="#EF4444" stroke="white" stroke-width="2"/>
                    
                    <!-- Center text -->
                    <text x="{{ $cx }}" y="{{ $cy }}" text-anchor="middle" font-size="20" font-weight="bold" fill="#374151">{{ $total }}</text>
                    <text x="{{ $cx }}" y="{{ $cy + 15 }}" text-anchor="middle" font-size="10" fill="#6B7280">Total</text>
                @else
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="#E5E7EB" stroke="white" stroke-width="2"/>
                    <text x="{{ $cx }}" y="{{ $cy }}" text-anchor="middle" font-size="12" fill="#6B7280">Sin datos</text>
                @endif
            </svg>
        </div>
        
        <div class="pie-legend">
            <div class="legend-item">
                <span class="legend-color" style="background: #10B981;"></span>
                <span class="legend-text">Realizadas: {{ $totalRealizadas ?? 0 }} ({{ round($porcentajeRealizadas ?? 0, 1) }}%)</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background: #EF4444;"></span>
                <span class="legend-text">No Realizadas: {{ $totalNoRealizadas ?? 0 }} ({{ round($porcentajeNoRealizadas ?? 0, 1) }}%)</span>
            </div>
        </div>
    </div>
    
    @if(isset($datosGrafico) && count($datosGrafico) > 0)
    <div class="chart-section">
        <h3 class="section-title">Comparativa por Día (Gráfico de Barras)</h3>
        <div class="bar-chart">
            @foreach($datosGrafico as $dia)
                @php
                    $totalDia = $dia['total'];
                    $widthRealizadas = $totalDia > 0 ? ($dia['realizadas'] / $totalDia) * 100 : 0;
                    $widthNoRealizadas = $totalDia > 0 ? ($dia['no_realizadas'] / $totalDia) * 100 : 0;
                @endphp
                <div class="bar-row">
                    <div class="bar-label">{{ $dia['fecha'] }} (Total: {{ $totalDia }})</div>
                    <div class="bar-container">
                        @if($totalDia > 0)
                            <div class="bar-fill-realizadas" style="width: {{ $widthRealizadas }}%;"></div>
                            <div class="bar-fill-no-realizadas" style="width: {{ $widthNoRealizadas }}%;"></div>
                            <div class="bar-text">
                                Realizadas: {{ $dia['realizadas'] }} | No Realizadas: {{ $dia['no_realizadas'] }}
                            </div>
                        @else
                            <div class="bar-text" style="color: #6B7280;">Sin clases</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="chart-section">
        <h3 class="section-title">Tabla Detallada</h3>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Realizadas</th>
                    <th>No Realizadas</th>
                    <th>Recuperadas</th>
                    <th>Total</th>
                    <th>% Realizadas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosGrafico as $dia)
                    <tr>
                        <td>{{ $dia['fecha'] }}</td>
                        <td style="color: #10B981; font-weight: bold;">{{ $dia['realizadas'] }}</td>
                        <td style="color: #EF4444; font-weight: bold;">{{ $dia['no_realizadas'] }}</td>
                        <td style="color: #3B82F6; font-weight: bold;">{{ $dia['recuperadas'] ?? 0 }}</td>
                        <td>{{ $dia['total'] }}</td>
                        <td>{{ $dia['porcentaje'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <div class="footer">
        <p>Generado por AulaSync - Sistema de Gestión de Espacios</p>
        <p>© {{ now()->year }} Todos los derechos reservados</p>
    </div>
</body>
</html>
