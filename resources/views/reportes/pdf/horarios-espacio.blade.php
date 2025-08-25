<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario del Espacio</title>
    <style>
        /* Reset básico y tipografía */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 6mm;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Cabecera limpia con logo y título */
        .header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img { height: 64px; }

        .title {
            flex: 1;
        }

        .title h1 { margin: 0; font-size: 20px; color: #0f172a; }
        .title p { margin: 4px 0 0 0; color: #6b7280; font-size: 12px; }

        .meta {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        /* Información del espacio */
        .espacio-info {
            background: #f8fafc;
            border: 1px solid #e6eef6;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .espacio-info h2 {
            margin: 0 0 8px 0;
            font-size: 16px;
            color: #0f172a;
        }

        .espacio-details {
            display: flex;
            gap: 24px;
            font-size: 12px;
        }

        .espacio-details span {
            color: #475569;
        }

        .espacio-details strong {
            color: #334155;
        }

        /* Tabla moderna */
        .table-wrap { 
            width: 100%; 
            overflow-x: auto; 
        }
        
        table {
            min-width: 100%;
            border-collapse: separate; 
            border-spacing: 0;
            margin-top: 4px;
            font-size: 9px;
            table-layout: fixed;
            page-break-inside: auto;
        }

        tr { page-break-inside: avoid; page-break-after: auto; }

        thead th {
            background-color: #d3081d;
            color: #fff;
            padding: 8px 6px;
            text-align: center;
            font-weight: 700;
            vertical-align: middle;
            font-size: 11px;
            white-space: nowrap;
            border: 1px solid #b91c1c;
        }

        tbody td {
            padding: 6px 4px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            white-space: normal;
            word-wrap: break-word;
            font-size: 8px;
            line-height: 1.2;
        }

        tbody tr:nth-child(even) td { 
            background: #f9fafb; 
        }

        /* Estilos para las celdas de horarios */
        .hora-cell {
            background-color: #f3f4f6;
            font-weight: 600;
            text-align: center;
            color: #374151;
            width: 80px;
        }

        .asignatura-block {
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: 500;
            padding: 4px 6px;
            border-radius: 3px;
            margin-bottom: 2px;
            display: block;
            text-align: center;
            font-size: 7px;
        }

        .asignatura-nombre {
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .asignatura-detalle {
            font-size: 6px;
            color: #475569;
        }

        .libre-cell {
            background-color: #dcfce7;
            color: #166534;
            text-align: center;
            font-weight: 500;
            font-style: italic;
            font-size: 8px;
        }

        /* Footer */
        .footer {
            margin-top: 22px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e6eef6;
            padding-top: 12px;
        }

        /* Ajustes de impresión (dompdf compatible) */
        @page { margin: 0mm }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">
                @php $logoPath = public_path('images/logo_instituto_tecnologico-01.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo Instituto Tecnológico">
                @else
                    <div style="width:64px;height:64px;border-radius:6px;background:#0b5e6f;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">UCS</div>
                @endif
                <div class="title">
                    <h1>Horario del Espacio</h1>
                    <p>Sistema AulaSync - Instituto Tecnológico</p>
                </div>
            </div>
            <div class="meta">
                <div>Fecha: {{ $fecha }}</div>
                <div>Generado: {{ $fecha_generacion }}</div>
            </div>
        </div>

        <div class="espacio-info">
            <h2>Espacio: {{ $espacio->nombre_espacio ?? 'N/A' }}</h2>
            <div class="espacio-details">
                <div><strong>Código:</strong> <span>{{ $espacio->id_espacio ?? 'N/A' }}</span></div>
                <div><strong>Tipo:</strong> <span>{{ $espacio->tipo_espacio ?? 'N/A' }}</span></div>
                <div><strong>Piso:</strong> <span>{{ $espacio->piso->numero_piso ?? 'N/A' }}</span></div>
                <div><strong>Facultad:</strong> <span>{{ $espacio->piso->facultad->nombre_facultad ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="table-wrap">
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
                    @forelse($datos as $fila)
                        <tr>
                            <td class="hora-cell">
                                <strong>{{ $fila['hora'] }}</strong>
                            </td>
                            <td>
                                @if($fila['LU'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['LU'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($fila['MA'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['MA'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($fila['MI'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['MI'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($fila['JU'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['JU'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($fila['VI'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['VI'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($fila['SA'] === null)
                                    <span class="libre-cell">-</span>
                                @else
                                    @foreach($fila['SA'] as $asignatura)
                                        <div class="asignatura-block">
                                            <div class="asignatura-nombre">{{ $asignatura['asignatura'] }}</div>
                                            <div class="asignatura-detalle">🏢 {{ $asignatura['espacio'] }}</div>
                                            <div class="asignatura-detalle"># {{ $asignatura['codigo'] }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #7f8c8d;">
                                No se encontraron horarios para este espacio
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div>Este reporte fue generado automáticamente por el Sistema AulaSync</div>
            <div>Página 1 de 1</div>
        </div>
    </div>
</body>
</html>