<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Accesos Registrados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
        }
        
        .header p {
            margin: 4px 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        
        th {
            background-color: #34495e;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .status-active {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-finished {
            color: #7f8c8d;
        }
        
        .type-profesor {
            background-color: #3498db;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-estudiante {
            background-color: #2ecc71;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-administrativo {
            background-color: #9b59b6;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-externo {
            background-color: #95a5a6;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class=header>
        <h1>Reporte de Accesos Registrados</h1>
        <p>AulaSync | Sistema de Información de Aulas</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>RUN</th>
                <th>Tipo</th>
                <th>UA</th>
                <th>Asignatura</th>
                <th>ID Sala</th>
                <th>Piso</th>
                <th>Fecha</th>
                <th>Hora Entrada</th>
                <th>Hora Salida</th>
                <th>Duración</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accesos as $acceso)
                <tr>
                    <td>{{ $acceso['id'] }}</td>
                    <td>{{ $acceso['usuario'] }}</td>
                    <td>{{ $acceso['run'] }}</td>
                    <td>
                        <span class="type-{{ $acceso['tipo_usuario'] }}">
                            {{ ucfirst($acceso['tipo_usuario']) }}
                        </span>
                    </td>
                    <td>{{ $acceso['ua'] ?? 'N/A' }}</td>
                    <td>{{ $acceso['asignatura'] ?? 'N/A' }}</td>
                    <td>{{ $acceso['id_espacio'] }}</td>
                    <td>{{ $acceso['piso'] }}</td>
                    <td>{{ $acceso['fecha'] }}</td>
                    <td>{{ $acceso['hora_entrada'] }}</td>
                    <td>{{ $acceso['hora_salida'] }}</td>
                    <td>{{ $acceso['duracion'] }}</td>
                    <td class="{{ $acceso['estado'] == 'activa' ? 'status-active' : 'status-finished' }}">
                        {{ ucfirst($acceso['estado']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron accesos registrados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por AulaSync</p>
    </div>
</body>
</html>