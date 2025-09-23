<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Espacios</title>
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
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        
        .filtros {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .filtros h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
        }
        
        .filtros span {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 3px;
            margin-right: 5px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .estado-optimo {
            background-color: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-medio {
            background-color: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-bajo {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_instituto_tecnologico-01.png') }}" alt="Logo Instituto Tecnológico" style="height: 60px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
        <h1>Análisis de Espacios</h1>
        <p>Sistema AulaSync - Instituto Tecnológico</p>
        <p>Período: {{ $periodo }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    @if($filtros_aplicados['tipo_espacio'] || $filtros_aplicados['piso'] || $filtros_aplicados['estado'] || $filtros_aplicados['busqueda'])
        <div class="filtros">
            <h3>Filtros Aplicados:</h3>
            @if($filtros_aplicados['busqueda'])
                <span>Búsqueda: "{{ $filtros_aplicados['busqueda'] }}"</span>
            @endif
            @if($filtros_aplicados['tipo_espacio'])
                <span>Tipo: {{ $filtros_aplicados['tipo_espacio'] }}</span>
            @endif
            @if($filtros_aplicados['piso'])
                <span>Piso: {{ $filtros_aplicados['piso'] }}</span>
            @endif
            @if($filtros_aplicados['estado'])
                <span>Estado: {{ $filtros_aplicados['estado'] }}</span>
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Piso</th>
                <th>Facultad</th>
                <th>Estado</th>
                <th>Puestos</th>
                <th>Reservas</th>
                <th>Horas</th>
                <th>Utilización</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $espacio)
                <tr>
                    <td>{{ $espacio['id_espacio'] }}</td>
                    <td>{{ $espacio['nombre'] }}</td>
                    <td>{{ $espacio['tipo_espacio'] }}</td>
                    <td>{{ $espacio['piso'] }}</td>
                    <td>{{ $espacio['facultad'] }}</td>
                    <td>{{ $espacio['estado'] }}</td>
                    <td>{{ $espacio['puestos_disponibles'] }}</td>
                    <td>{{ $espacio['total_reservas'] }}</td>
                    <td>{{ $espacio['horas_utilizadas'] }}h</td>
                    <td>{{ $espacio['promedio_utilizacion'] }}</td>
                    <td>
                        <span class="estado-{{ strtolower(str_replace(' ', '-', $espacio['estado_utilizacion'])) }}">
                            {{ $espacio['estado_utilizacion'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron espacios registrados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

        <!-- Reporte de Salas Menos Ocupadas -->
        <div class="page-break"></div>
        <div class="header">
            <h1>Salas Menos Ocupadas</h1>
            <p>Solo se muestran las salas con menor porcentaje de ocupación</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Piso</th>
                    <th>Facultad</th>
                    <th>Estado</th>
                    <th>Puestos</th>
                    <th>Reservas</th>
                    <th>Horas</th>
                    <th>Utilización</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Filtrar las salas menos ocupadas (por ejemplo, las del 25% inferior de ocupación)
                    $umbral = 0.25; // 25% inferior
                    $total = count($datos);
                    $ordenados = collect($datos)->sortBy('promedio_utilizacion')->values();
                    $cantidad = ceil($total * $umbral);
                    $menos_ocupadas = $ordenados->take($cantidad);
                @endphp
                @forelse($menos_ocupadas as $espacio)
                    <tr>
                        <td>{{ $espacio['id_espacio'] }}</td>
                        <td>{{ $espacio['nombre'] }}</td>
                        <td>{{ $espacio['tipo_espacio'] }}</td>
                        <td>{{ $espacio['piso'] }}</td>
                        <td>{{ $espacio['facultad'] }}</td>
                        <td>{{ $espacio['estado'] }}</td>
                        <td>{{ $espacio['puestos_disponibles'] }}</td>
                        <td>{{ $espacio['total_reservas'] }}</td>
                        <td>{{ $espacio['horas_utilizadas'] }}h</td>
                        <td>{{ $espacio['promedio_utilizacion'] }}</td>
                        <td>
                            <span class="estado-{{ strtolower(str_replace(' ', '-', $espacio['estado_utilizacion'])) }}">
                                {{ $espacio['estado_utilizacion'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
                            No se encontraron salas menos ocupadas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Sistema AulaSync</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html> 