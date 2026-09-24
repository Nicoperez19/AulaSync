<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-headset mr-2 text-blue-600"></i> Dashboard de Soporte
                </h2>
                <p class="text-sm text-gray-500 mt-1">Resumen y seguimiento de solicitudes de soporte.</p>
            </div>
            <a href="{{ route('soporte.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class="fas fa-ticket-alt"></i> Ver todos los tickets
            </a>
        </div>
    </x-slot>

    <div class="space-y-6 pb-10">
        <form method="GET" action="{{ route('soporte.dashboard') }}" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="min-w-[220px]">
                <label for="id_sede" class="block text-xs font-medium text-gray-600 mb-1">Sede</label>
                <select id="id_sede" name="id_sede" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas las sedes</option>
                    @foreach($sedes as $sede)
                        <option value="{{ $sede->id_sede }}" @selected(request('id_sede') === $sede->id_sede)>{{ $sede->nombre_sede }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700">Aplicar filtro</button>
            @if(request()->filled('id_sede'))
                <a href="{{ route('soporte.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Limpiar</a>
            @endif
        </form>

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" aria-label="Resumen de tickets">
            @foreach([
                ['label' => 'Total de tickets', 'value' => $stats['total'], 'icon' => 'fa-ticket-alt', 'iconClass' => 'bg-blue-100 text-blue-600'],
                ['label' => 'Abiertos', 'value' => $stats['open'], 'icon' => 'fa-folder-open', 'iconClass' => 'bg-sky-100 text-sky-600'],
                ['label' => 'En proceso', 'value' => $stats['in_progress'], 'icon' => 'fa-spinner', 'iconClass' => 'bg-amber-100 text-amber-600'],
                ['label' => 'Cerrados', 'value' => $stats['closed'], 'icon' => 'fa-check-circle', 'iconClass' => 'bg-emerald-100 text-emerald-600'],
            ] as $card)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $card['iconClass'] }} flex items-center justify-center">
                        <i class="fas {{ $card['icon'] }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">Tickets por atender</h3>
                    <p class="text-xs text-gray-500 mt-1">Solicitudes abiertas y en proceso, ordenadas por prioridad y antigüedad.</p>
                </div>
                <a href="{{ route('soporte.index', ['status' => 'open']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Abrir bandeja</a>
            </div>

            @if($tickets->isEmpty())
                <div class="py-12 text-center text-sm text-gray-500">No hay tickets para mostrar con este filtro.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Ticket</th>
                                <th class="px-5 py-3 text-left">Solicitante</th>
                                <th class="px-5 py-3 text-left">Sede</th>
                                <th class="px-5 py-3 text-left">Prioridad</th>
                                <th class="px-5 py-3 text-left">Estado</th>
                                <th class="px-5 py-3 text-left">Asignado a</th>
                                <th class="px-5 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <span class="block font-mono text-xs text-gray-400">#{{ $ticket->id }}</span>
                                        <span class="font-medium text-gray-800">{{ Str::limit($ticket->title, 48) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $ticket->user?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $sedes->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $ticket->priorityLabel() }}</td>
                                    <td class="px-5 py-3">{{ $ticket->statusLabel() }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $ticket->assignedTo?->name ?? 'Sin asignar' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('soporte.show', $ticket) }}" class="text-blue-600 hover:text-blue-700 font-medium">Atender <i class="fas fa-arrow-right ml-1"></i></a>
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
