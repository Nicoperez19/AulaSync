<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-light-cloud-blue text-white shadow-sm">
                    <i class="fas fa-headset text-xl" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-light-cloud-blue">Centro de atención</p>
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard de Soporte</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Resumen y seguimiento de solicitudes de soporte.</p>
                </div>
            </div>
            <a href="{{ route('soporte.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-light-cloud-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">
                <i class="fas fa-ticket-alt" aria-hidden="true"></i> Ver todos los tickets
            </a>
        </div>
    </x-slot>

    <div class="space-y-5 pb-10">
        @if($canFilterSede)
            <form method="GET" action="{{ route('soporte.dashboard') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="min-w-[220px]">
                    <label for="id_sede" class="mb-1 block text-xs font-semibold text-gray-600">Filtrar por sede</label>
                    <select id="id_sede" name="id_sede" x-on:change="$el.form.requestSubmit()" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                        <option value="">Todas las sedes</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id_sede }}" @selected((string) request('id_sede') === (string) $sede->id_sede)>{{ $sede->nombre_sede }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->filled('id_sede'))
                    <a href="{{ route('soporte.dashboard') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Limpiar</a>
                @endif
            </form>
        @endif

        @php
            $chartTotal = $stats['total'];
            $statusRows = [
                ['label' => 'Abiertos', 'value' => $stats['open'], 'color' => '#7AA2D8'],
                ['label' => 'En proceso', 'value' => $stats['in_progress'], 'color' => '#E2B95B'],
                ['label' => 'Cerrados', 'value' => $stats['closed'], 'color' => '#78B69A'],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1.7fr)_minmax(280px,1fr)]">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6" aria-label="Estado actual de tickets">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Estado actual</p>
                        <h3 class="mt-1 text-lg font-bold text-gray-800">Solicitudes por atender</h3>
                    </div>
                    <span class="inline-flex items-center justify-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700">{{ $chartTotal }} en total</span>
                </div>
                <div class="mt-4 space-y-4" role="img" aria-label="{{ $chartTotal }} tickets: {{ $stats['open'] }} abiertos, {{ $stats['in_progress'] }} en proceso y {{ $stats['closed'] }} cerrados">
                    @foreach($statusRows as $status)
                        @php $percent = $chartTotal ? ($status['value'] / $chartTotal) * 100 : 0; @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="flex items-center gap-2 font-medium text-gray-700"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $status['color'] }}"></span>{{ $status['label'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $status['value'] }} <span class="font-normal text-gray-400">({{ number_format($percent, 0) }}%)</span></span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full transition-all" style="width: {{ $percent }}%; background-color: {{ $status['color'] }}"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="relative overflow-hidden rounded-2xl bg-light-cloud-blue/20 p-6 text-gray-800 shadow-sm">
                <div class="absolute -right-8 -top-8 h-36 w-36 rounded-full border-[18px] border-light-cloud-blue/10"></div>
                <div class="relative">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-light-cloud-blue/10 text-light-cloud-blue"><i class="fas fa-inbox text-lg" aria-hidden="true"></i></span>
                    <p class="mt-6 text-sm font-medium text-gray-600">Pendientes de atención</p>
                    <p class="mt-1 text-5xl font-bold tracking-tight text-gray-900">{{ $stats['open'] + $stats['in_progress'] }}</p>
                    <p class="mt-2 text-sm text-gray-600">{{ $stats['open'] }} abiertos · {{ $stats['in_progress'] }} en proceso</p>
                    <a href="{{ route('soporte.index', ['status' => 'open']) }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-light-cloud-blue px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800">Ir a la bandeja <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6" aria-label="Resumen de indicadores de soporte">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Resumen del período</p>
                    <h3 class="mt-1 text-lg font-bold text-gray-800">Indicadores de soporte</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }} · {{ $selectedSedeName }}</p>
                </div>
                <a href="{{ route('soporte.reportes', array_filter(['fecha_inicio' => $fechaInicio->toDateString(), 'fecha_fin' => $fechaFin->toDateString(), 'id_sede' => $selectedSedeId, 'estado' => $selectedStatus, 'prioridad' => $selectedPriority, 'id_escuela' => $selectedSchoolId, 'tipo_problema' => $selectedCategory])) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                    Ver reportes completos <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs font-medium text-blue-700">Solicitados</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['total'] }}</p></div>
                <div class="rounded-xl bg-sky-50 p-4"><p class="text-xs font-medium text-sky-700">Pendientes</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['open'] }}</p></div>
                <div class="rounded-xl bg-amber-50 p-4"><p class="text-xs font-medium text-amber-700">En proceso</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['in_progress'] }}</p></div>
                <div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs font-medium text-emerald-700">Cerrados</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['closed'] }}</p></div>
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-medium text-gray-600">Sedes</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['sedes'] }}</p></div>
                <div class="rounded-xl bg-violet-50 p-4"><p class="text-xs font-medium text-violet-700">Escuelas</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['escuelas'] }}</p></div>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4 text-xs text-gray-500">
                <p>Prioridad: <span class="font-semibold text-red-700">Alta {{ $reportStats['priority_high'] }}</span> · <span class="font-semibold text-amber-700">Media {{ $reportStats['priority_medium'] }}</span> · <span class="font-semibold text-gray-700">Baja {{ $reportStats['priority_low'] }}</span></p>
                <p>Tipo más reportado: <span class="font-semibold text-gray-700">{{ $reportStats['top_category']?->label ?? 'Sin datos' }}</span>@if($reportStats['top_category']) ({{ $reportStats['top_category']->total }}) @endif</p>
                <p>Tiempo promedio de resolución: <span class="font-semibold text-gray-700">{{ $reportStats['average_resolution_minutes'] !== null ? intdiv((int) round($reportStats['average_resolution_minutes']), 60) . ' h ' . ((int) round($reportStats['average_resolution_minutes']) % 60) . ' min' : 'sin datos' }}</span></p>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-gray-800">Tickets por atender</h3>
                    <p class="mt-1 text-xs text-gray-500">Solicitudes abiertas y en proceso, ordenadas por prioridad y antigüedad.</p>
                </div>
                <a href="{{ route('soporte.index', ['status' => 'open']) }}" class="w-36 shrink-0 text-center text-sm font-semibold text-green-700 hover:text-green-800">Abrir bandeja</a>
            </div>
            @if($tickets->isEmpty())
                <div class="py-12 text-center text-sm text-gray-500">No hay tickets para mostrar con este filtro.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">#</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Título</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo de problema</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Solicitante</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Sede</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Prioridad</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Asignado a</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Respuestas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                                <tr class="transition-colors hover:bg-gray-50 {{ $ticket->isClosed() ? 'opacity-75' : '' }}">
                                    <td class="px-4 py-3 text-center font-mono text-xs text-gray-400">#{{ $ticket->id }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <p class="mx-auto max-w-[220px] truncate font-medium text-gray-800" title="{{ $ticket->title }}">{{ $ticket->title }}</p>
                                        <p class="mx-auto max-w-[220px] truncate text-xs text-gray-400">{{ Str::limit($ticket->description, 60) }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ticket->categoryLabel() }}</td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ticket->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $sedes->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php $pc = $ticket->priorityColor(); @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">{{ $ticket->priorityLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php $sc = $ticket->statusColor(); @endphp
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $sc === 'blue' ? 'bg-blue-500' : ($sc === 'yellow' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>{{ $ticket->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $ticket->assignedTo?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">{{ $ticket->replies_count }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('soporte.show', $ticket) }}" class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 transition-colors hover:bg-green-100" aria-label="Atender ticket {{ $ticket->id }}">Atender <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
