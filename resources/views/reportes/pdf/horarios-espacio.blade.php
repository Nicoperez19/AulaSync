<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Horarios por Espacio</title>
    <style>
        /* Reset básico y tipografía */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 6mm;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Cabecera limpia con logo y título */
        .header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img { height: 64px; }

        .title {
            flex: 1;
        }

        .title h1 { margin: 0; font-size: 20px; color: #0f172a; }
        .title p { margin: 4px 0 0 0; color: #6b7280; font-size: 12px; }

        .meta {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        /* Tarjeta de filtros */
        .filters-card {
            background: #f8fafc;
            border: 1px solid #e6eef6;
            padding: 6px 8px;
            border-radius: 6px;
            margin-top: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .filters-card .left { color: #334155; font-size: 13px; }
        .filters-card .right { color: #475569; font-size: 13px; }

        /* Tabla moderna */
    .table-wrap { width: 100%; overflow-x: auto; }
        table {
            min-width: 100%;
            border-collapse: separate; border-spacing: 0;
            margin-top: 4px;
            font-size: 9px; /* más compacto para caber más columnas */
            table-layout: fixed;
            page-break-inside: auto;
        }

        tr { page-break-inside: avoid; page-break-after: auto; }

        thead th {
            background-color: #d3081d; /* nuevo color de header */
            color: #fff;
            padding: 6px 6px;
            text-align: left;
            font-weight: 700; /* más gordita */
            vertical-align: middle;
            font-size: 13px; /* más grande */
            white-space: nowrap;
        }

        /* Encabezado adicional que abarca todas las columnas de módulos */
        thead .modules-header {
            background-color: #d3081d;
            color: #fff;
            text-align: center;
            font-size: 14px;
            padding: 8px 6px;
            font-weight: 700;
        }

        /* Números de módulos centrados */
        thead .module-number { text-align: center; }
        /* Celdas de módulo centradas */
        td.module-cell { text-align: center; }

        /* Texto vertical para las celdas de datos en las primeras columnas: usar writing-mode preferente y fallback */
        .vertical {
            display: inline-block;
            writing-mode: vertical-rl; /* preferible para PDF */
            -ms-writing-mode: tb-rl;
            text-orientation: mixed;
            /* fallback: rotar si writing-mode no funciona */
            transform: rotate(-90deg);
            transform-origin: center;
            /* permitir wrapping pero preferir romper por espacios (no dentro de palabras) */
            white-space: normal;
            word-break: normal;
            overflow-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 700;
            font-size: 9px; /* más compacto */
            max-width: 120px; /* ampliar para textos largos */
            max-height: 160px;
            padding: 2px 4px;
            line-height: 1;
        }

        /* Aplicar sólo a las celdas (td) que deben estar verticales */
        td.vertical-cell {
            width: 60px; /* aumentar ancho para que el texto rotado no se corte */
            padding: 2px 4px;
            vertical-align: middle;
            text-align: center;
            height: 140px; /* aumentar altura para permitir más líneas */
            overflow: hidden;
        }

        tbody td {
            padding: 4px 4px;
            border-bottom: 1px solid #eef2f6;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        tbody tr:nth-child(even) td { background: #fbfdff; }

        /* Badges para porcentajes */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 6px;
            color: #fff;
            font-weight: 600;
            font-size: 9px;
            min-width: 28px;
            text-align: center;
        }
        .badge.zer { background: #16a34a; } /* 0% */
        .badge.low { background: #f59e0b; } /* 1-40 */
        .badge.mid { background: #d97706; } /* 41-80 */
        .badge.high { background: #dc2626; } /* 81-100 */

        /* Footer */
        .footer {
            margin-top: 22px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e6eef6;
            padding-top: 12px;
        }

        .legend { display:flex; justify-content:center; gap:12px; margin: 14px 0; }
        .legend span { display:inline-block; padding:6px 10px; border-radius:6px; color:#fff; font-weight:700; font-size:11px }
        .legend .l0 { background:#16a34a }
        .legend .l1 { background:#f59e0b }
        .legend .l2 { background:#d97706 }
        .legend .l3 { background:#dc2626 }

    /* Ajustes de impresión (dompdf compatible) */
    @page { margin: 0mm }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">
                @php $logoPath = public_path('images/logo_instituto_tecnologico-01.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo Instituto Tecnológico">
                @else
                    <div style="width:64px;height:64px;border-radius:6px;background:#0b5e6f;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">UCS</div>
                @endif
                <div class="title">
                    <h1>Análisis de Horarios por Espacio</h1>
                    <p>Sistema AulaSync - Instituto Tecnológico</p>
                </div>
            </div>
            <div class="meta">
                <div>Fecha: {{ $fecha }}</div>
                <div>Generado: {{ $fecha_generacion }}</div>
            </div>
        </div>

        <div class="filters-card">
            <div class="left">
                <strong>Rango de Módulos:</strong> {{ $moduloInicio }} - {{ $moduloFin }}
            </div>
            <div class="right">
                <strong>Total de Módulos:</strong> {{ $modulosDia }}
            </div>
        </div>

        <div class="table-wrap">
            <table>
        <thead>
            <!-- Fila superior: título que abarca todas las columnas de módulos -->
            <tr>
                <th rowspan="2"><div>Espacio</div></th>
                <th rowspan="2"><div>Tipo</div></th>
                <th rowspan="2"><div>Piso</div></th>
                <th rowspan="2"><div>Facultad</div></th>
                <th class="modules-header" colspan="{{ $moduloFin - $moduloInicio + 1 }}">Módulo</th>
            </tr>
            <!-- Segunda fila: números de módulos -->
            <tr>
                @for ($i = $moduloInicio; $i <= $moduloFin; $i++)
                    <th class="module-number">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $fila)
                <tr>
                    <td class="vertical-cell"><div class="vertical">{{ $fila['espacio'] }}</div></td>
                        <td class="vertical-cell"><div class="vertical">{{ $fila['tipo'] }}</div></td>
                        <td class="vertical-cell"><div class="vertical">{{ $fila['piso'] }}</div></td>
                        <td class="vertical-cell"><div class="vertical">{{ $fila['facultad'] }}</div></td>
                    @for ($i = $moduloInicio; $i <= $moduloFin; $i++)
                        @php
                            $ocupacion = isset($fila['modulo_' . $i]) ? (int)str_replace('%', '', $fila['modulo_' . $i]) : 0;
                            if ($ocupacion == 0) {
                                $badgeClass = 'badge zer';
                            } elseif ($ocupacion <= 40) {
                                $badgeClass = 'badge low';
                            } elseif ($ocupacion <= 80) {
                                $badgeClass = 'badge mid';
                            } else {
                                $badgeClass = 'badge high';
                            }
                        @endphp
                        <td class="module-cell"><span class="{{ $badgeClass }}">{{ $ocupacion }}%</span></td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + ($moduloFin - $moduloInicio + 1) }}" style="text-align: center; padding: 20px; color: #7f8c8d;">
                        No se encontraron datos de horarios por espacio
                    </td>
                </tr>
            @endforelse
        </tbody>
            </table>
        </div>

        <div class="footer">
            <div><strong>Leyenda de Colores:</strong></div>
            <div class="legend" aria-hidden="true">
                <span class="l0">0%</span>
                <span class="l1">1-40%</span>
                <span class="l2">41-80%</span>
                <span class="l3">81-100%</span>
            </div>
            <div>Este reporte fue generado automáticamente por el Sistema AulaSync</div>
            <div>Página 1 de 1</div>
        </div>
    </div>
</body>
</html>