<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Asistencia</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .badge-status {
            display: inline-block;
            margin-top: 12px;
            background-color: #10b981;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 24px;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #cbd5e1;
            font-size: 13px;
        }
        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registro de Asistencia Confirmado</h1>
            <p>SIA &bull; Sistema Integrado Académico</p>
            <div class="badge-status">&#10003; PRESENTE</div>
        </div>

        <div class="content">
            <div class="greeting">
                Hola, {{ $estudiante->nombre }}
            </div>
            <p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 20px;">
                Te confirmamos que tu presencia ha sido registrada exitosamente mediante credencial QR para la siguiente sesión de clase:
            </p>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Asignatura:</span>
                    <span class="info-value">{{ $nombreClase }}</span>
                </div>
                @if($sala && $sala !== 'N/A')
                <div class="info-row">
                    <span class="info-label">Espacio / Sala:</span>
                    <span class="info-value">{{ $sala }}</span>
                </div>
                @endif
                @if($docente)
                <div class="info-row">
                    <span class="info-label">Docente:</span>
                    <span class="info-value">{{ $docente->name }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value">{{ $fechaRegistro }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hora de marcación:</span>
                    <span class="info-value">{{ $horaRegistro }} hrs.</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estudiante RUN:</span>
                    <span class="info-value">{{ $estudiante->run }}</span>
                </div>
            </div>

            <p style="font-size: 11px; color: #64748b; line-height: 1.4;">
                Este comprobante ha sido generado automáticamente por el sistema de control de asistencia SIA. Si tienes dudas respecto a esta marcación, comunícate directamente con tu docente de cátedra.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} SIA &bull; Universidad Católica de la Santísima Concepción
        </div>
    </div>
</body>
</html>
