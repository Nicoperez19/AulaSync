<div>
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

    <!-- Barra de búsqueda y acciones -->
    <div class="flex flex-col gap-4 mb-4 md:flex-row md:items-center md:justify-between">
        <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por Nombre de archivo o Usuario..."
                   class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total: <strong>{{ $dataLoads->total() }}</strong> archivos</span>
            @if (count($selected) >= 2)
                <button type="button" onclick="confirmDeleteSelected()" class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-white bg-red-700 hover:bg-red-800 rounded-lg shadow-sm hover:shadow transition duration-150 shrink-0">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar seleccionados
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white border shadow rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm border-collapse">
                <thead>
                    <tr class="text-white bg-red-600">
                        <th class="px-4 py-3 font-semibold text-center w-12 cursor-default" onclick="event.stopPropagation()">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 font-semibold text-center">Documento</th>
                        <th class="px-4 py-3 font-semibold text-center">Estado</th>
                        <th class="px-4 py-3 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-center">Usuario</th>
                        <th class="px-4 py-3 font-semibold text-center">Registros</th>
                        <th class="px-4 py-3 font-semibold text-center cursor-default" onclick="event.stopPropagation()">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataLoads as $index => $data)
                        <tr wire:key="row-{{ $data->id }}" class="border-b last:border-0 hover:bg-gray-100">
                            <!-- Checkbox individual -->
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" value="{{ $data->id }}" wire:model.live="selected" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-sm font-semibold text-center text-gray-800">{{ $data->nombre_archivo }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $estado = strtolower($data->estado ?? 'procesado');
                                @endphp
                                @if($estado === 'procesado' || $estado === 'completado')
                                    <span class="inline-block px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Procesado</span>
                                @elseif($estado === 'en proceso' || $estado === 'procesando')
                                    <span class="inline-block px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">En proceso</span>
                                @elseif($estado === 'error')
                                    <span class="inline-block px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Error</span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">{{ ucfirst($estado) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $data->created_at->format('Y-m-d H:i') }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center bg-blue-100 rounded-full w-7 h-7">
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.7 0 8 1.34 8 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z" />
                                        </svg>
                                    </span>
                                    <span class="font-semibold text-gray-800">{{ $data->user->name ?? 'Desconocido' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(isset($data->registros_cargados) && is_numeric($data->registros_cargados))
                                    <span class="inline-flex items-center gap-1 font-semibold text-green-600">
                                        {{ $data->registros_cargados }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button type="button" onclick="verDetalleCarga({{ $data->id }})" class="text-blue-600 hover:text-blue-800" title="Ver detalles">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <a href="{{ route('data.download', $data->id) }}" class="text-green-600 hover:text-green-800" title="Descargar archivo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmDeleteSingle({{ $data->id }})" class="text-red-600 hover:text-red-800" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">No se encontraron registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-end mt-4 px-4 pb-4">
            {{ $dataLoads->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Escuchadores de eventos globales show-success y show-error para SweetAlert
        Livewire.on('show-success', (data) => {
            const message = data[0]?.message || 'Operación completada exitosamente.';
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        });

        Livewire.on('show-error', (data) => {
            const message = data[0]?.message || 'Ocurrió un error al procesar la solicitud.';
            Swal.fire({
                title: '¡Error!',
                text: message,
                icon: 'error',
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
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
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
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteSelected');
            }
        });
    }
</script>
