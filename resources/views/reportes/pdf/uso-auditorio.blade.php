<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Uso del Auditorio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 22px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 12px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            width: 25%;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 9px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #fdfdfd;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-activa { background-color: #d1fae5; color: #065f46; }
        .status-finalizada { background-color: #f3f4f6; color: #374151; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #bdc3c7;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Uso del Auditorio</h1>
        <p>Instituto Tecnológico - Sistema SIA | Sistema de Informaci�n de Aulas</p>
        <p>Período: {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number">{{ $total_reservas }}</div>
            <div class="stat-label">Total Eventos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $completadas }}</div>
            <div class="stat-label">Finalizadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $activas }}</div>
            <div class="stat-label">En Curso</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ count($datos->unique('espacio')) }}</div>
            <div class="stat-label">Espacios Usados</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Auditorio</th>
                <th>Fecha</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Duración</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $reg)
                <tr>
                    <td>
                        <strong>{{ $reg['profesor_solicitante'] }}</strong><br>
                        <small style="color: #7f8c8d;">{{ $reg['tipo_usuario'] }}</small>
                    </td>
                    <td>{{ $reg['espacio'] }}</td>
                    <td>{{ $reg['fecha'] }}</td>
                    <td>{{ $reg['hora_inicio'] }}</td>
                    <td>{{ $reg['hora_termino'] }}</td>
                    <td>{{ $reg['duracion'] }}</td>
                    <td>
                        <span class="status-badge status-{{ $reg['estado'] }}">
                            {{ $reg['estado'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado digitalmente. La información contenida es de uso institucional.</p>
    </div>
</body>
</html>
