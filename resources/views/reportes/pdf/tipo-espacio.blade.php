<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis por Tipo de Espacio</title>
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
        
        .estado-medio-uso {
            background-color: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-bajo-uso {
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
        <h1>Análisis por Tipo de Espacio</h1>
        <p>Sistema AulaSync - Instituto Tecnológico</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo de Espacio</th>
                <th>Total Espacios</th>
                <th>Total Reservas</th>
                <th>Horas Utilizadas</th>
                <th>Promedio de Uso</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $tipo)
                <tr>
                    <td>{{ $tipo['nombre'] }}</td>
                    <td>{{ $tipo['total_espacios'] }}</td>
                    <td>{{ $tipo['total_reservas'] }}</td>
                    <td>{{ $tipo['horas_utilizadas'] }}h</td>
                    <td>{{ $tipo['promedio'] }}%</td>
                    <td>
                        <span class="estado-{{ strtolower(str_replace(' ', '-', $tipo['estado'])) }}">
                            {{ $tipo['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron tipos de espacio registrados
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