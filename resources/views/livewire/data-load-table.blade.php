<div>
    <!-- Card Filtros y Acciones -->
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
                       placeholder="Buscar por Nombre de archivo o Usuario..."
                       class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                    Total: <strong class="text-gray-800 dark:text-white">{{ $dataLoads->total() }}</strong> archivos
                </span>
                @if (count($selected) >= 2)
                    <button type="button" onclick="confirmDeleteSelected()" class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#D12420] hover:bg-[#950101] rounded-lg shadow-sm transition duration-150 shrink-0">
                        <i class="fas fa-trash-alt"></i>
                        Eliminar seleccionados
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12 cursor-default" onclick="event.stopPropagation()">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-gray-300 text-[#D12420] focus:ring-[#950101]">
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Documento</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Estado</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Fecha</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Usuario</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Registros</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center min-w-[240px] cursor-default" onclick="event.stopPropagation()">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($dataLoads as $index => $data)
                        <tr wire:key="row-{{ $data->id }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <!-- Checkbox individual -->
                            <td class="px-4 py-3 text-center align-middle">
                                <input type="checkbox" value="{{ $data->id }}" wire:model.live="selected" class="w-4 h-4 rounded border-gray-300 text-[#D12420] focus:ring-[#950101]">
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-2 justify-center">
                                    <i class="fa-solid fa-file-excel text-emerald-600 text-base"></i>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $data->nombre_archivo }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                @php
                                    $estado = strtolower($data->estado ?? 'procesado');
                                @endphp
                                @if($estado === 'procesado' || $estado === 'completado')
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-full">Procesado</span>
                                @elseif($estado === 'en proceso' || $estado === 'procesando')
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full">En proceso</span>
                                @elseif($estado === 'error')
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Error</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">{{ ucfirst($estado) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $data->created_at->format('Y-m-d H:i') }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center bg-gray-100 text-gray-600 rounded-full w-7 h-7 text-xs font-semibold">
                                        {{ substr($data->user->name ?? 'D', 0, 1) }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $data->user->name ?? 'Desconocido' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                @if(isset($data->registros_cargados) && is_numeric($data->registros_cargados))
                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                                        {{ $data->registros_cargados }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button" onclick="verDetalleCarga({{ $data->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg border border-blue-200 transition-colors shadow-xs"
                                        title="Ver detalles">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span>Detalle</span>
                                    </button>
                                    <a href="{{ route('data.download', $data->id) }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg border border-emerald-200 transition-colors shadow-xs"
                                        title="Descargar archivo">
                                        <i class="fa-solid fa-download text-xs"></i>
                                        <span>Descargar</span>
                                    </a>
                                    <button type="button" onclick="confirmDeleteSingle({{ $data->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                        <span>Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No se encontraron registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dataLoads->hasPages())
            <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
                {{ $dataLoads->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Livewire.on('show-success', (data) => {
            const message = data[0]?.message || 'Operación completada exitosamente.';
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Aceptar'
            });
        });

        Livewire.on('show-error', (data) => {
            const message = data[0]?.message || 'Ocurrió un error al procesar la solicitud.';
            Swal.fire({
                title: '¡Error!',
                text: message,
                icon: 'error',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Aceptar'
            });
        });
    });

    function confirmDeleteSingle(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: '¿Desea eliminar este registro de carga? Al hacerlo, se eliminará permanentemente toda la información de planificación y horarios importados desde este archivo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteSingle', id);
            }
        });
    }

    function confirmDeleteSelected() {
        Swal.fire({
            title: '¿Está seguro?',
            text: '¿Desea eliminar los registros de carga seleccionados? Al hacerlo, se eliminará permanentemente toda la información de planificación y horarios importados desde estos archivos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteSelected');
            }
        });
    }
</script>
