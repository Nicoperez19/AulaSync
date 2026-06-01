<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Inconsistencias - SIA | Sistema de Informaci�n de Aulas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .alert-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .section {
            margin-bottom: 25px;
            padding: 15px;
            border-left: 4px solid #dc3545;
            background-color: #fff5f5;
        }
        .section h3 {
            color: #dc3545;
            margin-top: 0;
        }
        .inconsistencia-item {
            background-color: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #495057;
        }
        .stat-label {
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 12px;
        }
        .success {
            color: #28a745;
        }
        .warning {
            color: #ffc107;
        }
        .danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="alert-icon">🚨</span>
            <strong>Alerta de Inconsistencias - SIA | Sistema de Informaci�n de Aulas</strong>
            <br>
            <small>Fecha de verificación: {{ $datos['fecha_verificacion'] }}</small>
        </div>

        <h2>Se han detectado inconsistencias en el sistema</h2>
        <p>El sistema de verificación automática ha encontrado problemas que requieren atención inmediata.</p>

        @if(count($datos['espacios_ocupados_sin_reserva']) > 0)
        <div class="section">
            <h3>⚠️ Espacios Ocupados sin Reserva Activa</h3>
            <p><strong>{{ count($datos['espacios_ocupados_sin_reserva']) }}</strong> espacios están marcados como ocupados pero no tienen una reserva activa correspondiente.</p>
            @foreach($datos['espacios_ocupados_sin_reserva'] as $espacio)
            <div class="inconsistencia-item">
                <strong>{{ $espacio['id_espacio'] }}</strong>: {{ $espacio['nombre_espacio'] }}
                <br><small>Estado: {{ $espacio['estado'] }}</small>
            </div>
            @endforeach
        </div>
        @endif

        @if(count($datos['reservas_activas_espacios_disponibles']) > 0)
        <div class="section">
            <h3>⚠️ Reservas Activas en Espacios Disponibles</h3>
            <p><strong>{{ count($datos['reservas_activas_espacios_disponibles']) }}</strong> reservas están activas pero sus espacios están marcados como disponibles.</p>
            @foreach($datos['reservas_activas_espacios_disponibles'] as $reserva)
            <div class="inconsistencia-item">
                <strong>Reserva:</strong> {{ $reserva['id_reserva'] }}
                <br><strong>Espacio:</strong> {{ $reserva['espacio_id'] }} - {{ $reserva['espacio_nombre'] }}
                <br><strong>Usuario:</strong> {{ $reserva['tipo_usuario'] }} ({{ $reserva['usuario'] }})
                <br><strong>Fecha:</strong> {{ $reserva['fecha_reserva'] }} {{ $reserva['hora'] }}
            </div>
            @endforeach
        </div>
        @endif

        @if(count($datos['reservas_antiguas']) > 0)
        <div class="section">
            <h3>⚠️ Reservas Activas de Días Anteriores</h3>
            <p><strong>{{ count($datos['reservas_antiguas']) }}</strong> reservas siguen activas desde días anteriores y no se han finalizado.</p>
            @foreach($datos['reservas_antiguas'] as $reserva)
            <div class="inconsistencia-item">
                <strong>Reserva:</strong> {{ $reserva['id_reserva'] }}
                <br><strong>Espacio:</strong> {{ $reserva['espacio_id'] }} - {{ $reserva['espacio_nombre'] }}
                <br><strong>Usuario:</strong> {{ $reserva['tipo_usuario'] }} ({{ $reserva['usuario'] }})
                <br><strong>Fecha:</strong> {{ $reserva['fecha_reserva'] }} {{ $reserva['hora'] }}
                <br><small class="danger">⚠️ Reserva antigua que debería haberse finalizado</small>
            </div>
            @endforeach
        </div>
        @endif

        <h3>📊 Estadísticas del Sistema</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $datos['estadisticas']['total_espacios'] }}</div>
                <div class="stat-label">Total Espacios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $datos['estadisticas']['espacios_disponibles'] }}</div>
                <div class="stat-label">Espacios Disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $datos['estadisticas']['espacios_ocupados'] }}</div>
                <div class="stat-label">Espacios Ocupados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $datos['estadisticas']['reservas_activas'] }}</div>
                <div class="stat-label">Reservas Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $datos['estadisticas']['reservas_finalizadas'] }}</div>
                <div class="stat-label">Reservas Finalizadas</div>
            </div>
        </div>

        <h3>🔧 Acciones Recomendadas</h3>
        <ul>
            <li><strong>Revisar manualmente</strong> los espacios y reservas inconsistentes</li>
            <li><strong>Ejecutar comando de liberación:</strong> <code>php artisan espacios:liberar</code></li>
            <li><strong>Verificar estado después:</strong> <code>php artisan sistema:verificar-estado</code></li>
            <li><strong>Contactar al equipo técnico</strong> si las inconsistencias persisten</li>
        </ul>

        <div class="footer">
            <p>Este correo fue generado automáticamente por el sistema de monitoreo de SIA | Sistema de Informaci�n de Aulas.</p>
            <p>Para más información, revise los logs del sistema o contacte al administrador.</p>
        </div>
    </div>
</body>
</html>
