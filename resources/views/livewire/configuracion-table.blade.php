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
                       placeholder="Buscar por Clave, Valor o Descripción..."
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
                    Total: <strong class="text-gray-800 dark:text-white">{{ $configuraciones->total() }}</strong> configuraciones
                </span>
            </div>
        </div>
    </div>

    <!-- Card Tabla Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700" wire:loading.class="opacity-60">
        <div class="overflow-x-auto">
            <table id="config-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('clave')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Clave
                                @if($sortField === 'clave')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Valor
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('descripcion')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Descripción
                                @if($sortField === 'descripcion')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-48 px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($configuraciones as $configuracion)
                        <tr wire:key="config-row-{{ $configuracion->id }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                                @if(str_starts_with($configuracion->clave, 'logo_institucional_'))
                                    @php
                                        $idSede = str_replace('logo_institucional_', '', $configuracion->clave);
                                        $sede = \App\Models\Sede::find($idSede);
                                    @endphp
                                    <span class="block">{{ $configuracion->clave }}</span>
                                    <span class="block text-xs text-gray-500">Sede: {{ $sede ? $sede->nombre_sede : $idSede }}</span>
                                @elseif(str_starts_with($configuracion->clave, 'correo_administrativo_') || str_starts_with($configuracion->clave, 'nombre_remitente_'))
                                    @php
                                        $idFacultad = str_replace(['correo_administrativo_', 'nombre_remitente_'], '', $configuracion->clave);
                                        $facultad = \App\Models\Facultad::find($idFacultad);
                                        $tipo = str_starts_with($configuracion->clave, 'correo_administrativo_') ? 'Correo' : 'Nombre';
                                    @endphp
                                    <span class="block">{{ $configuracion->clave }}</span>
                                    <span class="block text-xs text-gray-500">{{ $tipo }} - Escuela: {{ $facultad ? $facultad->nombre_facultad : $idFacultad }}</span>
                                @else
                                    {{ $configuracion->clave }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle text-gray-700 dark:text-gray-300">
                                @if(str_starts_with($configuracion->clave, 'logo_institucional_') && $configuracion->valor)
                                    <img src="{{ asset('storage/images/logo/' . $configuracion->valor) }}" alt="Logo" class="h-10">
                                @else
                                    {{ Str::limit($configuracion->valor, 50) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle text-gray-600 dark:text-gray-400">
                                {{ $configuracion->descripcion ?? 'N/A' }}
                            </td>
                            <td class="w-48 px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('configuracion.edit', $configuracion->id) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                       title="Editar configuración">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </a>

                                    <form id="delete-form-{{ $configuracion->id }}"
                                          action="{{ route('configuracion.destroy', $configuracion->id) }}"
                                          method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="deleteConfiguracion('{{ $configuracion->id }}')"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                                title="Eliminar configuración">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-configuraciones-row">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-sliders text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron configuraciones</p>
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
            {{ $configuraciones->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

<script>
    function deleteConfiguracion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
