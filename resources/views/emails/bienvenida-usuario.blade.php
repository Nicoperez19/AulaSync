<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a AulaSync</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
            color: #ffffff;
            padding: 35px 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 0;
            font-size: 15px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 25px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 15px;
        }
        .intro-text {
            color: #4B5563;
            font-size: 15px;
            margin-bottom: 25px;
        }
        .card {
            background-color: #F8F7FF;
            border: 1px solid #E0E7FF;
            border-radius: 10px;
            padding: 20px 25px;
            margin-bottom: 25px;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #4F46E5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            border-bottom: 1px solid #E0E7FF;
            padding-bottom: 8px;
        }
        .credential-item {
            display: flex;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .credential-item:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            width: 140px;
            font-weight: 600;
            color: #6B7280;
        }
        .credential-value {
            color: #111827;
            font-weight: 500;
            word-break: break-all;
        }
        .badge {
            display: inline-block;
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .button-container {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #4F46E5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            transition: background-color 0.2s ease;
        }
        .security-notice {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 13px;
            color: #92400E;
            margin-bottom: 25px;
        }
        .footer {
            background-color: #F9FAFB;
            padding: 20px 25px;
            text-align: center;
            font-size: 13px;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AulaSync</h1>
            <p>Plataforma de GestiÃ³n AcadÃ©mica</p>
        </div>
        <div class="content">
            <div class="greeting">Â¡Hola, {{ $user->name }}!</div>
            <p class="intro-text">
                Se ha creado tu cuenta exitosamente en <strong>AulaSync</strong>. A continuaciÃ³n, te compartimos tus datos y credenciales para acceder a la plataforma:
            </p>

            <div class="card">
                <div class="card-title">Detalles de Acceso</div>
                
                <div class="credential-item">
                    <span class="credential-label">Correo ElectrÃ³nico:</span>
                    <span class="credential-value"><strong>{{ $user->email }}</strong></span>
                </div>

                @if($user->run)
                <div class="credential-item">
                    <span class="credential-label">RUN:</span>
                    <span class="credential-value">{{ $user->run }}</span>
                </div>
                @endif

                @if(!empty($password))
                <div class="credential-item">
                    <span class="credential-label">ContraseÃ±a:</span>
                    <span class="credential-value"><code style="background: #E5E7EB; padding: 2px 6px; border-radius: 4px;">{{ $password }}</code></span>
                </div>
                @endif

                @if(!empty($roleName))
                <div class="credential-item">
                    <span class="credential-label">Rol Asignado:</span>
                    <span class="credential-value"><span class="badge">{{ $roleName }}</span></span>
                </div>
                @endif
            </div>

            <div class="security-notice">
                <strong>RecomendaciÃ³n de seguridad:</strong> Te sugerimos cambiar tu contraseÃ±a una vez que ingreses por primera vez a la plataforma.
            </div>

            <div class="button-container">
                <a href="{{ $loginUrl }}" class="btn" target="_blank">Iniciar SesiÃ³n en AulaSync</a>
            </div>
        </div>
        <div class="footer">
            <p>Este es un correo automÃ¡tico enviado por el sistema AulaSync. Por favor, no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} AulaSync. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
