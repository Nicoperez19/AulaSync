<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprobante de Reserva - {{ $id_reserva }}</title>
    <style>
        @page {
            margin: 0px 0px 20px 0px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* ===== ENCABEZADO INSTITUCIONAL ===== */
        .header-band {
            width: 100%;
            background-color: #D2091E;
            border-collapse: collapse;
            padding: 0;
            margin: 0;
        }

        .header-inner {
            padding: 10px 22px 10px 22px;
        }

        .header-left {
            width: 55%;
            vertical-align: middle;
        }

        .header-right {
            width: 45%;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-row {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: auto;
            vertical-align: middle;
            padding-right: 12px;
        }

        .logo-cell img {
            max-height: 92px;
            max-width: 140px;
            object-fit: contain;
        }

        .system-name {
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
            line-height: 1.3;
            letter-spacing: 0.3px;
        }

        .system-inst {
            font-size: 8.5px;
            color: rgba(255,255,255,0.80);
            margin-top: 3px;
            font-weight: 400;
        }

        .voucher-label {
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 5px;
        }

        .folio-box {
            display: inline-block;
            background-color: #ffffff;
            border-radius: 4px;
            padding: 3px 9px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            font-weight: bold;
            color: #D2091E;
            letter-spacing: 0.5px;
        }

        /* ===== CONTENIDO ===== */
        .content-wrapper {
            padding: 16px 22px;
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
            border-left: 4px solid #002F6C;
            color: #002F6C;
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
            background-color: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            border-left: 3px solid #D2091E;
            padding: 6px 12px;
            font-size: 10.5px;
            font-weight: bold;
            color: #002F6C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-content {
            padding: 9px 12px;
            background-color: #ffffff;
        }

        /* Tablas Clave - Valor */
        .kv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv-table td {
            padding: 3px 4px;
            vertical-align: middle;
        }

        .kv-label {
            width: 33%;
            font-weight: 600;
            color: #475569;
            font-size: 9.5px;
        }

        .kv-value {
            width: 67%;
            color: #0f172a;
            font-weight: 500;
            font-size: 10.5px;
        }

        .highlight-text {
            color: #002F6C;
            font-weight: bold;
        }

        /* Badge de espacio - alineado con texto */
        .espacio-row {
            width: 100%;
            border-collapse: collapse;
        }

        .espacio-badge {
            display: inline-block;
            background-color: #D2091E;
            color: #ffffff;
            font-weight: bold;
            font-size: 10.5px;
            padding: 2px 8px;
            border-radius: 4px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .espacio-nombre {
            font-size: 10px;
            color: #475569;
            vertical-align: middle;
            padding-left: 5px;
        }

        /* Bloque inferior: QR + Instrucciones */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .qr-cell {
            width: 115px;
            text-align: center;
            vertical-align: top;
            padding-right: 12px;
        }

        .qr-image {
            width: 95px;
            height: 95px;
            border: 1px solid #cbd5e1;
            padding: 3px;
            border-radius: 4px;
            background-color: #ffffff;
        }

        .qr-caption {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.3;
        }

        .terms-cell {
            vertical-align: top;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-left: 3px solid #002F6C;
            border-radius: 6px;
            padding: 9px 12px;
        }

        .terms-title {
            font-weight: bold;
            font-size: 9px;
            color: #002F6C;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .terms-list {
            margin: 0;
            padding-left: 14px;
            font-size: 8px;
            color: #64748b;
            line-height: 1.4;
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
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<!-- ===== ENCABEZADO INSTITUCIONAL (Fondo Rojo UCSC) ===== -->
<table class="header-band" cellpadding="0" cellspacing="0">
    <tr>
        <td class="header-inner">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- LOGO + NOMBRE SISTEMA -->
                    <td class="header-left">
                        <table class="header-logo-row">
                            <tr>
                                @if(!empty($logo_base64))
                                <td class="logo-cell">
                                    <img src="{{ $logo_base64 }}" alt="Logo Institucional">
                                </td>
                                @endif
                                <td style="vertical-align: middle;">
                                    <div class="system-name">SIA &nbsp;| &nbsp;Sistema de Información de Aulas</div>
                                    <div class="system-inst">{{ $institucion }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <!-- TÍTULO COMPROBANTE + FOLIO -->
                    <td class="header-right">
                        <div class="voucher-label">Comprobante de Reserva</div>
                        <div>
                            <span class="folio-box">{{ $id_reserva }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ===== CONTENIDO ===== -->
<div class="content-wrapper">

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
                                    <td class="kv-value" style="word-break: break-all; font-size: 9.5px;">{{ $email_responsable }}</td>
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
                                    <td class="kv-label" style="vertical-align: middle;">Espacio:</td>
                                    <td class="kv-value" style="vertical-align: middle;">
                                        <table style="border-collapse: collapse; width: 100%;">
                                            <tr>
                                                <td style="padding: 0; vertical-align: middle; width: auto;">
                                                    <span class="espacio-badge">{{ $id_espacio }}</span>
                                                </td>
                                                @if($nombre_espacio !== $id_espacio)
                                                <td style="padding: 0 0 0 5px; vertical-align: middle;">
                                                    <span class="espacio-nombre">{{ $nombre_espacio }}</span>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>
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
                                    <td class="kv-value" style="font-size: 9.5px;">{{ $institucion }}</td>
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
                                    <td class="kv-value">
                                        <strong style="color: #002F6C;">{{ $hora_inicio }}</strong>
                                        <span style="color: #64748b;"> a </span>
                                        <strong style="color: #002F6C;">{{ $hora_fin }}</strong>
                                        <span style="color: #64748b;"> hrs.</span>
                                    </td>
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
                                    <td class="kv-value" style="font-size: 9px;">{{ $descripcion_actividad }}</td>
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
                <div style="margin-top: 6px; padding-top: 5px; border-top: 1px dashed #e2e8f0; font-size: 8.5px; color: #475569;">
                    <strong style="color: #002F6C;">Observaciones:</strong> {{ $observaciones }}
                </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- QR de Validación -->
    @if(!empty($qr_base64))
    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="text-align: center; padding: 6px 0;">
                <img src="{{ $qr_base64 }}" style="width: 80px; height: 80px; border: 1px solid #cbd5e1; padding: 3px; border-radius: 4px; background: #fff;" alt="QR">
                <div style="font-size: 7.5px; color: #64748b; margin-top: 3px;">
                    Código de Validación &nbsp;|&nbsp; <strong style="color: #002F6C;">{{ $id_reserva }}</strong>
                </div>
            </td>
        </tr>
    </table>
    @endif

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
