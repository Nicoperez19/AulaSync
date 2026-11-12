<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Fallas e Incidencias en Espacios</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 14mm 12mm 14mm; }
        body, div, table, thead, tbody, tr, th, td, h1, h2, p, span { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #1e293b; background: #fff; padding: 10px 14px; }
        .header { margin-bottom: 10px; border-bottom: 3px solid #D2091E; padding-bottom: 6px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 25%; vertical-align: middle; }
        .header-logo img { max-height: 40px; width: auto; display: block; }
        .header-title { width: 50%; text-align: center; vertical-align: middle; }
        .header-title h1 { font-size: 15px; color: #D2091E; text-transform: uppercase; font-weight: 800; margin-bottom: 2px; letter-spacing: .5px; }
        .header-title p { font-size: 9px; color: #475569; font-weight: 600; }
        .header-meta { width: 25%; text-align: right; vertical-align: middle; font-size: 8px; color: #334155; line-height: 1.4; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 10px -6px; }
        .summary td { width: 20%; border: 1px solid #e2e8f0; background: #f8fafc; padding: 6px 8px; }
        .summary .n { font-size: 15px; font-weight: bold; color: #0f172a; }
        .summary .l { font-size: 7.5px; color: #64748b; text-transform: uppercase; }
        .filters { font-size: 8px; color: #475569; margin-bottom: 8px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th { background: #f1f5f9; color: #1e293b; font-weight: bold; font-size: 8px; text-align: left; padding: 5px 4px; border: 1px solid #cbd5e1; text-transform: uppercase; }
        .data-table td { padding: 4.5px 4px; border: 1px solid #e2e8f0; font-size: 7.5px; vertical-align: top; }
        .muted { color: #64748b; font-size: 7px; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-weight: bold; font-size: 7px; text-transform: uppercase; }
        .sev-baja { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .sev-media { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
        .sev-alta { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .sev-critica { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .est-abierta { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .est-en_proceso { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .est-resuelta { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .est-descartada { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .footer { margin-top: 8px; text-align: center; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if (file_exists(public_path('images/Logo-UCSC-Color-Horizontal.png')))
                        <img src="{{ public_path('images/Logo-UCSC-Color-Horizontal.png') }}" alt="Logo UCSC">
                    @else
                        <span style="font-weight: bold; color: #D2091E; font-size: 14px;">UCSC</span>
                    @endif
                </td>
                <td class="header-title">
                    <h1>SIA &bull; FALLAS E INCIDENCIAS EN ESPACIOS</h1>
                    <p>Trazabilidad de fallas y mantenimientos</p>
                </td>
                <td class="header-meta">
                    <div><strong>Generado:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
                    <div><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</div>
                    <div><strong>Total registros:</strong> {{ $incidencias->count() }}@if ($truncado) (se muestran los primeros {{ $limite }}) @endif</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary">
        <tr>
            <td><div class="n">{{ $resumen['abiertas'] }}</div><div class="l">Abiertas</div></td>
            <td><div class="n">{{ $resumen['en_proceso'] }}</div><div class="l">En proceso</div></td>
            <td><div class="n">{{ $resumen['resueltas'] }}</div><div class="l">Resueltas</div></td>
            <td><div class="n">{{ $resumen['descartadas'] }}</div><div class="l">Descartadas</div></td>
            <td><div class="n">{{ $resumen['bloqueantes'] }}</div><div class="l">Dejaron la sala fuera de servicio</div></td>
        </tr>
    </table>

    @if (!empty($descripcionFiltros))
        <div class="filters"><strong>Filtros aplicados:</strong> {{ $descripcionFiltros }}</div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 9%;">Espacio</th>
                <th style="width: 15%;">Tipo de falla</th>
                <th style="width: 24%;">Descripción</th>
                <th style="width: 8%;">Severidad</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 12%;">Reportó</th>
                <th style="width: 24%;">Resolución / cierre</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($incidencias as $i)
                <tr>
                    <td>
                        <strong>{{ $i->id_espacio }}</strong><br>
                        <span class="muted">{{ $i->espacio?->nombre_espacio ?? '—' }}</span>
                        @if ($i->bloquea_uso)
                            <br><span class="muted"><strong>Sala fuera de servicio</strong></span>
                        @endif
                    </td>
                    <td>{{ $i->categoria_label }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($i->descripcion, 220) }}</td>
                    <td><span class="badge sev-{{ $i->severidad }}">{{ $i->severidad_label }}</span></td>
                    <td><span class="badge est-{{ $i->estado }}">{{ $i->estado_label }}</span></td>
                    <td>
                        {{ $i->reportado_por_nombre ?? '—' }}<br>
                        <span class="muted">{{ $i->reportado_en?->format('d/m/Y H:i') }}</span>
                    </td>
                    <td>
                        @if ($i->resuelto_en)
                            {{ \Illuminate\Support\Str::limit($i->resolucion, 200) }}<br>
                            <span class="muted">{{ $i->resuelto_por_nombre ?? '—' }} · {{ $i->resuelto_en->format('d/m/Y H:i') }} · {{ $i->horas_abierta }} h</span>
                        @else
                            <span class="muted">Pendiente · abierta hace {{ $i->horas_abierta }} h</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 15px; color: #94a3b8;">
                        No hay incidencias con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIA | Sistema de Información de Aulas &bull; Universidad Católica de la Santísima Concepción &bull; Reporte emitido automáticamente.
    </div>
</body>
</html>
