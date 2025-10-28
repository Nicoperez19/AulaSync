<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Accesos Registrados</title>
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
        
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 5px;
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
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-estudiante {
            background-color: #2ecc71;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-administrativo {
            background-color: #9b59b6;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .type-externo {
            background-color: #95a5a6;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_instituto_tecnologico-01.png') }}" alt="Logo Instituto Tecnológico" style="height: 60px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
        <h1>Reporte de Accesos Registrados (QR)</h1>
        <p>Sistema AulaSync - Instituto Tecnológico</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>RUN</th>
                <th>Tipo</th>
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
                    <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron accesos registrados
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