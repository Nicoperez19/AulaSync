<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ocupación Semanal - SIA</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 12mm 8mm 12mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1e293b;
            background-color: #ffffff;
        }
        .header {
            margin-bottom: 8px;
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
            max-height: 42px;
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
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-title p {
            font-size: 9.5px;
            color: #475569;
            font-weight: 600;
        }
        .header-meta {
            width: 25%;
            text-align: right;
            vertical-align: middle;
            font-size: 8px;
            color: #334155;
            line-height: 1.35;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7.5px;
        }
        .badge-filtro {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .legend-bar {
            margin-bottom: 6px;
            font-size: 8px;
            text-align: right;
        }
        .legend-item {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            margin-left: 6px;
            font-weight: bold;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .grid-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 8.5px;
            text-align: center;
            padding: 5px 3px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .grid-table td {
            text-align: center;
            padding: 4.5px 2px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .mod-col {
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
            width: 16%;
            text-align: left !important;
            padding-left: 6px !important;
            font-size: 8.5px;
        }
        .val-low {
            background-color: #ecfdf5;
            color: #065f46;
            font-weight: bold;
        }
        .val-mid {
            background-color: #fffbeb;
            color: #92400e;
            font-weight: bold;
        }
        .val-high {
            background-color: #fef2f2;
            color: #991b1b;
            font-weight: bold;
        }
        .val-na {
            background-color: #f8fafc;
            color: #94a3b8;
            font-style: italic;
        }
        .prom-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #94a3b8;
            padding: 5px 2px;
            font-size: 9px;
        }

        .cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 6px;
        }
        .card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
            vertical-align: top;
        }
        .card-label {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .card-value {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }
        .card-value.highlight {
            color: #D2091E;
        }

        .footer {
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
                    <h1>SIA &bull; OCUPACIÓN SEMANAL</h1>
                    <p>Reporte Oficial de Ocupación por Módulo y Día</p>
                </td>
                <td class="header-meta">
                    <div><strong>Período:</strong> {{ $datos['periodo'] ?? 'N/A' }}</div>
                    <div><strong>Semana:</strong> {{ $datos['semana_inicio'] ?? '' }} al {{ $datos['semana_fin'] ?? '' }} (Sem. {{ $datos['semana_iso'] ?? '' }})</div>
                    <div><strong>Filtro:</strong> <span class="badge badge-filtro">{{ strtoupper(str_replace('_', ' ', $datos['tipo_filtro'] ?? 'TODOS')) }}</span></div>
                    <div><strong>Espacios Evaluados:</strong> {{ $datos['total_espacios'] ?? 0 }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="legend-bar">
        <span style="font-weight: bold; color: #475569; margin-right: 4px;">Nivel de Demanda:</span>
        <span class="legend-item val-low" style="border: 1px solid #a7f3d0;">Baja: 0% - 35%</span>
        <span class="legend-item val-mid" style="border: 1px solid #fde68a;">Media: 36% - 75%</span>
        <span class="legend-item val-high" style="border: 1px solid #fecaca;">Alta: 76% - 100%</span>
        <span class="legend-item val-na" style="border: 1px solid #e2e8f0;">N/A: Sin Datos</span>
    </div>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 16%;">Módulo / Horario</th>
                <th style="width: 14%;">Lunes</th>
                <th style="width: 14%;">Martes</th>
                <th style="width: 14%;">Miércoles</th>
                <th style="width: 14%;">Jueves</th>
                <th style="width: 14%;">Viernes</th>
                <th style="width: 14%;">Sábado</th>
            </tr>
        </thead>
        <tbody>
            @for ($m = 1; $m <= 15; $m++)
                @php
                    $horario = \App\Helpers\ModulosHelper::getHorarioModulo('lunes', $m);
                    $ini = substr($horario['inicio'] ?? '00:00', 0, 5);
                    $fin = substr($horario['fin'] ?? '00:00', 0, 5);
                @endphp
                <tr>
                    <td class="mod-col">
                        <strong>Mód. {{ $m }}</strong> <span style="color: #64748b; font-size: 8px; font-weight: normal;">({{ $ini }} - {{ $fin }})</span>
                    </td>
                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                        @php
                            $val = $datos['ocupacion'][$dia][$m] ?? null;
                            if ($val === null) {
                                $class = 'val-na';
                                $text = 'N/A';
                            } else {
                                $num = (int)$val;
                                if ($num <= 35) {
                                    $class = 'val-low';
                                } elseif ($num <= 75) {
                                    $class = 'val-mid';
                                } else {
                                    $class = 'val-high';
                                }
                                $text = $num . '%';
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $text }}</td>
                    @endforeach
                </tr>
            @endfor
            <tr class="prom-row">
                <td style="text-align: left; padding-left: 6px;">PROMEDIO DÍA</td>
                @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                    @php
                        $promDia = $datos['resumen']['promedios_por_dia'][$dia] ?? 0;
                    @endphp
                    <td>{{ $promDia }}%</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <table class="cards-table">
        <tr>
            <td class="card" style="width: 25%;">
                <div class="card-label">Promedio General Semanal</div>
                <div class="card-value highlight">{{ $datos['resumen']['promedio_general'] ?? 0 }}%</div>
            </td>
            <td class="card" style="width: 25%;">
                <div class="card-label">Pico Máximo de Ocupación</div>
                <div class="card-value" style="color: #b91c1c;">{{ $datos['resumen']['pico_maximo'] ?? 0 }}%</div>
            </td>
            <td class="card" style="width: 25%;">
                <div class="card-label">Día y Módulo Pico</div>
                <div class="card-value" style="font-size: 10px; padding-top: 2px;">{{ ucfirst($datos['resumen']['dia_pico'] ?? 'N/A') }} &bull; Mód. {{ $datos['resumen']['modulo_pico'] ?? 'N/A' }}</div>
            </td>
            <td class="card" style="width: 25%;">
                <div class="card-label">Generado Por</div>
                <div class="card-value" style="font-size: 9px; font-weight: 600; padding-top: 3px; color: #475569;">
                    {{ auth()->user()->name ?? 'Sistema SIA' }} &bull; {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        SIA | Sistema de Información de Aulas &bull; Universidad Católica de la Santísima Concepción &bull; Información sujeta a planificación académica y reservas registradas.
    </div>
</body>
</html>
