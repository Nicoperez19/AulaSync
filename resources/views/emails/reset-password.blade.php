<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - AulaSync</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #e74c3c;
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #c0392b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">AulaSync</div>
            <h2>Restablecer Contraseña</h2>
        </div>

        <div class="content">
            <p>Hola,</p>
            
            <p>Has recibido este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta en AulaSync.</p>
            
            <p>Para restablecer tu contraseña, haz clic en el botón de abajo:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
            </div>
            
            <p>Si no solicitaste un restablecimiento de contraseña, no es necesario que hagas nada.</p>
            
            <div class="warning">
                <strong>Importante:</strong> Este enlace expirará en 60 minutos por razones de seguridad.
            </div>
            
            <p>Si tienes problemas para hacer clic en el botón "Restablecer Contraseña", copia y pega la siguiente URL en tu navegador web:</p>
            
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                {{ $resetUrl }}
            </p>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} AulaSync. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
