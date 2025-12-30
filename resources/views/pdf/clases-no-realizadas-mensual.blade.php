<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual - Clases No Realizadas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            padding: 15px;
            position: relative;
        }
        
        .logo {
            position: absolute;
            top: 15px;
            left: 15px;
            width: 70px;
            height: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            margin-top: 35px;
            padding-bottom: 8px;
            border-bottom: 3px solid #DC2626;
        }
        
        .header h1 {
            font-size: 16px;
            color: #DC2626;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 11px;
            color: #666;
        }
        
        .periodo-info {
            background: #F3F4F6;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .periodo-info p {
            margin: 2px 0;
            font-size: 9px;
        }
        
        .estadisticas-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .stat-box {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 8px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            width: 16%;
            margin-right: 0.5%;
            vertical-align: top;
        }
        
        .stat-box:last-child {
            margin-right: 0;
        }
        
        .stat-box .label {
            font-size: 7px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        
        .stat-box .value {
            font-size: 16px;
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
        
        .stat-box.info .value {
            color: #3B82F6;
        }
        
        .resumen-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .resumen-table th {
            background: #1F2937;
            color: white;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            font-size: 8px;
            border: 1px solid #374151;
        }
        
        .resumen-table td {
            padding: 5px;
            border: 1px solid #D1D5DB;
            font-size: 8px;
        }
        
        .resumen-table tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .resumen-table tr:hover {
            background: #F3F4F6;
        }
        
        .profesor-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .profesor-header {
            background: #DC2626;
            color: white;
            padding: 6px 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .profesor-header h3 {
            font-size: 11px;
            margin-bottom: 2px;
        }
        
        .profesor-header .info {
            font-size: 8px;
            opacity: 0.95;
        }
        
        .profesor-stats {
            background: #FEE2E2;
            padding: 5px 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            width: 100%;
            text-align: center;
        }
        
        .profesor-stats .stat {
            font-size: 8px;
            display: inline-block;
            width: 19%;
            margin-right: 1%;
            vertical-align: top;
        }
        
        .profesor-stats .stat:last-child {
            margin-right: 0;
        }
        
        .profesor-stats .stat .label {
            color: #6B7280;
            display: block;
            margin-bottom: 2px;
        }
        
        .profesor-stats .stat .value {
            font-weight: bold;
            font-size: 11px;
            color: #1F2937;
        }
        
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        table.detalle th {
            background: #F3F4F6;
            color: #374151;
            font-weight: bold;
            padding: 5px;
            text-align: left;
            font-size: 8px;
            border: 1px solid #D1D5DB;
        }
        
        table.detalle td {
            padding: 4px;
            border: 1px solid #E5E7EB;
            font-size: 8px;
        }
        
        table.detalle tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .estado-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        
        .estado-no-realizada {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .estado-justificado {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .badge-si {
            background: #D1FAE5;
            color: #065F46;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .badge-no {
            background: #FEE2E2;
            color: #991B1B;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #D1D5DB;
            text-align: center;
            font-size: 7px;
            color: #6B7280;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6B7280;
            font-style: italic;
        }
        
        .section-title {
            background: #374151;
            color: white;
            padding: 6px 10px;
            margin: 15px 0 10px 0;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
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
        <h1>REPORTE MENSUAL DE CLASES NO REALIZADAS</h1>
        <div class="subtitle">Gestor de Aulas IT - Sistema de Gestión Académica</div>
    </div>

    <!-- Información del Período -->
    <div class="periodo-info">
        <p><strong>Mes:</strong> {{ ucfirst($periodo['mes']) }} {{ $periodo['anio'] }}</p>
        <p><strong>Período:</strong> {{ $periodo['inicio'] }} - {{ $periodo['fin'] }}</p>
        <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Estadísticas Generales -->
    <div class="estadisticas-grid">
        <div class="stat-box danger">
            <div class="label">Total No Realizadas</div>
            <div class="value">{{ $estadisticas['total_clases_no_realizadas'] }}</div>
        </div>
        <div class="stat-box danger">
            <div class="label">Sin Justificar</div>
            <div class="value">{{ $estadisticas['total_no_realizadas'] }}</div>
        </div>
        <div class="stat-box success">
            <div class="label">Justificadas</div>
            <div class="value">{{ $estadisticas['total_justificadas'] }}</div>
        </div>
        <div class="stat-box warning">
            <div class="label">Recuperadas</div>
            <div class="value">{{ $estadisticas['total_recuperadas'] }}</div>
        </div>
        <div class="stat-box info">
            <div class="label">Profesores</div>
            <div class="value">{{ $estadisticas['profesores_afectados'] }}</div>
        </div>
        <div class="stat-box {{ $estadisticas['porcentaje_no_realizadas'] > 5 ? 'danger' : 'success' }}">
            <div class="label">% No Realizadas</div>
            <div class="value">{{ $estadisticas['porcentaje_no_realizadas'] }}%</div>
        </div>
    </div>

    @if(count($profesores) > 0)
        <!-- Resumen General por Profesor -->
        <div class="section-title">RESUMEN POR PROFESOR</div>
        <table class="resumen-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Profesor</th>
                    <th style="width: 12%;">RUN</th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 12%;">No Realizadas</th>
                    <th style="width: 12%;">Justificadas</th>
                    <th style="width: 12%;">Recuperadas</th>
                    <th style="width: 12%;">% Cumplimiento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profesores as $profesor)
                    <tr>
                        <td><strong>{{ $profesor['profesor'] }}</strong></td>
                        <td>{{ $profesor['run'] }}</td>
                        <td style="text-align: center;"><strong>{{ $profesor['total_ausencias'] }}</strong></td>
                        <td style="text-align: center;">{{ $profesor['no_realizadas'] }}</td>
                        <td style="text-align: center;">{{ $profesor['justificadas'] }}</td>
                        <td style="text-align: center;">{{ $profesor['recuperadas'] }}</td>
                        <td style="text-align: center;">
                            <strong style="color: {{ $profesor['porcentaje_cumplimiento'] >= 90 ? '#10B981' : ($profesor['porcentaje_cumplimiento'] >= 70 ? '#F59E0B' : '#DC2626') }}">
                                {{ number_format($profesor['porcentaje_cumplimiento'], 1) }}%
                            </strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Detalle por Profesor -->
        <div class="section-title">DETALLE DE CLASES NO REALIZADAS</div>
        @foreach($profesores as $profesor)
            <div class="profesor-section">
                <div class="profesor-header">
                    <div>
                        <h3>{{ $profesor['profesor'] }}</h3>
                        <div class="info">RUN: {{ $profesor['run'] }}</div>
                    </div>
                </div>

                <div class="profesor-stats">
                    <div class="stat">
                        <span class="label">Total Ausencias</span>
                        <span class="value">{{ $profesor['total_ausencias'] }}</span>
                    </div>
                    <div class="stat">
                        <span class="label">No Realizadas</span>
                        <span class="value" style="color: #DC2626;">{{ $profesor['no_realizadas'] }}</span>
                    </div>
                    <div class="stat">
                        <span class="label">Justificadas</span>
                        <span class="value" style="color: #10B981;">{{ $profesor['justificadas'] }}</span>
                    </div>
                    <div class="stat">
                        <span class="label">Recuperadas</span>
                        <span class="value" style="color: #F59E0B;">{{ $profesor['recuperadas'] }}</span>
                    </div>
                    <div class="stat">
                        <span class="label">% Cumplimiento</span>
                        <span class="value" style="color: {{ $profesor['porcentaje_cumplimiento'] >= 90 ? '#10B981' : '#DC2626' }};">
                            {{ number_format($profesor['porcentaje_cumplimiento'], 1) }}%
                        </span>
                    </div>
                </div>

                <table class="detalle">
                    <thead>
                        <tr>
                            <th style="width: 9%;">Fecha</th>
                            <th style="width: 9%;">Día</th>
                            <th style="width: 22%;">Asignatura</th>
                            <th style="width: 8%;">Espacio</th>
                            <th style="width: 7%;">Módulo</th>
                            <th style="width: 10%;">Estado</th>
                            <th style="width: 9%;">Recuperada</th>
                            <th style="width: 9%;">Justificada</th>
                            <th style="width: 17%;">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profesor['clases'] as $clase)
                            <tr>
                                <td>{{ $clase['fecha'] }}</td>
                                <td>{{ ucfirst(substr($clase['dia_semana'], 0, 3)) }}</td>
                                <td>
                                    <strong>{{ $clase['asignatura'] }}</strong><br>
                                    <small style="color: #6B7280;">{{ $clase['codigo_asignatura'] }}</small>
                                </td>
                                <td>{{ $clase['espacio'] }}</td>
                                <td style="text-align: center;">{{ $clase['modulo'] }}</td>
                                <td>
                                    <span class="estado-badge estado-{{ $clase['estado'] === 'no_realizada' ? 'no-realizada' : 'justificado' }}">
                                        {{ $clase['estado'] === 'no_realizada' ? 'No Realizada' : 'Justificado' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-{{ $clase['recuperada'] === 'Sí' ? 'si' : 'no' }}">
                                        {{ $clase['recuperada'] }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-{{ $clase['justificada'] === 'Sí' ? 'si' : 'no' }}">
                                        {{ $clase['justificada'] }}
                                    </span>
                                </td>
                                <td style="font-size: 7px;">
                                    {{ strlen($clase['observaciones']) > 50 ? substr($clase['observaciones'], 0, 50) . '...' : $clase['observaciones'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div class="no-data">
            <p>✓ No se registraron clases no realizadas durante este mes</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Gestor de Aulas IT</p>
        <p>Documento confidencial - Uso interno exclusivo | {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
