<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-light-cloud-blue text-white shadow-sm"><i class="fas fa-chart-pie text-xl" aria-hidden="true"></i></span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-light-cloud-blue">Unidad de soporte</p>
                    <h2 class="text-2xl font-bold text-gray-800">Reportes de soporte</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Informe detallado de solicitudes, estados, tiempos y sedes.</p>
                </div>
            </div>
            <a href="{{ route('soporte.dashboard', array_filter(['id_sede' => $selectedSedeId, 'fecha_inicio' => $fechaInicio->toDateString(), 'fecha_fin' => $fechaFin->toDateString(), 'estado' => $selectedStatus, 'prioridad' => $selectedPriority, 'id_escuela' => $selectedSchoolId, 'tipo_problema' => $selectedCategory])) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al dashboard</a>
        </div>
    </x-slot>

    <div class="space-y-5 pb-10">
        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6" aria-label="Filtros del informe">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Consulta oficial</p>
                <h3 class="mt-1 text-lg font-bold text-gray-800">Parámetros del informe</h3>
            </div>
            <form method="GET" action="{{ route('soporte.reportes') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="fecha_inicio" class="mb-1 block text-xs font-semibold text-gray-600">Desde</label>
                    <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ $fechaInicio->toDateString() }}" max="{{ $fechaFin->toDateString() }}" class="rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                </div>
                <div>
                    <label for="fecha_fin" class="mb-1 block text-xs font-semibold text-gray-600">Hasta</label>
                    <input id="fecha_fin" name="fecha_fin" type="date" value="{{ $fechaFin->toDateString() }}" min="{{ $fechaInicio->toDateString() }}" class="rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                </div>
                @if($canFilterSede)
                    <div class="min-w-[210px]">
                        <label for="id_sede" class="mb-1 block text-xs font-semibold text-gray-600">Sede</label>
                        <select id="id_sede" name="id_sede" class="w-full rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                            <option value="">Todas las sedes</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" @selected((string) $selectedSedeId === (string) $sede->id_sede)>{{ $sede->nombre_sede }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="min-w-[170px]">
                    <label for="estado" class="mb-1 block text-xs font-semibold text-gray-600">Condición / estado</label>
                    <select id="estado" name="estado" class="w-full rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                        <option value="">Todos los estados</option>
                        <option value="open" @selected($selectedStatus === 'open')>Pendiente</option>
                        <option value="in_progress" @selected($selectedStatus === 'in_progress')>En proceso</option>
                        <option value="closed" @selected($selectedStatus === 'closed')>Cerrado</option>
                    </select>
                </div>
                <div class="min-w-[170px]">
                    <label for="prioridad" class="mb-1 block text-xs font-semibold text-gray-600">Prioridad</label>
                    <select id="prioridad" name="prioridad" class="w-full rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                        <option value="">Todas las prioridades</option>
                        <option value="high" @selected($selectedPriority === 'high')>Alta</option>
                        <option value="medium" @selected($selectedPriority === 'medium')>Media</option>
                        <option value="low" @selected($selectedPriority === 'low')>Baja</option>
                    </select>
                </div>
                <div class="min-w-[210px]">
                    <label for="id_escuela" class="mb-1 block text-xs font-semibold text-gray-600">Escuela</label>
                    <select id="id_escuela" name="id_escuela" class="w-full rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                        <option value="">Todas las escuelas</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id_area_academica }}" @selected((string) $selectedSchoolId === (string) $school->id_area_academica)>{{ $school->nombre_area_academica }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[210px]">
                    <label for="tipo_problema" class="mb-1 block text-xs font-semibold text-gray-600">Tipo de problema</label>
                    <select id="tipo_problema" name="tipo_problema" class="w-full rounded-lg border-gray-300 text-sm focus:border-light-cloud-blue focus:ring-light-cloud-blue">
                        <option value="">Todos los tipos</option>
                        @foreach(\App\Models\SupportTicket::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" @selected($selectedCategory === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-light-cloud-blue px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800"><i class="fas fa-filter" aria-hidden="true"></i> Aplicar filtros</button>
                <a href="{{ route('soporte.reportes') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">Limpiar</a>
            </form>
            @if($periodError)
                <p class="mt-3 text-sm text-red-600">Revisa los filtros: fechas, sede, escuela, estado, prioridad o tipo de problema no válidos.</p>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm" aria-label="Informe oficial de soporte">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Informe de gestión</p>
                    <h3 class="mt-1 text-lg font-bold text-gray-800">Resumen ejecutivo</h3>
                    <p class="mt-1 text-sm text-gray-500">Período {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }} · {{ $selectedSedeName }} · {{ $selectedSchoolName }} · {{ $selectedCategoryName }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('soporte.reportes.export', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800"><i class="fas fa-file-excel" aria-hidden="true"></i> Excel</a>
                    <a href="{{ route('soporte.reportes.export', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800"><i class="fas fa-file-pdf" aria-hidden="true"></i> PDF</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-6 sm:px-6">
                <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs font-medium text-blue-700">Solicitados</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['total'] }}</p></div>
                <div class="rounded-xl bg-sky-50 p-4"><p class="text-xs font-medium text-sky-700">Pendientes</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['open'] }}</p></div>
                <div class="rounded-xl bg-amber-50 p-4"><p class="text-xs font-medium text-amber-700">En proceso</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['in_progress'] }}</p></div>
                <div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs font-medium text-emerald-700">Cerrados</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['closed'] }}</p></div>
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-medium text-gray-600">Sedes</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['sedes'] }}</p></div>
                <div class="rounded-xl bg-violet-50 p-4"><p class="text-xs font-medium text-violet-700">Escuelas</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $reportStats['escuelas'] }}</p></div>
            </div>

            <div class="grid gap-5 border-t border-gray-100 p-5 sm:p-6 lg:grid-cols-2">
                <div>
                    <h4 class="mb-3 text-sm font-semibold text-gray-800">Solicitudes por escuela</h4>
                    @if($reportBySchool->isEmpty())
                        <p class="rounded-lg bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">No hay solicitudes registradas por escuela en este período.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200 bg-gray-50"><tr>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Escuela</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Solicitados</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Pendientes</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">En proceso</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Cerrados</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo principal</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Prioridad alta</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($reportBySchool as $row)
                                        <tr><td class="px-4 py-3 text-center font-medium text-gray-700">{{ $row->school_name }}</td><td class="px-4 py-3 text-center">{{ $row->total }}</td><td class="px-4 py-3 text-center">{{ $row->open }}</td><td class="px-4 py-3 text-center">{{ $row->in_progress }}</td><td class="px-4 py-3 text-center">{{ $row->closed }}</td><td class="px-4 py-3 text-center">{{ $row->top_category }} ({{ $row->top_category_total }})</td><td class="px-4 py-3 text-center">{{ $row->high }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <h4 class="mb-3 mt-5 text-sm font-semibold text-gray-800">Solicitudes por sede</h4>
                    @if($reportBySede->isEmpty())
                        <p class="rounded-lg bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">No hay solicitudes registradas por sede en este período.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200 bg-gray-50"><tr>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Sede</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Solicitados</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Pendientes</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">En proceso</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Cerrados</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo principal</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Prioridad alta</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($reportBySede as $row)
                                        <tr><td class="px-4 py-3 text-center font-medium text-gray-700">{{ $row->sede_name }}</td><td class="px-4 py-3 text-center">{{ $row->total }}</td><td class="px-4 py-3 text-center">{{ $row->open }}</td><td class="px-4 py-3 text-center">{{ $row->in_progress }}</td><td class="px-4 py-3 text-center">{{ $row->closed }}</td><td class="px-4 py-3 text-center">{{ $row->top_category }} ({{ $row->top_category_total }})</td><td class="px-4 py-3 text-center">{{ $row->high }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="space-y-4">
                    <div>
                        <h4 class="mb-3 text-sm font-semibold text-gray-800">Tickets por prioridad</h4>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([['high', 'Alta', 'red'], ['medium', 'Media', 'amber'], ['low', 'Baja', 'gray']] as [$level, $label, $tone])
                                @php $priorityRow = $reportByPriority->firstWhere('priority', $level); @endphp
                                <div class="rounded-xl p-4 {{ $tone === 'red' ? 'bg-red-50' : ($tone === 'amber' ? 'bg-amber-50' : 'bg-gray-50') }}">
                                    <p class="text-xs font-medium {{ $tone === 'red' ? 'text-red-700' : ($tone === 'amber' ? 'text-amber-700' : 'text-gray-600') }}">{{ $label }}</p>
                                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $priorityRow->total ?? 0 }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-3 text-sm font-semibold text-gray-800">Tickets por tipo de problema</h4>
                        @if($reportByCategory->isEmpty())
                            <p class="rounded-lg bg-gray-50 px-4 py-5 text-center text-sm text-gray-500">No hay tipos de problema en este período.</p>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-gray-100">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50"><tr><th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo</th><th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Solicitados</th></tr></thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($reportByCategory as $row)
                                            <tr><td class="px-4 py-3 text-center font-medium text-gray-700">{{ $row->label }}</td><td class="px-4 py-3 text-center">{{ $row->total }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4">
                        <h4 class="text-sm font-semibold text-gray-800">Tiempo promedio de resolución</h4>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $reportStats['average_resolution_minutes'] !== null ? intdiv((int) round($reportStats['average_resolution_minutes']), 60) . ' h ' . ((int) round($reportStats['average_resolution_minutes']) % 60) . ' min' : 'Sin datos' }}</p>
                        <p class="mt-1 text-xs text-gray-500">Promedio de tickets cerrados creados en este período.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 px-5 py-5 sm:px-6">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-800">Detalle de solicitudes</h4>
                    <p class="mt-1 text-xs text-gray-500">{{ $tickets->total() }} registros en el período · página {{ $tickets->currentPage() }} de {{ max(1, $tickets->lastPage()) }}</p>
                </div>
                @if($tickets->isEmpty())
                    <p class="rounded-lg bg-gray-50 px-4 py-10 text-center text-sm text-gray-500">No hay solicitudes registradas con estos filtros.</p>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50"><tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">#</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Título</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Solicitante</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Sede</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Escuela</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Prioridad</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Asignado a</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Respuestas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($tickets as $ticket)
                                    <tr class="transition-colors hover:bg-gray-50">
                                        <td class="px-4 py-3 text-center font-mono text-xs text-gray-400">#{{ $ticket->id }}</td>
                                        <td class="px-4 py-3 text-center"><p class="mx-auto max-w-[220px] truncate font-medium text-gray-800" title="{{ $ticket->title }}">{{ $ticket->title }}</p><p class="mx-auto max-w-[220px] truncate text-xs text-gray-400">{{ Str::limit($ticket->description, 60) }}</p></td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ticket->categoryLabel() }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ticket->user?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $sedes->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $schoolNameByUser->get($ticket->user_id)?->school_name ?? 'Sin escuela' }}</td>
                                        <td class="px-4 py-3 text-center"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $ticket->priorityColor() === 'red' ? 'bg-red-100 text-red-700' : ($ticket->priorityColor() === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">{{ $ticket->priorityLabel() }}</span></td>
                                        <td class="px-4 py-3 text-center"><span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $ticket->statusColor() === 'blue' ? 'bg-blue-100 text-blue-700' : ($ticket->statusColor() === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}"><span class="h-1.5 w-1.5 rounded-full {{ $ticket->statusColor() === 'blue' ? 'bg-blue-500' : ($ticket->statusColor() === 'yellow' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>{{ $ticket->statusLabel() }}</span></td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $ticket->assignedTo?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center"><span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">{{ $ticket->replies_count }}</span></td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($tickets->hasPages())
                        <div class="mt-4">{{ $tickets->links() }}</div>
                    @endif
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
