<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario de Espacio - {{ $espacio->id_espacio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #dc2626;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            margin-top: 5px;
            font-size: 14px;
        }
        .info-espacio {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
        .info-espacio h3 {
            margin: 0 0 10px 0;
            color: #dc2626;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 12px;
        }
        .info-item {
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            margin-right: 10px;
            min-width: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        th {
            background-color: #dc2626;
            color: white;
            padding: 8px;
            text-align: center;
            border: 1px solid #dc2626;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            min-height: 40px;
        }
        .hora-col {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 80px;
        }
        .clase {
            background-color: #fef3c7;
            border-radius: 4px;
            padding: 4px;
            margin: 2px;
            font-size: 9px;
            line-height: 1.2;
        }
        .clase.asignatura {
            font-weight: bold;
            color: #92400e;
        }
        .clase.profesor {
            color: #78350f;
            font-size: 8px;
        }
        .empty-cell {
            color: #999;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .no-horario {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-horario .icon {
            font-size: 48px;
            color: #dc2626;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Horario de Espacio</h1>
        <div class="subtitle">Sistema de Gestión de Espacios Académicos</div>
    </div>

    <div class="info-espacio">
        <h3>Información del Espacio</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID:</span>
                <span>{{ $espacio->id_espacio }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span>{{ $espacio->nombre_espacio }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo:</span>
                <span>{{ $espacio->tipo_espacio }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Capacidad:</span>
                <span>{{ $espacio->puestos_disponibles ?? 'N/A' }} personas</span>
            </div>
            <div class="info-item">
                <span class="info-label">Piso:</span>
                <span>{{ $espacio->piso->numero_piso ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Facultad:</span>
                <span>{{ $espacio->piso->facultad->nombre_facultad ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    @if(count($modulosUnicos) > 0)
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                    <th>Sábado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modulosUnicos as $modulo)
                    <tr>
                        <td class="hora-col">
                            {{ \Carbon\Carbon::parse($modulo['hora_inicio'])->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($modulo['hora_termino'])->format('H:i') }}
                        </td>
                        @foreach(['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'] as $dia)
                            @php
                                $clases = collect($horarios)->filter(function($h) use ($dia, $modulo) {
                                    return strtolower($h['dia']) === $dia && 
                                           $h['hora_inicio'] === $modulo['hora_inicio'] && 
                                           $h['hora_termino'] === $modulo['hora_termino'];
                                });
                            @endphp
                            <td>
                                @if($clases->count() > 0)
                                    @foreach($clases as $clase)
                                        <div class="clase asignatura">{{ $clase['asignatura'] }} ({{ $clase['codigo_asignatura'] }})</div>
                                        @if($clase['user'])
                                            <div class="clase profesor">{{ $clase['user']['name'] }}</div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="empty-cell">-</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-horario">
            <div class="icon">📅</div>
            <h3>No hay módulos horarios disponibles</h3>
            <p>No se encontraron módulos horarios configurados en el sistema.</p>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ $fecha_generacion }}</p>
        <p>Sistema AulaSync - Gestión de Espacios Académicos</p>
    </div>
</body>
</html> 