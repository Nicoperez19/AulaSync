<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Reservas de Espacios</title>
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
        <h1>Histórico de Reservas de Espacios</h1>
        <p>Sistema AulaSync - Instituto Tecnológico</p>
        <p>Período: {{ $fecha_inicio }} - {{ $fecha_fin }}</p>
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
                <th>Fecha</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Espacio</th>
                <th>Tipo</th>
                <th>Piso</th>
                <th>Facultad</th>
                <th>Profesor/Solicitante</th>
                <th>Tipo Usuario</th>
                <th>Horas</th>
                <th>Duración</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $reserva)
                <tr>
                    <td>{{ $reserva['fecha'] }}</td>
                    <td>{{ $reserva['hora_inicio'] }}</td>
                    <td>{{ $reserva['hora_fin'] }}</td>
                    <td>{{ $reserva['espacio'] }}</td>
                    <td>{{ $reserva['tipo_espacio'] }}</td>
                    <td>{{ $reserva['piso'] }}</td>
                    <td>{{ $reserva['facultad'] }}</td>
                    <td>{{ $reserva['usuario'] }}</td>
                    <td>
                        <span class="tipo-{{ strtolower($reserva['tipo_usuario']) }}">
                            {{ $reserva['tipo_usuario'] }}
                        </span>
                    </td>
                    <td>{{ $reserva['horas_utilizadas'] }}h</td>
                    <td>{{ $reserva['duracion'] }}</td>
                    <td>
                        <span class="estado-{{ strtolower(str_replace(' ', '-', $reserva['estado'])) }}">
                            {{ $reserva['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron reservas registradas
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
