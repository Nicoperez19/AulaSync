<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .info-card {
            background-color: #f8f7ff;
            border-left: 4px solid #4F46E5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            color: #4F46E5;
            width: 40%;
        }
        .info-value {
            color: #374151;
            width: 60%;
            text-align: right;
        }
        .badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>📌 Confirmación de Reserva</h1>
            <p>SIA | Sistema de Información de Aulas</p>
        </div>

        <div class="body">
            <p class="greeting">
                Estimado/a <strong>{{ $nombreUsuario }}</strong>,
            </p>

            <p>Se ha registrado una nueva reserva a su nombre. A continuación encontrará el resumen:</p>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">📋 N° Reserva</span>
                    <span class="info-value"><strong>{{ $idReserva }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Fecha</span>
                    <span class="info-value">{{ $fechaReserva }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕒 Hora de inicio</span>
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
                @if($nombreAsignatura)
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

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $urlComprobante ?? url('/reservas/' . $idReserva . '/comprobante') }}"
                   style="background-color: #4F46E5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block;">
                    📄 Descargar Comprobante Oficial (PDF)
                </a>
                <p style="font-size: 11px; color: #6b7280; margin-top: 8px;">
                    También encontrará el archivo PDF adjunto a este correo electrónico.
                </p>
            </div>

            <p>Si tiene alguna consulta, contacte con administración.</p>
            <p>Saludos cordiales,<br><strong>Equipo SIA | Sistema de Información de Aulas</strong></p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
            <p>&copy; {{ date('Y') }} <strong>SIA | Sistema de Información de Aulas</strong></p>
        </div>
    </div>
</body>
</html>
