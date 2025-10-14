<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $asunto }}</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .email-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }
        .email-content {
            margin: 30px 0;
            font-size: 15px;
            line-height: 1.8;
        }
        .email-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        /* Estilos para el contenido HTML personalizado */
        .email-content h1 {
            color: #1f2937;
            font-size: 24px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .email-content h2 {
            color: #374151;
            font-size: 20px;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .email-content h3 {
            color: #4b5563;
            font-size: 18px;
            margin-top: 16px;
            margin-bottom: 8px;
        }
        .email-content p {
            margin: 10px 0;
        }
        .email-content ul, .email-content ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        .email-content li {
            margin: 5px 0;
        }
        .email-content a {
            color: #4F46E5;
            text-decoration: none;
        }
        .email-content a:hover {
            text-decoration: underline;
        }
        .email-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .email-content table td,
        .email-content table th {
            padding: 10px;
            border: 1px solid #e5e7eb;
        }
        .email-content table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .email-content blockquote {
            border-left: 4px solid #4F46E5;
            padding-left: 15px;
            margin: 15px 0;
            color: #4b5563;
            font-style: italic;
        }
        .email-content strong {
            font-weight: 600;
            color: #1f2937;
        }
        .email-content em {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">AulaSync</div>
            <div style="color: #6b7280; font-size: 14px;">Sistema de Gestión Académica</div>
        </div>

        <!-- Saludo -->
        @if($nombreDestinatario)
            <div class="greeting">
                Hola, {{ $nombreDestinatario }}
            </div>
        @endif

        <!-- Contenido HTML Personalizado -->
        <div class="email-content">
            {!! $contenidoHtml !!}
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 5px 0;">
                <strong>AulaSync</strong> - Sistema de Gestión Académica
            </p>
            <p style="margin: 5px 0; color: #9ca3af; font-size: 12px;">
                Este es un correo automático, por favor no responder directamente a este mensaje.
            </p>
            <p style="margin: 10px 0; color: #9ca3af; font-size: 11px;">
                © {{ date('Y') }} AulaSync. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
