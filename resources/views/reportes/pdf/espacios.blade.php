<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Análisis de Espacios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
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
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #374151;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .estado-optimo {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .estado-medio {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .estado-bajo {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Análisis de Espacios</h1>
        <p>Período: {{ $periodo }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
        <p>Total de registros: {{ $total_registros }}</p>
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
            @foreach($datos as $espacio)
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
                        <span class="
                            @if($espacio['estado_utilizacion'] == 'Óptimo') estado-optimo
                            @elseif($espacio['estado_utilizacion'] == 'Medio uso') estado-medio
                            @else estado-bajo
                            @endif">
                            {{ $espacio['estado_utilizacion'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente por AulaSync</p>
        <p>Página 1</p>
    </div>
</body>
</html> 