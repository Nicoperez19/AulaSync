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
                   placeholder="Buscar por ID, Nombre, Tipo, Estado, Piso o Facultad..."
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
            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                Total: <strong class="text-gray-800 dark:text-white">{{ $espacios->total() }}</strong> espacios
            </span>
        </div>
    </div>

    <!-- Card Tabla de Espacios Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700" wire:loading.class="opacity-60">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-900/60 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('id_espacio')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                ID Espacio
                                @if($sortField === 'id_espacio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('nombre_espacio')">
                            <span class="inline-flex items-center gap-1">
                                Nombre del Espacio
                                @if($sortField === 'nombre_espacio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Facultad / Sede
                        </th>
                        <th scope="col" class="w-24 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Piso
                        </th>
                        <th scope="col" class="w-36 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('tipo_espacio')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Tipo
                                @if($sortField === 'tipo_espacio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-28 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('estado')">
                            <span class="inline-flex items-center gap-1">
                                Estado
                                @if($sortField === 'estado')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-24 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('puestos_disponibles')">
                            <span class="inline-flex items-center gap-1">
                                Puestos
                                @if($sortField === 'puestos_disponibles')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-36 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($espacios as $espacio)
                        <tr wire:key="espacio-row-{{ $espacio->id_espacio }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-blue-600 dark:text-blue-400 text-sm">{{ $espacio->id_espacio }}</span>
                            </td>
                            <td class="px-3 py-3 text-left align-middle whitespace-nowrap">
                                <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $espacio->nombre_espacio ?? 'Sin nombre' }}</span>
                            </td>
                            <td class="px-3 py-3 text-left align-middle whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-300">{{ $espacio->piso->facultad->nombre_facultad ?? 'Sin Facultad' }}</div>
                                <div class="text-xs text-gray-500">Sede {{ $espacio->piso->facultad->sede->nombre_sede ?? 'Sin nombre' }}</div>
                            </td>
                            <td class="w-24 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600">
                                    Piso {{ $espacio->piso->numero_piso ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="w-36 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $espacio->tipo_espacio }}</span>
                            </td>
                            <td class="w-28 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full 
                                    @if ($espacio->estado === 'Disponible') bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800
                                    @elseif($espacio->estado === 'Ocupado') bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-800
                                    @else bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800 @endif">
                                    {{ $espacio->estado }}
                                </span>
                            </td>
                            <td class="w-24 px-3 py-3 text-center align-middle whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium">
                                {{ $espacio->puestos_disponibles ?? 'N/A' }}
                            </td>
                            <td class="w-36 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('spaces.edit', $espacio->id_espacio) }}"
                                       class="inline-flex items-center justify-center p-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors shadow-xs dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700"
                                       title="Editar espacio">
                                        <i class="fa-solid fa-edit w-3.5 h-3.5"></i>
                                    </a>

                                    <a href="{{ route('spaces.download-qr', $espacio->id_espacio) }}"
                                       class="inline-flex items-center justify-center p-1.5 border border-amber-300 text-xs font-medium rounded-md text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors shadow-xs dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700"
                                       title="Descargar QR">
                                        <i class="fa-solid fa-qrcode w-3.5 h-3.5"></i>
                                    </a>

                                    <form action="{{ route('spaces.delete', $espacio->id_espacio) }}" method="POST" class="inline-block" id="delete-form-{{ $espacio->id_espacio }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('delete-form-{{ $espacio->id_espacio }}')"
                                                class="inline-flex items-center justify-center p-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 transition-colors shadow-xs dark:bg-red-900/40 dark:text-red-300 dark:border-red-700"
                                                title="Eliminar espacio">
                                            <x-icons.delete class="w-3.5 h-3.5" aria-hidden="true" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-espacios-row">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-building-circle-xmark text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron espacios</p>
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
            {{ $espacios->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

<script>
    function confirmDelete(formId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(formId);
                if (form) {
                    form.submit();
                }
            }
        });
    }
</script>
