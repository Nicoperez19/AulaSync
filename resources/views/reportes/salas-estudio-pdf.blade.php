<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Salas de Estudio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ec4899;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #ec4899;
            font-size: 20px;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 10px;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-box p {
            margin: 3px 0;
        }
        .sala-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .sala-header {
            background-color: #ec4899;
            color: white;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .grupo {
            background-color: #fdf2f8;
            border-left: 3px solid #ec4899;
            padding: 10px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .grupo-header {
            font-weight: bold;
            color: #831843;
            margin-bottom: 8px;
            font-size: 11px;
        }
        .grupo-info {
            color: #666;
            font-size: 10px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table th {
            background-color: #f3f4f6;
            color: #374151;
            padding: 6px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #d1d5db;
        }
        table td {
            padding: 5px 6px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 10px;
            font-size: 9px;
            color: #9ca3af;
        }
        .page-break {
            page-break-after: always;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Reporte de Salas de Estudio</h1>
        <p>Accesos registrados agrupados por sesión</p>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p>Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    @if(count($gruposPorSala) > 0)
        @foreach($gruposPorSala as $idSala => $data)
            <div class="sala-section">
                <div class="sala-header">
                    {{ $data['sala']->nombre_espacio }} ({{ $data['sala']->id_espacio }}) - 
                    Capacidad: {{ $data['sala']->capacidad_maxima }} personas
                </div>

                @foreach($data['grupos'] as $index => $grupo)
                    <div class="grupo">
                        <div class="grupo-header">
                            Grupo #{{ $index + 1 }} - {{ $grupo['fecha']->format('d/m/Y') }}
                        </div>
                        <div class="grupo-info">
                            ⏰ Horario: {{ $grupo['hora_inicio']->format('H:i') }} - {{ $grupo['hora_fin']->format('H:i') }}
                            ({{ round($grupo['hora_inicio']->diffInMinutes($grupo['hora_fin']) / 60, 1) }} hrs) |
                            👥 {{ count($grupo['reservas']) }} personas
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">RUN</th>
                                    <th style="width: 35%">Nombre</th>
                                    <th style="width: 15%">Entrada</th>
                                    <th style="width: 15%">Salida</th>
                                    <th style="width: 15%">Tiempo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grupo['reservas'] as $i => $reserva)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $reserva->run_solicitante }}</td>
                                        <td>{{ $reserva->solicitante->nombre ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}</td>
                                        <td>
                                            @if($reserva->hora_salida)
                                                {{ \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i') }}
                                            @else
                                                --:--
                                            @endif
                                        </td>
                                        <td>
                                            @if($reserva->hora_salida)
                                                {{ round(\Carbon\Carbon::parse($reserva->hora)->diffInMinutes(\Carbon\Carbon::parse($reserva->hora_salida)) / 60, 1) }} hrs
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach

        <!-- Sección de usuarios vetados -->
        @if(count($vetosActivos) > 0)
            <div class="page-break"></div>
            
            <div class="sala-section">
                <div class="sala-header" style="background-color: #ea580c;">
                    🚫 Lista de Usuarios Vetados ({{ count($vetosActivos) }})
                </div>

                <div style="padding: 10px;">
                    <p style="color: #666; font-size: 10px; margin-bottom: 10px;">
                        Usuarios con restricción de acceso a las salas de estudio
                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 12%">RUN</th>
                                <th style="width: 20%">Nombre</th>
                                <th style="width: 8%">Tipo</th>
                                <th style="width: 30%">Motivo</th>
                                <th style="width: 12%">Vetado por</th>
                                <th style="width: 13%">Fecha veto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vetosActivos as $i => $veto)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $veto->run_vetado }}</td>
                                    <td style="font-weight: bold;">{{ $veto->solicitante->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <span style="background-color: {{ $veto->tipo_veto === 'grupal' ? '#fed7aa' : '#bfdbfe' }}; 
                                                     padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                                            {{ $veto->tipo_veto === 'grupal' ? 'Grupal' : 'Individual' }}
                                        </span>
                                    </td>
                                    <td style="font-size: 9px;">{{ $veto->observacion }}</td>
                                    <td>{{ $veto->vetado_por ?? 'Sistema' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($veto->fecha_veto)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top: 10px; padding: 8px; background-color: #fef3c7; border-left: 3px solid #f59e0b; font-size: 9px; color: #78350f;">
                        <strong>⚠️ Nota:</strong> Los usuarios vetados no pueden acceder a ninguna sala de estudio hasta que se libere el veto.
                    </div>
                </div>
            </div>
        @endif

        <div class="footer">
            Documento generado automáticamente por Gestor de Aulas IT - {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
    @else
        <div class="no-data">
            <p style="font-size: 14px; color: #6b7280;">📭 No hay registros para el período seleccionado</p>
        </div>
    @endif
</body>
</html>
