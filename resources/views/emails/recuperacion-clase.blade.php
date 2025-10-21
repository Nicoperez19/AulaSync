<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Recuperación de Clase</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4A90E2;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #4A90E2;
            border-radius: 3px;
        }
        .info-label {
            font-weight: bold;
            color: #4A90E2;
            display: inline-block;
            min-width: 150px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-reagendada {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>📅 Notificación de Recuperación de Clase</h2>
    </div>
    
    <div class="content">
        <p>Estimado/a <strong>{{ $recuperacion->profesor->name }}</strong>,</p>
        
        <p>Le informamos sobre una clase que debe ser recuperada:</p>
        
        <div class="info-box">
            <p><span class="info-label">📚 Asignatura:</span> {{ $recuperacion->asignatura->nombre_asignatura ?? 'N/A' }}</p>
            <p><span class="info-label">📅 Fecha Original:</span> {{ $recuperacion->fecha_clase_original->format('d/m/Y') }}</p>
            @if($recuperacion->moduloOriginal)
                <p><span class="info-label">🕐 Módulo Original:</span> {{ $recuperacion->moduloOriginal->nombre_modulo }}</p>
            @endif
            @if($recuperacion->espacio)
                <p><span class="info-label">🏫 Espacio:</span> {{ $recuperacion->espacio->nombre_espacio }}</p>
            @endif
        </div>

        @if($recuperacion->fecha_reagendada)
            <div class="info-box">
                <p><strong>✅ La clase ha sido reagendada:</strong></p>
                <p><span class="info-label">📅 Nueva Fecha:</span> {{ $recuperacion->fecha_reagendada->format('d/m/Y') }}</p>
                @if($recuperacion->moduloReagendado)
                    <p><span class="info-label">🕐 Nuevo Módulo:</span> {{ $recuperacion->moduloReagendado->nombre_modulo }}</p>
                @endif
                @if($recuperacion->espacioReagendado)
                    <p><span class="info-label">🏫 Nuevo Espacio:</span> {{ $recuperacion->espacioReagendado->nombre_espacio }}</p>
                @endif
            </div>
        @else
            <div class="info-box">
                <p><strong>⏳ Estado:</strong> <span class="status-badge status-pendiente">Pendiente de Reagendar</span></p>
                <p>La clase aún no ha sido reagendada. Se le notificará cuando se asigne una nueva fecha.</p>
            </div>
        @endif

        @if($recuperacion->notas)
            <div class="info-box">
                <p><span class="info-label">📝 Notas:</span></p>
                <p>{{ $recuperacion->notas }}</p>
            </div>
        @endif

        <p>Si tiene alguna consulta o requiere mayor información, por favor contacte con el departamento correspondiente.</p>
        
        <p>Atentamente,<br>
        <strong>Sistema de Gestión Académica</strong></p>
    </div>
    
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>© {{ date('Y') }} Sistema de Gestión Académica</p>
    </div>
</body>
</html>
