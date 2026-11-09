<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-light-cloud-blue text-white shadow-sm">
                    <i class="fas fa-headset text-xl" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-light-cloud-blue">Centro de atención</p>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $isTecnico ? 'Gestión de Soporte' : 'Mis solicitudes de soporte' }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                    @if($isTecnico)
                        Gestión de tickets de soporte
                    @else
                        Consulta el estado de tus solicitudes de soporte
                    @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('soporte.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Nuevo Ticket
            </a>
        </div>
    </x-slot>

    <div class="space-y-4 pb-8">

        {{-- Alertas --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                <i class="fas fa-check-circle text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Distribución de solicitudes por estado --}}
        @if($stats)
            @php
                $chartTotal = $stats['open'] + $stats['in_progress'] + $stats['closed'];
                $statusRows = [
                    ['label' => 'Abiertas', 'value' => $stats['open'], 'color' => '#7AA2D8'],
                    ['label' => 'En proceso', 'value' => $stats['in_progress'], 'color' => '#E2B95B'],
                    ['label' => 'Cerradas', 'value' => $stats['closed'], 'color' => '#78B69A'],
                ];
            @endphp
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5" aria-label="Gráfico de columnas de solicitudes por estado">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Estado actual</p>
                        <h3 class="mt-1 text-lg font-bold text-gray-800">Estado de mis tickets</h3>
                        <p class="mt-1 text-sm text-gray-500">Distribución de tus solicitudes de soporte</p>
                    </div>
                    <span class="inline-flex items-center justify-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700">{{ $chartTotal }} en total</span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-4 px-2" role="img" aria-label="{{ $chartTotal }} solicitudes: {{ $stats['open'] }} abiertas, {{ $stats['in_progress'] }} en proceso y {{ $stats['closed'] }} cerradas">
                    @foreach($statusRows as $status)
                        @php
                            $percent = $chartTotal ? ($status['value'] / $chartTotal) * 100 : 0;
                            $maxValue = max($stats['open'], $stats['in_progress'], $stats['closed']);
                            $barHeight = $maxValue ? ($status['value'] / $maxValue) * 78 : 0;
                        @endphp
                        <div class="min-w-0 text-center">
                            <div class="flex h-28 flex-col items-center justify-end border-b border-gray-200">
                                <span class="mb-2 text-sm font-bold text-gray-800">{{ $status['value'] }}</span>
                                <div class="w-full max-w-20 rounded-t-lg transition-all" style="height: {{ $barHeight }}%; min-height: {{ $status['value'] ? '8px' : '0' }}; background-color: {{ $status['color'] }}" aria-hidden="true"></div>
                            </div>
                            <span class="mt-2 block text-xs font-medium text-gray-600">{{ $status['label'] }}</span>
                            <span class="mt-0.5 block text-[11px] text-gray-400">{{ number_format($percent, 0) }}%</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Filtros --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <form method="GET" action="{{ route('soporte.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" x-on:input.debounce.500ms="$el.form.requestSubmit()"
                           placeholder="Título o descripción..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                </div>
                @if($isGlobalAdmin)
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sede</label>
                    <select name="id_sede" x-on:change="$el.form.requestSubmit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue">
                        <option value="">Todas las sedes</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id_sede }}" {{ request('id_sede') === $sede->id_sede ? 'selected' : '' }}>{{ $sede->nombre_sede }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status" x-on:change="$el.form.requestSubmit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue">
                        <option value="">Todos</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Abierto</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                    <select name="priority" x-on:change="$el.form.requestSubmit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue">
                        <option value="">Todas</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    @if(request()->hasAny(['search','status','priority','id_sede']))
                        <a href="{{ route('soporte.index') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabla de tickets y detalle en modal --}}
        <div x-data="{ activeTicket: null }" @keydown.escape.window="activeTicket = null">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($tickets->isEmpty())
                <div class="py-16 text-center">
                    <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">No hay tickets disponibles</p>
                    <p class="text-sm text-gray-400 mt-1">
                        @if(!$isTecnico) ¿Necesitas ayuda? @endif
                        <a href="{{ route('soporte.create') }}" class="text-light-cloud-blue underline">Crea un nuevo ticket</a>
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">#</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Título</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Tipo de problema</th>
                                @if($isTecnico)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Solicitante</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Sede</th>
                                @endif
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Prioridad</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Estado</th>
                                @if($isTecnico)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Asignado a</th>
                                @endif
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Respuestas</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wide">Fecha</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wide">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors {{ $ticket->isClosed() ? 'opacity-75' : '' }}">
                                <td class="px-4 py-3 text-center font-mono text-gray-400 text-xs">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3 text-center">
                                    <p class="mx-auto max-w-[220px] truncate font-medium text-gray-800" title="{{ $ticket->title }}">
                                        {{ $ticket->title }}
                                    </p>
                                    <p class="mx-auto max-w-[220px] truncate text-xs text-gray-400">{{ Str::limit($ticket->description, 60) }}</p>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ticket->categoryLabel() }}</td>
                                @if($isTecnico)
                                <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $ticket->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $sedes->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    @php $pc = $ticket->priorityColor(); @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $ticket->priorityLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php $sc = $ticket->statusColor(); @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            {{ $sc === 'blue' ? 'bg-blue-500' : ($sc === 'yellow' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>
                                        {{ $ticket->statusLabel() }}
                                    </span>
                                </td>
                                @if($isTecnico)
                                <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $ticket->assignedTo?->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                        {{ $ticket->replies->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="activeTicket = {{ $ticket->id }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                                       aria-label="Ver estado y detalle del ticket {{ $ticket->id }}">
                                        <i class="fas fa-eye"></i> Ver estado
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($tickets->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $tickets->links() }}
                    </div>
                @endif
            @endif
        </div>
        @foreach($tickets as $ticket)
            @php
                $statusSteps = ['open' => 1, 'in_progress' => 2, 'closed' => 3];
                $currentStep = $statusSteps[$ticket->status] ?? 1;
            @endphp
            <div x-cloak x-show="activeTicket === {{ $ticket->id }}" x-transition.opacity
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6" role="dialog" aria-modal="true"
                 aria-labelledby="ticket-title-{{ $ticket->id }}">
                <button type="button" class="absolute inset-0 bg-slate-950/60" @click="activeTicket = null" aria-label="Cerrar detalle"></button>
                <section x-transition class="relative flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <header class="flex items-start justify-between gap-4 bg-light-cloud-blue px-5 py-5 text-white sm:px-7">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Seguimiento de solicitud · #{{ $ticket->id }}</p>
                            <h3 id="ticket-title-{{ $ticket->id }}" class="mt-1 text-xl font-bold">{{ $ticket->title }}</h3>
                        </div>
                        <button type="button" @click="activeTicket = null" class="rounded-lg p-2 text-white/80 hover:bg-white/15 hover:text-white" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </header>
                    <div class="overflow-y-auto p-5 sm:p-7">
                        <div class="flex flex-wrap items-center gap-2">
                            @php $statusStyle = ['open' => 'bg-sky-100 text-sky-800', 'in_progress' => 'bg-amber-100 text-amber-800', 'closed' => 'bg-emerald-100 text-emerald-800'][$ticket->status] ?? 'bg-gray-100 text-gray-700'; @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle }}"><i class="fas fa-circle mr-1 text-[8px]"></i>{{ $ticket->statusLabel() }}</span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">Prioridad {{ strtolower($ticket->priorityLabel()) }}</span>
                            <span class="text-xs text-gray-400">Actualizado {{ $ticket->updated_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-3 items-start">
                            @foreach(['Recibido', 'En revisión', 'Cerrado'] as $stepIndex => $stepLabel)
                                @php $stepNumber = $stepIndex + 1; @endphp
                                <div class="relative text-center">
                                    @if($stepNumber < 3)
                                        <div class="absolute left-1/2 top-4 h-0.5 w-full {{ $currentStep > $stepNumber ? 'bg-light-cloud-blue' : 'bg-gray-200' }}"></div>
                                    @endif
                                    <span class="relative z-10 mx-auto flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold {{ $currentStep >= $stepNumber ? 'bg-light-cloud-blue text-white' : 'bg-gray-100 text-gray-400' }}">
                                        @if($currentStep > $stepNumber)<i class="fas fa-check"></i>@else{{ $stepNumber }}@endif
                                    </span>
                                    <span class="mt-2 block text-xs font-medium {{ $currentStep >= $stepNumber ? 'text-light-cloud-blue' : 'text-gray-400' }}">{{ $stepLabel }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Detalle de la solicitud</h4>
                            <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $ticket->description }}</p>
                            <p class="mt-3 text-xs text-gray-400">Creada el {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="mt-6">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="font-semibold text-gray-800">Historial de respuestas</h4>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-500">{{ $ticket->replies->count() }}</span>
                            </div>
                            <div class="space-y-3">
                                @forelse($ticket->replies as $reply)
                            <article class="rounded-xl border p-4 {{ $reply->is_staff_reply ? 'border-red-100 bg-red-50/70' : 'border-gray-100 bg-white' }}">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-800">{{ $reply->user?->name ?? 'Equipo de soporte' }}
                                                @if($reply->is_staff_reply)<span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-light-cloud-blue">Soporte</span>@endif
                                            </p>
                                            <time class="text-xs text-gray-400">{{ $reply->created_at->format('d/m/Y H:i') }}</time>
                                        </div>
                                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-600">{{ $reply->message }}</p>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                                        Aún no hay respuestas. Aquí aparecerán las actualizaciones del equipo de soporte.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <footer class="flex justify-end border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-7">
                        <button type="button" @click="activeTicket = null" class="rounded-lg bg-light-cloud-blue px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Cerrar</button>
                    </footer>
                </section>
            </div>
        @endforeach
        </div>

    </div>
</x-app-layout>
