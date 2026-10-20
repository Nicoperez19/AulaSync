<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Auditoría - Clases y Justificaciones</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 16mm 12mm 16mm;
        }
        body, div, table, thead, tbody, tr, th, td, h1, h2, p, span {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #1e293b;
            background-color: #ffffff;
            padding: 14px 20px 10px 20px;
        }
        .header {
            margin-bottom: 10px;
            border-bottom: 3px solid #D2091E;
            padding-bottom: 6px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo {
            width: 25%;
            vertical-align: middle;
        }
        .header-logo img {
            max-height: 40px;
            width: auto;
            display: block;
        }
        .header-title {
            width: 50%;
            text-align: center;
            vertical-align: middle;
        }
        .header-title h1 {
            font-size: 15px;
            color: #D2091E;
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .header-title p {
            font-size: 9px;
            color: #475569;
            font-weight: 600;
        }
        .header-meta {
            width: 25%;
            text-align: right;
            vertical-align: middle;
            font-size: 8px;
            color: #334155;
            line-height: 1.4;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 8px;
            text-align: left;
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4.5px 4px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }
        .badge-justificar { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-registrar { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-reagendar { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-eliminar { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-otro { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if(file_exists(public_path('images/Logo-UCSC-Color-Horizontal.png')))
                        <img src="{{ public_path('images/Logo-UCSC-Color-Horizontal.png') }}" alt="Logo UCSC">
                    @else
                        <span style="font-weight: bold; color: #D2091E; font-size: 14px;">UCSC</span>
                    @endif
                </td>
                <td class="header-title">
                    <h1>SIA &bull; REGISTRO DE AUDITORÍA DE CLASES</h1>
                    <p>Trazabilidad Inmutable de Justificaciones y Cambios de Estado</p>
                </td>
                <td class="header-meta">
                    <div><strong>Generado:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
                    <div><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</div>
                    <div><strong>Total Registros:</strong> {{ $registros->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 11%;">Fecha / Hora</th>
                <th style="width: 14%;">Usuario Responsable</th>
                <th style="width: 10%;">Acción</th>
                <th style="width: 16%;">Docente</th>
                <th style="width: 15%;">Asignatura / Espacio</th>
                <th style="width: 12%;">Fecha Clase / Mód.</th>
                <th style="width: 10%;">Estado (Ant. &rarr; Nvo)</th>
                <th style="width: 12%;">Motivo / Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $item)
                @php
                    $act = strtolower($item->accion);
                    $badge = 'badge-otro';
                    if (str_contains($act, 'justific')) $badge = 'badge-justificar';
                    elseif (str_contains($act, 'registra')) $badge = 'badge-registrar';
                    elseif (str_contains($act, 'reagend')) $badge = 'badge-reagendar';
                    elseif (str_contains($act, 'elimin')) $badge = 'badge-eliminar';
                @endphp
                <tr>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong>{{ $item->user_nombre }}</strong><br>
                        <span style="color: #64748b; font-size: 7px;">{{ $item->user_rol ?? 'Usuario' }} (IP: {{ $item->ip ?? 'N/A' }})</span>
                    </td>
                    <td>
                        <span class="badge {{ $badge }}">{{ str_replace('_', ' ', $item->accion) }}</span>
                    </td>
                    <td>
                        <strong>{{ $item->profesor->name ?? 'N/A' }}</strong><br>
                        <span style="color: #64748b;">RUN: {{ $item->run_profesor ?? 'N/A' }}</span>
                    </td>
                    <td>
                        {{ $item->asignatura->nombre_asignatura ?? ($item->id_asignatura ?? 'N/A') }}<br>
                        <span style="color: #64748b;">Espacio: {{ $item->id_espacio ?? 'N/A' }}</span>
                    </td>
                    <td>
                        {{ $item->fecha_clase ? $item->fecha_clase->format('d/m/Y') : 'N/A' }}<br>
                        <span style="color: #64748b;">Mód: {{ $item->id_modulo ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span style="color: #64748b;">{{ $item->estado_anterior ?? '—' }}</span> &rarr;
                        <strong>{{ $item->estado_nuevo ?? '—' }}</strong>
                    </td>
                    <td>
                        @if ($item->motivo)
                            <strong>Motivo:</strong> {{ $item->motivo }}<br>
                        @endif
                        @if ($item->observaciones)
                            <span style="color: #475569;">{{ Str::limit($item->observaciones, 80) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 15px; color: #94a3b8;">
                        No se registraron movimientos con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIA | Sistema de Información de Aulas &bull; Universidad Católica de la Santísima Concepción &bull; Registro oficial de auditoría emitido automáticamente.
    </div>
</body>
</html>
