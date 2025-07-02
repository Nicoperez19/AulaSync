<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Espacios - AulaSync</title>
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
            padding-bottom: 10px;
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
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 16px;
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
            font-size: 10px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
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
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 10px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Histórico de Uso de Espacios</h1>
        <p>AulaSync - Sistema de Gestión de Espacios Académicos</p>
        <p>Período: {{ $periodo }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $total_registros }}</div>
            <div class="stat-label">Total de Registros</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $datos->unique('espacio')->count() }}</div>
            <div class="stat-label">Espacios Utilizados</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $datos->unique('usuario')->count() }}</div>
            <div class="stat-label">Usuarios Únicos</div>
        </div>
    </div>

    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Espacio</th>
                    <th>Usuario</th>
                    <th>Tipo Usuario</th>
                    <th>Horas</th>
                    <th>Duración</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos as $registro)
                    <tr>
                        <td>{{ $registro['fecha'] }}</td>
                        <td>{{ $registro['hora_inicio'] }}</td>
                        <td>{{ $registro['hora_fin'] }}</td>
                        <td>{{ $registro['espacio'] }}</td>
                        <td>{{ $registro['usuario'] }}</td>
                        <td>{{ $registro['tipo_usuario'] }}</td>
                        <td>{{ $registro['horas_utilizadas'] }} h</td>
                        <td>{{ $registro['duracion'] }}</td>
                        <td>{{ $registro['estado'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="info-section">
            <p>No hay registros de uso para mostrar en el período seleccionado.</p>
        </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema AulaSync</p>
        <p>Para más información, contacte al administrador del sistema</p>
    </div>
</body>
</html> 