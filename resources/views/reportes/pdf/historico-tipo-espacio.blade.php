<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico por Tipo de Espacio</title>
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
        
        .info-filtros {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-filtros strong {
            color: #495057;
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
        
        .estado-activa {
            background-color: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-finalizada {
            background-color: #7f8c8d;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-cancelada {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .estado-en-progreso {
            background-color: #3498db;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .tipo-profesor {
            background-color: #3498db;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .tipo-estudiante {
            background-color: #2ecc71;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .tipo-solicitante {
            background-color: #9b59b6;
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
        <h1>Histórico por Tipo de Espacio</h1>
        <p>Sistema AulaSync - Instituto Tecnológico</p>
        <p>Período: {{ $fecha_inicio }} - {{ $fecha_fin }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>


    <table>
        <thead>
            <tr>
                <th>Profesor/Solicitante</th>
                <th>Espacio</th>
                <th>Fecha</th>
                <th>Hora Entrada</th>
                <th>Hora Salida</th>
                <th>Duración</th>
                <th>Tipo Usuario</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $registro)
                <tr>
                    <td>
                        <div>{{ $registro['profesor_solicitante'] }}</div>
                        <div style="font-size: 9px; color: #7f8c8d;">{{ $registro['run'] }}</div>
                        <div style="font-size: 9px; color: #7f8c8d;">{{ $registro['email'] }}</div>
                    </td>
                    <td>
                        <div>{{ $registro['espacio'] }}</div>
                        <div style="font-size: 9px; color: #7f8c8d;">{{ $registro['facultad'] }}</div>
                    </td>
                    <td>{{ $registro['fecha'] }}</td>
                    <td>{{ $registro['hora_inicio'] }}</td>
                    <td>{{ $registro['hora_termino'] }}</td>
                    <td>{{ $registro['duracion'] }}</td>
                    <td>
                        <span class="tipo-{{ strtolower($registro['tipo_usuario']) }}">
                            {{ $registro['tipo_usuario'] }}
                        </span>
                    </td>
                    <td>
                        <span class="estado-{{ strtolower(str_replace(' ', '-', $registro['estado'])) }}">
                            {{ $registro['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron registros
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
