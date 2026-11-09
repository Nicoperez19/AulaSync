<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de gestión de soporte</title>
    <style>
        @page { margin: 28px 30px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #243247; font-size: 9px; }
        .institution { width: 100%; border-bottom: 2px solid #c8102e; padding-bottom: 10px; margin-bottom: 16px; }
        .institution td { vertical-align: middle; }
        .logo { width: 195px; }
        .institution-name { text-align: right; color: #4b5563; font-size: 9px; line-height: 1.5; }
        h1 { margin: 0 0 4px; text-align: center; color: #1f2937; font-size: 17px; }
        .subtitle { margin: 0 0 14px; text-align: center; color: #64748b; font-size: 9px; }
        .metadata, .kpis, .data { width: 100%; border-collapse: collapse; }
        .metadata { margin-bottom: 14px; }
        .metadata td { width: 50%; padding: 5px 8px; background: #f4f6f8; border: 1px solid #e2e8f0; }
        .kpis { margin-bottom: 16px; }
        .kpis td { width: 14.28%; padding: 8px 5px; text-align: center; background: #f4f7fb; border: 1px solid #dbe3ed; }
        .kpi-label { display: block; color: #64748b; font-size: 7px; text-transform: uppercase; }
        .kpi-value { display: block; margin-top: 4px; color: #1e293b; font-size: 14px; font-weight: bold; }
        h2 { margin: 14px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; font-size: 11px; color: #26374a; }
        .data th { padding: 6px 4px; background: #26374a; color: #fff; text-align: center; font-size: 7px; }
        .data td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; text-align: center; font-size: 7px; }
        .data tr:nth-child(even) td { background: #f8fafc; }
        .left { text-align: left !important; }
        .muted { color: #64748b; }
        .footer { position: fixed; bottom: -24px; left: 0; right: 0; padding-top: 5px; border-top: 1px solid #cbd5e1; text-align: center; color: #64748b; font-size: 7px; }
    </style>
</head>
<body>
    <table class="institution">
        <tr>
            <td><img class="logo" src="{{ public_path('images/logo_instituto_tecnologico-01.png') }}" alt="Instituto Tecnológico UCSC"></td>
            <td class="institution-name">UNIVERSIDAD CATÓLICA DE LA SANTÍSIMA CONCEPCIÓN<br>INSTITUTO TECNOLÓGICO · SEDE TALCAHUANO<br>UNIDAD DE SOPORTE</td>
        </tr>
    </table>

    <h1>Reporte de gestión de soporte</h1>
    <p class="subtitle">Resumen de solicitudes recibidas y atendidas en el período seleccionado</p>

    <table class="metadata">
        <tr><td><strong>Período:</strong> {{ $periodo }}</td><td><strong>Sede:</strong> {{ $selectedSedeName }}</td></tr>
        <tr><td><strong>Escuela:</strong> {{ $selectedSchoolName }}</td><td><strong>Condición / estado:</strong> {{ $estadoSeleccionado }}</td></tr>
        <tr><td><strong>Prioridad:</strong> {{ $prioridadSeleccionada }}</td><td><strong>Tipo de problema:</strong> {{ $tipoSeleccionado }}</td></tr>
        <tr><td><strong>Generado por:</strong> {{ $generadoPor }}</td><td><strong>Fecha de emisión:</strong> {{ $generadoEn->format('d/m/Y H:i') }}</td></tr>
    </table>

    <table class="kpis">
        <tr>
            <td><span class="kpi-label">Solicitados</span><span class="kpi-value">{{ $reportStats['total'] }}</span></td>
            <td><span class="kpi-label">Pendientes</span><span class="kpi-value">{{ $reportStats['open'] }}</span></td>
            <td><span class="kpi-label">En proceso</span><span class="kpi-value">{{ $reportStats['in_progress'] }}</span></td>
            <td><span class="kpi-label">Cerrados</span><span class="kpi-value">{{ $reportStats['closed'] }}</span></td>
            <td><span class="kpi-label">Sedes</span><span class="kpi-value">{{ $reportStats['sedes'] }}</span></td>
            <td><span class="kpi-label">Escuelas</span><span class="kpi-value">{{ $reportStats['escuelas'] }}</span></td>
            <td><span class="kpi-label">Tipo principal</span><span class="kpi-value" style="font-size: 9px">{{ $reportStats['top_category']?->label ?? 'Sin datos' }}</span></td>
        </tr>
    </table>
    <p class="muted"><strong>Tiempo promedio de resolución:</strong> {{ $tiempoPromedio }} (tickets cerrados creados durante el período).</p>

    <h2>Detalle por sede</h2>
    <table class="data">
        <thead><tr><th>Sede</th><th>Solicitados</th><th>Pendientes</th><th>En proceso</th><th>Cerrados</th><th>Tipo principal</th><th>Prioridad alta</th></tr></thead>
        <tbody>
            @forelse($reportBySede as $row)
                <tr><td class="left">{{ $row->sede_name }}</td><td>{{ $row->total }}</td><td>{{ $row->open }}</td><td>{{ $row->in_progress }}</td><td>{{ $row->closed }}</td><td>{{ $row->top_category }} ({{ $row->top_category_total }})</td><td>{{ $row->high }}</td></tr>
            @empty
                <tr><td colspan="7">No hay solicitudes registradas en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tipos de problema por sede</h2>
    <table class="data">
        <thead><tr><th>Sede</th><th>Acceso y cuenta</th><th>Red y conectividad</th><th>Equipos</th><th>Software</th><th>Reservas y espacios</th><th>Otro</th></tr></thead>
        <tbody>
            @forelse($reportBySede as $row)
                <tr><td class="left">{{ $row->sede_name }}</td><td>{{ $row->access }}</td><td>{{ $row->connectivity }}</td><td>{{ $row->hardware }}</td><td>{{ $row->software }}</td><td>{{ $row->reservations }}</td><td>{{ $row->other }}</td></tr>
            @empty
                <tr><td colspan="7">No hay solicitudes por sede en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle por escuela</h2>
    <table class="data">
        <thead><tr><th>Escuela</th><th>Solicitados</th><th>Pendientes</th><th>En proceso</th><th>Cerrados</th><th>Tipo principal</th><th>Prioridad baja</th><th>Prioridad media</th><th>Prioridad alta</th></tr></thead>
        <tbody>
            @forelse($reportBySchool as $row)
                <tr><td class="left">{{ $row->school_name }}</td><td>{{ $row->total }}</td><td>{{ $row->open }}</td><td>{{ $row->in_progress }}</td><td>{{ $row->closed }}</td><td>{{ $row->top_category }} ({{ $row->top_category_total }})</td><td>{{ $row->low }}</td><td>{{ $row->medium }}</td><td>{{ $row->high }}</td></tr>
            @empty
                <tr><td colspan="9">No hay solicitudes registradas por escuela en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tipos de problema por escuela</h2>
    <table class="data">
        <thead><tr><th>Escuela</th><th>Acceso y cuenta</th><th>Red y conectividad</th><th>Equipos</th><th>Software</th><th>Reservas y espacios</th><th>Otro</th></tr></thead>
        <tbody>
            @forelse($reportBySchool as $row)
                <tr><td class="left">{{ $row->school_name }}</td><td>{{ $row->access }}</td><td>{{ $row->connectivity }}</td><td>{{ $row->hardware }}</td><td>{{ $row->software }}</td><td>{{ $row->reservations }}</td><td>{{ $row->other }}</td></tr>
            @empty
                <tr><td colspan="7">No hay solicitudes por escuela en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tickets por prioridad</h2>
    <table class="data">
        <thead><tr><th>Prioridad</th><th>Cantidad</th></tr></thead>
        <tbody>
            @forelse($reportByPriority as $row)
                <tr><td>{{ ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'][$row->priority] ?? $row->priority }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No hay solicitudes para clasificar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tickets por tipo de problema</h2>
    <table class="data">
        <thead><tr><th>Tipo de problema</th><th>Cantidad</th></tr></thead>
        <tbody>
            @forelse($reportByCategory as $row)
                <tr><td>{{ $row->label }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No hay tipos de problema en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle de solicitudes</h2>
    <table class="data">
        <thead><tr><th>N°</th><th>Título</th><th>Solicitante</th><th>Sede</th><th>Escuela</th><th>Tipo de problema</th><th>Prioridad</th><th>Estado</th><th>Asignado a</th><th>Respuestas</th><th>Fecha de solicitud</th><th>Fecha de cierre</th></tr></thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td>#{{ $ticket->id }}</td>
                    <td class="left">{{ $ticket->title }}</td>
                    <td>{{ $ticket->user?->name ?? 'Sin solicitante' }}</td>
                    <td>{{ $sedes->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? 'Sin sede' }}</td>
                    <td>{{ $schoolNameByUser->get($ticket->user_id)?->school_name ?? 'Sin escuela' }}</td>
                    <td>{{ $ticket->categoryLabel() }}</td>
                    <td>{{ $ticket->priorityLabel() }}</td>
                    <td>{{ $ticket->statusLabel() }}</td>
                    <td>{{ $ticket->assignedTo?->name ?? 'Sin asignar' }}</td>
                    <td>{{ $ticket->replies_count }}</td>
                    <td>{{ $ticket->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $ticket->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="12">No hay solicitudes registradas en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Documento generado por AulaSync · Reporte de gestión de soporte</div>
</body>
</html>
