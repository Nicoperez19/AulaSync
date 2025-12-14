<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Horarios</title>
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
        
        .ocupacion-alta {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .ocupacion-media {
            background-color: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .ocupacion-baja {
            background-color: #27ae60;
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
        <h1>Análisis de Horarios</h1>
        <p>Gestor de Aulas IT - Instituto Tecnológico</p>
        <p>Fecha: {{ $fecha }}</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <div class="info-filtros">
        <strong>Rango de Módulos:</strong> {{ $moduloInicio }} - {{ $moduloFin }}<br>
        <strong>Total de Módulos:</strong> {{ $modulosDia }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Módulo</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Espacios Ocupados</th>
                <th>Total Espacios</th>
                <th>Porcentaje Ocupación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $modulo)
                <tr>
                    <td>{{ $modulo['modulo'] }}</td>
                    <td>{{ $modulo['hora_inicio'] }}</td>
                    <td>{{ $modulo['hora_fin'] }}</td>
                    <td>{{ $modulo['espacios_ocupados'] }}</td>
                    <td>{{ $modulo['total_espacios'] }}</td>
                    <td>{{ $modulo['porcentaje'] }}%</td>
                    <td>
                        <span class="ocupacion-{{ strtolower($modulo['estado']) }}">
                            {{ $modulo['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron datos de horarios
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Gestor de Aulas IT</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html> 