<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte por Tipo de Espacio</title>
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
            font-size: 11px;
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
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_instituto_tecnologico-01.png') }}" alt="Logo Instituto Tecnológico" style="height: 60px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
        <h1>Reporte por Tipo de Espacio</h1>
        <p>Sistema AulaSync - Instituto Tecnológico de Chile</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo de sala</th>
                <th>Nivel de utilización</th>
                <th>Comparativa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tiposEspacio as $tipo)
                <tr>
                    <td>{{ $tipo['nombre'] }}</td>
                    <td>{{ $tipo['utilizacion'] }}</td>
                    <td>{{ $tipo['comparativa'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay datos para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html> 