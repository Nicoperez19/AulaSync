<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cumplimiento de Clases por Escuela</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #D12420;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #1e293b;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            color: #64748b;
            font-size: 10px;
        }
        .kpis-container {
            width: 100%;
            margin-bottom: 15px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .kpi-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .kpi-green { color: #10b981; }
        .kpi-red { color: #d12420; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            padding: 6px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #334155;
        }
        table.data-table td {
            padding: 5px;
            border: 1px solid #e2e8f0;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table tr.total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        table.data-table tr.total-row td {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8.5px;
        }
        .badge-green { background-color: #d1fae5; color: #065f46; }
        .badge-amber { background-color: #fef3c7; color: #92400e; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AULASYNC - CONTROL DE GESTIÓN ACADÉMICA</h1>
        <p><strong>Reporte de Cumplimiento de Clases por Escuela</strong></p>
        <p>Período: <strong>{{ $periodo }}</strong> | Desde: <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</strong> Hasta: <strong>{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong> | Generado: <strong>{{ now()->format('d/m/Y H:i') }}</strong></p>
    </div>

    <!-- Resumen KPIs -->
    <div class="kpis-container">
        <table class="kpi-table">
            <tr>
                <td style="width: 25%; padding: 3px;">
                    <div class="kpi-box">
                        <div class="kpi-title">Total Clases Evaluadas</div>
                        <div class="kpi-value">{{ $totalGeneral }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding: 3px;">
                    <div class="kpi-box">
                        <div class="kpi-title">Clases Impartidas (Efectivas)</div>
                        <div class="kpi-value kpi-green">{{ $impartidasGeneral }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding: 3px;">
                    <div class="kpi-box">
                        <div class="kpi-title">Clases No Registradas</div>
                        <div class="kpi-value kpi-red">{{ $noRegistradasGeneral }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding: 3px;">
                    <div class="kpi-box">
                        <div class="kpi-title">% Cumplimiento General</div>
                        <div class="kpi-value {{ $pctGeneral >= 90 ? 'kpi-green' : ($pctGeneral >= 75 ? 'color: #d97706;' : 'kpi-red') }}">
                            {{ $pctGeneral }}%
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla Resumen por Escuela -->
    <h3 style="font-size: 11px; margin: 12px 0 4px 0; color: #0f172a;">Resumen Comparativo por Escuela de la Sede</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 32%;">Escuela</th>
                <th style="width: 10%; text-align: center;">Total Clases</th>
                <th style="width: 10%; text-align: center;">Impartidas</th>
                <th style="width: 10%; text-align: center;">Realizadas Norm.</th>
                <th style="width: 8%; text-align: center;">Recup.</th>
                <th style="width: 8%; text-align: center;">Justif.</th>
                <th style="width: 10%; text-align: center;">No Registradas</th>
                <th style="width: 12%; text-align: center;">% Cumplimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($escuelasData as $escuela)
                @php
                    $pct = $escuela['pct'] ?? 0;
                    $badgeClass = $pct >= 90 ? 'badge-green' : ($pct >= 75 ? 'badge-amber' : 'badge-red');
                @endphp
                <tr>
                    <td><strong>{{ $escuela['nombre'] }}</strong></td>
                    <td style="text-align: center;">{{ $escuela['total'] }}</td>
                    <td style="text-align: center; color: #059669; font-weight: bold;">{{ $escuela['impartidas'] }}</td>
                    <td style="text-align: center;">{{ $escuela['realizadas'] }}</td>
                    <td style="text-align: center;">{{ $escuela['recuperadas'] }}</td>
                    <td style="text-align: center;">{{ $escuela['justificadas'] }}</td>
                    <td style="text-align: center; color: #b91c1c; font-weight: bold;">{{ $escuela['no_registradas'] }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $badgeClass }}">{{ $pct }}%</span>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL GENERAL SEDE</td>
                <td style="text-align: center;">{{ $totalGeneral }}</td>
                <td style="text-align: center; color: #059669;">{{ $impartidasGeneral }}</td>
                <td style="text-align: center;">{{ $realizadasGeneral }}</td>
                <td style="text-align: center;">{{ $recuperadasGeneral }}</td>
                <td style="text-align: center;">{{ $justificadasGeneral }}</td>
                <td style="text-align: center; color: #b91c1c;">{{ $noRegistradasGeneral }}</td>
                <td style="text-align: center;">{{ $pctGeneral }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        AulaSync - Sistema Integral de Control Docente y Gestión de Espacios | Documento Oficial de Reporte Académico
    </div>
</body>
</html>
