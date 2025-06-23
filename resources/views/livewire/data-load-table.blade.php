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
   

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th wire:click="sortBy('nombre_archivo')" class="p-3 cursor-pointer">
                        Documento
                        @if ($sortField === 'nombre_archivo')
                            @if ($sortDirection === 'asc')
                                <span class="sort-icon">▲</span>
                            @else
                                <span class="sort-icon">▼</span>
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('created_at')" class="p-3 cursor-pointer">
                        Fecha de Registro
                        @if ($sortField === 'created_at')
                            @if ($sortDirection === 'asc')
                                <span class="sort-icon">▲</span>
                            @else
                                <span class="sort-icon">▼</span>
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('user.run')" class="p-3 cursor-pointer">
                        RUN Usuario
                        @if ($sortField === 'user.run')
                            @if ($sortDirection === 'asc')
                                <span class="sort-icon">▲</span>
                            @else
                                <span class="sort-icon">▼</span>
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('user.name')" class="p-3 cursor-pointer">
                        Nombre Usuario
                        @if ($sortField === 'user.name')
                            @if ($sortDirection === 'asc')
                                <span class="sort-icon">▲</span>
                            @else
                                <span class="sort-icon">▼</span>
                            @endif
                        @endif
                    </th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataLoads as $index => $data)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200 dark:bg-gray-800' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $data->nombre_archivo }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $data->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $data->user->run ?? 'N/A' }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $data->user->name ?? 'Desconocido' }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('data.show', $data->id) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.view class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $data->id }}"
                                    action="{{ route('data.destroy', $data->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="deleteData('{{ $data->id }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">
                            No se encontraron registros
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
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
