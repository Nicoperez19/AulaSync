<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocupación por Horarios - {{ $fecha }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 16px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 8px 4px;
            text-align: center;
            border: 1px solid #1d4ed8;
            font-weight: bold;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .espacio-cell {
            text-align: left;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .tipo-cell, .piso-cell, .facultad-cell {
            text-align: center;
            background-color: #f1f5f9;
        }
        .ocupacion-0 {
            background-color: #dcfce7;
            color: #166534;
        }
        .ocupacion-1-40 {
            background-color: #fef3c7;
            color: #92400e;
        }
        .ocupacion-41-80 {
            background-color: #fed7aa;
            color: #c2410c;
        }
        .ocupacion-81-100 {
            background-color: #fecaca;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ocupación por Horarios</h1>
        <p><strong>Fecha:</strong> {{ $fecha }}</p>
        <p><strong>Módulos:</strong> {{ $moduloInicio }} - {{ $moduloFin }}</p>
        <p><strong>Generado:</strong> {{ $fecha_generacion }}</p>
    </div>

    <div class="info-section">
        <h3>Información del Reporte</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Total de Espacios:</span>
                <span class="info-value">{{ $total_registros }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rango de Módulos:</span>
                <span class="info-value">{{ $moduloInicio }} - {{ $moduloFin }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha de Generación:</span>
                <span class="info-value">{{ $fecha_generacion }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Período:</span>
                <span class="info-value">{{ $fecha }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Espacio</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 5%;">Piso</th>
                <th style="width: 12%;">Facultad</th>
                @for ($i = $moduloInicio; $i <= $moduloFin; $i++)
                    <th style="width: {{ 60 / ($moduloFin - $moduloInicio + 1) }}%;">
                        Módulo {{ $i }}<br>
                        <small>({{ $modulosDia[$i - 1] }})</small>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $fila)
                <tr>
                    <td class="espacio-cell">{{ $fila['espacio'] }}</td>
                    <td class="tipo-cell">{{ $fila['tipo'] }}</td>
                    <td class="piso-cell">{{ $fila['piso'] }}</td>
                    <td class="facultad-cell">{{ $fila['facultad'] }}</td>
                    @for ($i = $moduloInicio; $i <= $moduloFin; $i++)
                        @php
                            $ocupacion = isset($fila['modulo_' . $i]) ? (int)str_replace('%', '', $fila['modulo_' . $i]) : 0;
                            $clase = $ocupacion == 0 ? 'ocupacion-0' : 
                                   ($ocupacion <= 40 ? 'ocupacion-1-40' : 
                                   ($ocupacion <= 80 ? 'ocupacion-41-80' : 'ocupacion-81-100'));
                        @endphp
                        <td class="{{ $clase }}">{{ $ocupacion }}%</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Leyenda de Colores:</strong></p>
        <p>
            <span style="background-color: #dcfce7; color: #166534; padding: 2px 6px; margin-right: 10px;">0%</span>
            <span style="background-color: #fef3c7; color: #92400e; padding: 2px 6px; margin-right: 10px;">1-40%</span>
            <span style="background-color: #fed7aa; color: #c2410c; padding: 2px 6px; margin-right: 10px;">41-80%</span>
            <span style="background-color: #fecaca; color: #991b1b; padding: 2px 6px;">81-100%</span>
        </p>
        <p>Sistema de Gestión de Espacios - Instituto Tecnológico</p>
    </div>
</body>
</html> 