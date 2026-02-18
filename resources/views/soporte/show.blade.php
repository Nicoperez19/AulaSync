<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('soporte.index') }}"
               class="flex items-center justify-center w-8 h-8 bg-white rounded-lg shadow-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-0.5">
                    <span class="font-mono text-xs text-gray-400">#{{ $ticket->id }}</span>
                    {{-- Badge estado --}}
                    @php $sc = $ticket->statusColor(); @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $sc === 'blue' ? 'bg-blue-500' : ($sc === 'yellow' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>
                        {{ $ticket->statusLabel() }}
                    </span>
                    {{-- Badge prioridad --}}
                    @php $pc = $ticket->priorityColor(); @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $ticket->priorityLabel() }}
                    </span>
                </div>
                <h2 class="text-xl font-bold text-gray-800 truncate">{{ $ticket->title }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="pb-10">
        @if(session('success'))
            <div class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                <i class="fas fa-check-circle text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ── Columna principal: descripción + conversación ──────────────── --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Descripción del ticket --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-sm font-bold text-blue-600">
                                {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->user?->name ?? 'Usuario desconocido' }}</p>
                                <p class="text-xs text-gray-400">{{ $ticket->created_at->format('d/m/Y H:i') }} · Creó el ticket</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">Descripción inicial</span>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
                    </div>
                </div>

                {{-- Hilo de respuestas --}}
                @forelse($ticket->replies as $reply)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden {{ $reply->is_staff_reply ? 'border-l-4 border-blue-400' : '' }}">
                        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100
                                    {{ $reply->is_staff_reply ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                            {{ $reply->is_staff_reply ? 'bg-blue-200 text-blue-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ strtoupper(substr($reply->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                        {{ $reply->user?->name ?? 'Usuario' }}
                                        @if($reply->is_staff_reply)
                                            <span class="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-600 rounded font-medium">Técnico</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $reply->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $reply->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-sm text-gray-400">
                        <i class="fas fa-comments text-2xl mb-2 block"></i>
                        Sin respuestas aún
                    </div>
                @endforelse

                {{-- Formulario de respuesta --}}
                @if(!$ticket->isClosed())
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-reply mr-1 text-blue-500"></i> Agregar respuesta
                        </h3>
                        <form method="POST" action="{{ route('soporte.reply', $ticket) }}">
                            @csrf
                            @if($errors->has('message'))
                                <p class="mb-2 text-xs text-red-600">{{ $errors->first('message') }}</p>
                            @endif
                            <textarea
                                name="message"
                                rows="4"
                                placeholder="{{ $isTecnico ? 'Escribe tu respuesta al usuario...' : 'Agrega información adicional o consulta el estado...' }}"
                                class="w-full px-4 py-2.5 text-sm border {{ $errors->has('message') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                required
                            >{{ old('message') }}</textarea>
                            <div class="flex items-center justify-between mt-3">
                                <p class="text-xs text-gray-400">Máximo 5000 caracteres</p>
                                <button type="submit"
                                        class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-paper-plane mr-1"></i> Enviar
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="text-center py-4 text-sm text-gray-500 bg-white rounded-xl shadow-sm">
                        <i class="fas fa-lock mr-1 text-gray-400"></i>
                        Este ticket está cerrado.
                        <form method="POST" action="{{ route('soporte.reopen', $ticket) }}" class="inline">
                            @csrf
                            <button type="submit" class="ml-2 text-blue-600 underline text-xs hover:text-blue-700">
                                Reabrir ticket
                            </button>
                        </form>
                    </div>
                @endif

            </div>

            {{-- ── Columna lateral: metadatos y acciones ─────────────────────── --}}
            <div class="space-y-4">

                {{-- Info del ticket --}}
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-2">Detalles</h3>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Creado por</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->user?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Fecha de creación</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Última actualización</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->updated_at->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Respuestas</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->replies->count() }}</dd>
                        </div>
                        @if($ticket->assignedTo)
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Técnico asignado</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->assignedTo->name }}</dd>
                        </div>
                        @endif
                        @if($ticket->closed_at)
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Cerrado el</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $ticket->closed_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Acciones del técnico --}}
                @if($isTecnico)
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-2">
                        <i class="fas fa-tools mr-1 text-blue-500"></i> Acciones de técnico
                    </h3>

                    {{-- Cambiar estado --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Cambiar estado</p>
                        <form method="POST" action="{{ route('soporte.status', $ticket) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status"
                                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Abierto</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                            <button type="submit"
                                    class="px-3 py-1.5 bg-gray-700 text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition-colors">
                                OK
                            </button>
                        </form>
                    </div>

                    {{-- Asignar técnico --}}
                    @if($tecnicos->isNotEmpty())
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Asignar a</p>
                        <form method="POST" action="{{ route('soporte.assign', $ticket) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="assigned_to"
                                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Sin asignar</option>
                                @foreach($tecnicos as $t)
                                    <option value="{{ $t->id }}" {{ $ticket->assigned_to === $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-3 py-1.5 bg-gray-700 text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition-colors">
                                OK
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Acción: cerrar ticket (dueño) --}}
                @if(!$ticket->isClosed() && $ticket->user_id === Auth::id())
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 mb-2">¿Se resolvió el problema?</p>
                    <form method="POST" action="{{ route('soporte.close', $ticket) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                            <i class="fas fa-check mr-1"></i> Marcar como resuelto
                        </button>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
