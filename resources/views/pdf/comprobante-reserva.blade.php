<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprobante de Reserva - {{ $id_reserva }}</title>
    <style>
        @page {
            margin: 22px 28px 22px 28px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Contenedor Principal */
        .voucher-container {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #ffffff;
            padding: 18px 22px;
        }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .institute-logo {
            max-height: 52px;
            max-width: 200px;
            object-fit: contain;
        }

        .system-badge-title {
            font-size: 17px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            line-height: 1.1;
        }

        .system-badge-sub {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .voucher-badge {
            text-align: right;
            vertical-align: middle;
        }

        .voucher-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .folio-box {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: bold;
            color: #1d4ed8;
        }

        /* Banner de Estado */
        .status-banner {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .status-banner td {
            padding: 7px 12px;
            border-radius: 6px;
        }

        .status-activa {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .status-programada {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }

        .status-finalizada {
            background-color: #f8fafc;
            border-left: 4px solid #64748b;
            color: #334155;
        }

        /* Secciones de Información */
        .section-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .section-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 12px;
            font-size: 10.5px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-content {
            padding: 9px 12px;
            background-color: #ffffff;
        }

        /* Tablas de pares Clave - Valor */
        .kv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv-table td {
            padding: 3.5px 4px;
            vertical-align: top;
        }

        .kv-label {
            width: 32%;
            font-weight: 600;
            color: #475569;
            font-size: 10px;
        }

        .kv-value {
            width: 68%;
            color: #0f172a;
            font-weight: 500;
            font-size: 10.5px;
        }

        .highlight-text {
            color: #1e40af;
            font-weight: bold;
        }

        .espacio-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            font-size: 11.5px;
            padding: 2px 7px;
            border-radius: 4px;
        }

        /* Bloque inferior: QR + Instrucciones */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .qr-cell {
            width: 120px;
            text-align: center;
            vertical-align: top;
            padding-right: 14px;
        }

        .qr-image {
            width: 100px;
            height: 100px;
            border: 1px solid #cbd5e1;
            padding: 3px;
            border-radius: 4px;
            background-color: #ffffff;
        }

        .qr-caption {
            font-size: 8px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.2;
        }

        .terms-cell {
            vertical-align: top;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 9px 12px;
        }

        .terms-title {
            font-weight: bold;
            font-size: 9.5px;
            color: #334155;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .terms-list {
            margin: 0;
            padding-left: 14px;
            font-size: 8.5px;
            color: #64748b;
            line-height: 1.35;
        }

        .terms-list li {
            margin-bottom: 2px;
        }

        /* Pie de página */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="voucher-container">

    <!-- Encabezado con Membrete y Logo Institucional -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @if(!empty($logo_base64))
                        <td style="width: 90px; vertical-align: middle; padding-right: 12px;">
                            <img src="{{ $logo_base64 }}" class="institute-logo" alt="Logo Institucional">
                        </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <div class="system-badge-title">SIA</div>
                            <div class="system-badge-sub">Sistema de Información de Aulas</div>
                            <div style="font-size: 9px; color: #334155; font-weight: bold; margin-top: 2px;">{{ $institucion }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="voucher-badge" style="width: 45%; vertical-align: middle;">
                <div class="voucher-title">Comprobante de Reserva</div>
                <div>Folio: <span class="folio-box">{{ $id_reserva }}</span></div>
            </td>
        </tr>
    </table>

    <!-- Estado de la Reserva -->
    <table class="status-banner">
        <tr>
            <td class="status-{{ strtolower($reserva->estado ?? 'activa') }}">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 60%; font-size: 10.5px;">
                            <strong>Estado:</strong> {{ $estado }} &nbsp;|&nbsp; <strong>Tipo:</strong> {{ $tipo_reserva }}
                        </td>
                        <td style="width: 40%; text-align: right; font-size: 9px;">
                            <strong>Emisión:</strong> {{ $fecha_emision }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Grid de Dos Columnas: Responsable y Espacio -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <!-- Columna 1: Responsable -->
            <td style="width: 49%; vertical-align: top;">
                <table class="section-table">
                    <tr>
                        <td class="section-header">1. Datos del Responsable</td>
                    </tr>
                    <tr>
                        <td class="section-content">
                            <table class="kv-table">
                                <tr>
                                    <td class="kv-label">Nombre:</td>
                                    <td class="kv-value highlight-text">{{ $nombre_responsable }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">RUN / RUT:</td>
                                    <td class="kv-value">{{ $run_responsable }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Tipo:</td>
                                    <td class="kv-value">{{ $tipo_responsable }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Correo:</td>
                                    <td class="kv-value" style="word-break: break-all;">{{ $email_responsable }}</td>
                                </tr>
                                @if($telefono_responsable && $telefono_responsable !== 'No registrado')
                                <tr>
                                    <td class="kv-label">Teléfono:</td>
                                    <td class="kv-value">{{ $telefono_responsable }}</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 2%;"></td>

            <!-- Columna 2: Espacio Reservado -->
            <td style="width: 49%; vertical-align: top;">
                <table class="section-table">
                    <tr>
                        <td class="section-header">2. Espacio Asignado</td>
                    </tr>
                    <tr>
                        <td class="section-content">
                            <table class="kv-table">
                                <tr>
                                    <td class="kv-label">Espacio:</td>
                                    <td class="kv-value">
                                        <span class="espacio-badge">{{ $id_espacio }}</span>
                                        @if($nombre_espacio !== $id_espacio)
                                            <span style="font-size: 9.5px; color: #475569;"> - {{ $nombre_espacio }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Tipo Espacio:</td>
                                    <td class="kv-value">{{ $tipo_espacio }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Ubicación:</td>
                                    <td class="kv-value">{{ $piso }} &bull; {{ $facultad }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Capacidad:</td>
                                    <td class="kv-value">{{ $capacidad }} personas</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Sede / Campus:</td>
                                    <td class="kv-value">{{ $institucion }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Horario y Detalles de la Actividad -->
    <table class="section-table">
        <tr>
            <td class="section-header">3. Horario y Detalles de la Reserva</td>
        </tr>
        <tr>
            <td class="section-content">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <table class="kv-table">
                                <tr>
                                    <td class="kv-label">Fecha Reserva:</td>
                                    <td class="kv-value highlight-text">{{ $fecha }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Horario:</td>
                                    <td class="kv-value"><strong>{{ $hora_inicio }}</strong> a <strong>{{ $hora_fin }}</strong> hrs.</td>
                                </tr>
                                <tr>
                                    <td class="kv-label">Módulos:</td>
                                    <td class="kv-value">{{ $modulos }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <table class="kv-table">
                                <tr>
                                    <td class="kv-label">Actividad:</td>
                                    <td class="kv-value">{{ $actividad }}</td>
                                </tr>
                                @if($descripcion_actividad)
                                <tr>
                                    <td class="kv-label">Descripción:</td>
                                    <td class="kv-value" style="font-size: 9.5px;">{{ $descripcion_actividad }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="kv-label">Registrado por:</td>
                                    <td class="kv-value" style="font-size: 9.5px;">{{ $creado_por }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                @if($observaciones)
                <div style="margin-top: 6px; padding-top: 5px; border-top: 1px dashed #e2e8f0; font-size: 9px; color: #475569;">
                    <strong>Observaciones:</strong> {{ $observaciones }}
                </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- QR de Validación y Normativa -->
    <table class="bottom-table">
        <tr>
            @if(!empty($qr_base64))
            <td class="qr-cell">
                <img src="{{ $qr_base64 }}" class="qr-image" alt="Código QR de Validación">
                <div class="qr-caption">
                    Código de Validación<br>
                    <strong>ID: {{ $id_reserva }}</strong>
                </div>
            </td>
            @endif

            <td class="terms-cell">
                <div class="terms-title">Normas de Uso y Condiciones del Espacio</div>
                <ul class="terms-list">
                    <li>Presente este comprobante impreso o digital en portería/conserjería para solicitar la apertura del espacio o entrega de llaves.</li>
                    <li>El responsable debe estar presente durante el uso del recinto y velar por el correcto cuidado del mobiliario y equipamiento.</li>
                    <li>Al finalizar la actividad, el espacio debe entregarse ordenado, limpio, con luces y equipos apagados, y debidamente cerrado.</li>
                    <li>Cualquier daño, avería o anomalía debe ser reportada de inmediato al personal de administración.</li>
                </ul>
            </td>
        </tr>
    </table>

    <!-- Pie de Página -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;">
                Documento generado automáticamente por <strong>SIA (Sistema de Información de Aulas)</strong> &bull; {{ date('Y') }}
            </td>
            <td style="width: 40%; text-align: right;">
                Válido únicamente para la fecha y horario indicados
            </td>
        </tr>
    </table>

</div>

</body>
</html>
