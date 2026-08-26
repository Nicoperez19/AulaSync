<div>
    <div class="flex flex-col gap-4 mb-4 md:flex-row md:items-center md:justify-between">
        <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por ID, Espacio, Docente/Solicitante o Fecha..."
                   class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <span>Total: <strong>{{ $reservas->total() }}</strong> reservas</span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $reservas->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id_reserva')">
                        ID Reserva
                        @if($sortField === 'id_reserva')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('fecha_reserva')">
                        Fecha
                        @if($sortField === 'fecha_reserva')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Horario</th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id_espacio')">
                        Espacio
                        @if($sortField === 'id_espacio')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Usuario / Solicitante</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $index => $reserva)
                    <tr wire:key="reserva-row-{{ $reserva->id_reserva }}" class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 dark:hover:bg-gray-800">
                        <td class="p-3 font-semibold text-blue-600 border border-white dark:border-gray-700 dark:text-blue-400">
                            {{ $reserva->id_reserva }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            {{ $reserva->fecha_reserva ? \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            {{ substr($reserva->hora, 0, 5) }} - {{ substr($reserva->hora_salida, 0, 5) }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap font-medium">
                            {{ $reserva->id_espacio }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex flex-col items-center">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $reserva->nombre_usuario }}</span>
                                <span class="text-xs text-gray-500">{{ $reserva->run_profesor ?: $reserva->run_solicitante }}</span>
                            </div>
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            @if($reserva->estado === 'activa')
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">Activa</span>
                            @elseif($reserva->estado === 'finalizada')
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">Finalizada</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">{{ ucfirst($reserva->estado) }}</span>
                            @endif
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('reservas.edit', $reserva->id_reserva) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>
                                <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}" class="reserva-delete-form inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="submit" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700 btn-delete-reserva" data-espacio="{{ $reserva->id_espacio }}">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr wire:key="empty-reservas-row">
                        <td colspan="7" class="p-6 text-center text-gray-500 dark:text-gray-400">
                            No se encontraron reservas que coincidan con la búsqueda "{{ $search }}".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reservas->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    // Interceptar clicks en botones de eliminar reservas para notificar otras pestañas
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-reserva').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const espacioId = this.getAttribute('data-espacio');
                const form = this.closest('form');
                if (!form) return;

                // Guardar en localStorage para notificar otras pestañas
                localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));

                // Enviar el formulario para eliminar en esta pestaña
                form.submit();
            });
        });
    });
</script>
