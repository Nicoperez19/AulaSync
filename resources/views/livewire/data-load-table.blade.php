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

<div class="bg-white rounded-xl shadow border">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm border-collapse">
            <thead>
                <tr class="bg-red-600 text-white">
                    <th class="px-4 py-3 text-center font-semibold">Documento</th>
                    <th class="px-4 py-3 text-center font-semibold">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold">Fecha</th>
                    <th class="px-4 py-3 text-center font-semibold">Usuario</th>
                    <th class="px-4 py-3 text-center font-semibold">Registros</th>
                    <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataLoads as $index => $data)
                    <tr class="border-b last:border-0 hover:bg-gray-100">
                        <td class="px-4 py-3">
                            <div class="flex flex-col items-center justify-center gap-2">
                              
                                <span
                                    class="font-semibold text-gray-800 text-sm text-center">{{ $data->nombre_archivo }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $estado = strtolower($data->estado ?? 'procesado');
                            @endphp
                            @if($estado === 'procesado' || $estado === 'completado')
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Procesado</span>
                            @elseif($estado === 'en proceso' || $estado === 'procesando')
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">En
                                    proceso</span>
                            @elseif($estado === 'error')
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Error</span>
                            @else
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">{{ ucfirst($estado) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ $data->created_at->format('Y-m-d H:i') }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 bg-blue-100 rounded-full">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 12c2.7 0 8 1.34 8 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z" />
                                    </svg>
                                </span>
                                <span class="font-semibold text-gray-800">{{ $data->user->name ?? 'Desconocido' }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(isset($data->registros_cargados) && is_numeric($data->registros_cargados))
                                <span class="inline-flex items-center gap-1 text-green-600 font-semibold">
                                    {{$data->registros_cargados }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" onclick="verDetalleCarga({{ $data->id }})" class="text-blue-600 hover:text-blue-800" title="Ver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <form id="delete-form-{{ $data->id }}" action="{{ route('data.destroy', $data->id) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="deleteData('{{ $data->id }}')"
                                        class="text-red-600 hover:text-red-800" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">No se encontraron registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-end items-center mt-4">
        {{ $dataLoads->links() }}
    </div>
</div>

<script>
    function deleteData(id) {
        if (confirm('¿Está seguro de que desea eliminar este registro?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>