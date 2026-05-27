<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-headset mr-2 text-blue-600"></i>
                    Soporte Técnico
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($isTecnico)
                        Gestión de tickets de soporte
                    @else
                        Consulta el estado de tus solicitudes de soporte
                    @endif
                </p>
            </div>
            <a href="{{ route('soporte.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fas fa-plus"></i>
                Nuevo Ticket
            </a>
        </div>
    </x-slot>

    <div class="space-y-5 pb-10">

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

        {{-- Estadísticas (solo técnicos/admins) --}}
        @if($isTecnico && $stats)
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-folder-open text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['open'] }}</p>
                        <p class="text-xs text-gray-500">Abiertos</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500 flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-spinner text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['in_progress'] }}</p>
                        <p class="text-xs text-gray-500">En proceso</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-500 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['closed'] }}</p>
                        <p class="text-xs text-gray-500">Cerrados</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <form method="GET" action="{{ route('soporte.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Título o descripción..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Abierto</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                    <select name="priority" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['search','status','priority']))
                        <a href="{{ route('soporte.index') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabla de tickets --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($tickets->isEmpty())
                <div class="py-16 text-center">
                    <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">No hay tickets disponibles</p>
                    <p class="text-sm text-gray-400 mt-1">
                        @if(!$isTecnico) ¿Necesitas ayuda? @endif
                        <a href="{{ route('soporte.create') }}" class="text-blue-600 underline">Crea un nuevo ticket</a>
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Título</th>
                                @if($isTecnico)
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Solicitante</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Prioridad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                                @if($isTecnico)
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Asignado a</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Respuestas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors {{ $ticket->isClosed() ? 'opacity-75' : '' }}">
                                <td class="px-4 py-3 font-mono text-gray-400 text-xs">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 truncate max-w-[220px]" title="{{ $ticket->title }}">
                                        {{ $ticket->title }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate max-w-[220px]">{{ Str::limit($ticket->description, 60) }}</p>
                                </td>
                                @if($isTecnico)
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $ticket->user?->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3">
                                    @php $pc = $ticket->priorityColor(); @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $ticket->priorityLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php $sc = $ticket->statusColor(); @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            {{ $sc === 'blue' ? 'bg-blue-500' : ($sc === 'yellow' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>
                                        {{ $ticket->statusLabel() }}
                                    </span>
                                </td>
                                @if($isTecnico)
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $ticket->assignedTo?->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                        {{ $ticket->replies->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('soporte.show', $ticket) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                        Ver <i class="fas fa-arrow-right"></i>
                                    </a>
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

    </div>
</x-app-layout>
