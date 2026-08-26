<style>
    .sort-icon {
        display: none;
        margin-left: 5px;
        transition: transform 0.2s;
    }

    .asc .sort-icon,
    .desc .sort-icon {
        display: inline-block;
    }

    .asc .sort-icon {
        transform: rotate(180deg);
    }

    .desc .sort-icon {
        transform: rotate(0deg);
    }

    th {
        cursor: pointer;
        user-select: none;
    }

    th:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

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
                   placeholder="Buscar por RUN, Nombre, Correo, Teléfono o Tipo..."
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
            <span>Total: <strong>{{ $visitantes->total() }}</strong> visitantes</span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $visitantes->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="visitantes-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 cursor-pointer hover:bg-black hover:bg-opacity-10" wire:click="sortBy('run_solicitante')">
                        RUN Solicitante
                        @if($sortField === 'run_solicitante')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="ml-1 opacity-40">↕</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer hover:bg-black hover:bg-opacity-10" wire:click="sortBy('nombre')">
                        Nombre
                        @if($sortField === 'nombre')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="ml-1 opacity-40">↕</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer hover:bg-black hover:bg-opacity-10" wire:click="sortBy('correo')">
                        Correo
                        @if($sortField === 'correo')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="ml-1 opacity-40">↕</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer hover:bg-black hover:bg-opacity-10" wire:click="sortBy('telefono')">
                        Teléfono
                        @if($sortField === 'telefono')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="ml-1 opacity-40">↕</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer hover:bg-black hover:bg-opacity-10" wire:click="sortBy('tipo_solicitante')">
                        Tipo
                        @if($sortField === 'tipo_solicitante')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="ml-1 opacity-40">↕</span>
                        @endif
                    </th>
                    <th class="p-3">Estado</th>
                    <th class="p-3">Fecha Registro</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitantes as $index => $visitante)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $visitante->run_solicitante }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $visitante->nombre }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $visitante->correo }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $visitante->telefono ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($visitante->tipo_solicitante === 'estudiante') bg-blue-100 text-blue-800
                                @elseif($visitante->tipo_solicitante === 'personal') bg-green-100 text-green-800
                                @elseif($visitante->tipo_solicitante === 'visitante') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($visitante->tipo_solicitante) }}
                            </span>
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($visitante->activo) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                {{ $visitante->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($visitante->fecha_registro)->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" type="button"
                                    class="inline-flex items-center px-4 py-2"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-visitante-{{ $visitante->id }}')">
                                    <x-icons.edit class="w-5 h-5" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $visitante->id }}"
                                    action="{{ route('visitantes.delete', $visitante->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="deleteVisitante('{{ $visitante->id }}', '{{ $visitante->nombre }}')"
                                        class="px-4 py-2">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron visitantes</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $visitantes->links('vendor.pagination.tailwind') }}
    </div>
</div>
