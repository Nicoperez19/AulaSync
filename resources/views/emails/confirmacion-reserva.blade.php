<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 28px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .body {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 18px;
        }
        .info-card {
            background-color: #f8f7ff;
            border-left: 4px solid #4F46E5;
            border-radius: 4px;
            padding: 18px 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            padding: 7px 0;
            border-bottom: 1px solid #e8e7f8;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 700;
            color: #4F46E5;
            min-width: 160px;
        }
        .info-value {
            color: #374151;
        }
        .badge {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .footer strong {
            color: #4F46E5;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>✅ Reserva Confirmada</h1>
            <p>AulaSync – Sistema de Gestión de Espacios</p>
        </div>

        <div class="body">
            <p class="greeting">
                Estimado/a <strong>{{ $nombreUsuario }}</strong>,
            </p>
            <p>Su reserva ha sido registrada exitosamente. A continuación encontrará el detalle:</p>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">📋 N° Reserva</span>
                    <span class="info-value">{{ $idReserva }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Fecha</span>
                    <span class="info-value">{{ $fechaReserva }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕐 Hora de inicio</span>
                    <span class="info-value">{{ $horaReserva }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🏫 Espacio</span>
                    <span class="info-value">{{ $nombreEspacio }} ({{ $idEspacio }})</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🏷️ Tipo de espacio</span>
                    <span class="info-value">{{ $tipoEspacio }}</span>
                </div>
                @if ($nombreAsignatura)
                <div class="info-row">
                    <span class="info-label">📚 Asignatura</span>
                    <span class="info-value">{{ $nombreAsignatura }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value"><span class="badge">Activa</span></span>
                </div>
            </div>

            <p>Recuerde devolver el espacio una vez finalizado su uso para que otros puedan utilizarlo.</p>
            <p>Si tiene alguna consulta, contacte con administración.</p>
            <p>Saludos cordiales,<br><strong>Equipo AulaSync</strong></p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
            <p>© {{ date('Y') }} <strong>AulaSync</strong> – Sistema de Gestión de Espacios Académicos</p>
        </div>
    </div>
</body>
</html>
