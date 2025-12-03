<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - Clases No Realizadas</title>
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
            border-bottom: 3px solid #EF4444;
        }
        
        .header h1 {
            font-size: 20px;
            color: #EF4444;
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
            color: #EF4444;
        }
        
        .stat-box.success .value {
            color: #3B82F6;
        }
        
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .tabla th,
        .tabla td {
            border: 1px solid #E5E7EB;
            padding: 6px;
            text-align: left;
            font-size: 8px;
        }
        
        .tabla th {
            background: #EF4444;
            color: white;
            font-weight: bold;
        }
        
        .tabla tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .estado-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .estado-recuperada {
            background: #DBEAFE;
            color: #1E40AF;
        }
        
        .estado-pendiente {
            background: #FED7AA;
            color: #C2410C;
        }
        
        .estado-no-realizada {
            background: #FEE2E2;
            color: #991B1B;
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
        <h1>Reporte de Clases No Realizadas</h1>
        <p class="subtitle">Sistema de Gestión de Espacios - AulaSync</p>
    </div>
    
    <div class="periodo-info">
        <p><strong>Período:</strong> {{ $periodo ?? 'No definido' }}</p>
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="estadisticas-grid">
        <div class="stat-box">
            <div class="label">Total No Realizadas</div>
            <div class="value">{{ $totalNoRealizadas ?? 0 }}</div>
        </div>
        <div class="stat-box success">
            <div class="label">Recuperadas</div>
            <div class="value">{{ $clasesRecuperadas ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Pendientes</div>
            <div class="value">{{ $clasesPendientes ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">% Recuperadas</div>
            <div class="value">{{ $porcentajeRecuperadas ?? 0 }}%</div>
        </div>
    </div>
    
    @if(isset($clasesDetalle) && count($clasesDetalle) > 0)
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Asignatura</th>
                <th>Profesor</th>
                <th>Módulo</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clasesDetalle as $clase)
                <tr>
                    <td>{{ $clase['fecha'] }}</td>
                    <td>{{ $clase['asignatura'] }}</td>
                    <td>{{ $clase['profesor'] }}</td>
                    <td>{{ $clase['modulo'] }}</td>
                    <td>{{ $clase['hora'] }}</td>
                    <td>
                        <span class="estado-badge estado-{{ str_replace('_', '-', $clase['estado']) }}">
                            @if($clase['estado'] === 'recuperada')
                                Recuperada
                            @elseif($clase['estado'] === 'pendiente')
                                Pendiente
                            @else
                                No Realizada
                            @endif
                        </span>
                    </td>
                    <td>{{ $clase['motivo'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p style="text-align: center; padding: 20px; color: #6B7280;">No hay clases no realizadas en este período.</p>
    @endif
    
    <div class="footer">
        <p>Generado por AulaSync - Sistema de Gestión de Espacios</p>
        <p>© {{ now()->year }} Todos los derechos reservados</p>
    </div>
</body>
</html>
