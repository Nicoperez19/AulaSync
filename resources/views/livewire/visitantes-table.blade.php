<div>
    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="relative w-full md:w-1/2">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por RUN, Nombre, Correo, Teléfono o Tipo..."
                       class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <span class="bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                    Total: <strong class="text-gray-800 dark:text-white">{{ $visitantes->total() }}</strong> visitantes
                </span>
            </div>
        </div>
    </div>

    <!-- Card Tabla Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700" wire:loading.class="opacity-60">
        <div class="overflow-x-auto">
            <table id="visitantes-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="w-32 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('run_solicitante')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                RUN
                                @if($sortField === 'run_solicitante')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('nombre')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Nombre
                                @if($sortField === 'nombre')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('correo')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Correo
                                @if($sortField === 'correo')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-32 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('telefono')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Teléfono
                                @if($sortField === 'telefono')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-32 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('tipo_solicitante')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Tipo
                                @if($sortField === 'tipo_solicitante')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-24 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="w-36 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Fecha Registro
                        </th>
                        <th scope="col" class="w-48 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($visitantes as $visitante)
                        <tr wire:key="visitante-row-{{ $visitante->id }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="w-32 px-4 py-3 text-center align-middle whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                                {{ $visitante->run_solicitante }}
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap font-medium text-gray-800 dark:text-gray-200">
                                {{ $visitante->nombre }}
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap text-gray-600 dark:text-gray-400">
                                {{ $visitante->correo }}
                            </td>
                            <td class="w-32 px-4 py-3 text-center align-middle whitespace-nowrap text-gray-600 dark:text-gray-400">
                                {{ $visitante->telefono ?? 'N/A' }}
                            </td>
                            <td class="w-32 px-4 py-3 text-center align-middle whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border
                                    @if($visitante->tipo_solicitante === 'estudiante') bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-200
                                    @elseif($visitante->tipo_solicitante === 'personal') bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300
                                    @elseif($visitante->tipo_solicitante === 'visitante') bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-950 dark:text-purple-300
                                    @else bg-gray-100 text-gray-800 border-gray-200 @endif">
                                    {{ ucfirst($visitante->tipo_solicitante) }}
                                </span>
                            </td>
                            <td class="w-24 px-4 py-3 text-center align-middle whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border
                                    @if($visitante->activo) bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300
                                    @else bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-300 @endif">
                                    {{ $visitante->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="w-36 px-4 py-3 text-center align-middle whitespace-nowrap text-gray-600 dark:text-gray-400 text-xs">
                                {{ \Carbon\Carbon::parse($visitante->fecha_registro)->format('d/m/Y H:i') }}
                            </td>
                            <td class="w-48 px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            x-on:click.prevent="$dispatch('open-modal', 'edit-visitante-{{ $visitante->id }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                            title="Editar visitante">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </button>

                                    <form id="delete-form-{{ $visitante->id }}"
                                          action="{{ route('visitantes.delete', $visitante->id) }}"
                                          method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="deleteVisitante('{{ $visitante->id }}', '{{ addslashes($visitante->nombre) }}')"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                                title="Eliminar visitante">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                                                title="Eliminar visitante">
                                            <x-icons.delete class="w-3.5 h-3.5" aria-hidden="true" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-visitantes-row">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-id-badge text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron visitantes</p>
                                    <p class="text-xs text-gray-400 mt-1">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer de paginación integrado --}}
        <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
            {{ $visitantes->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
