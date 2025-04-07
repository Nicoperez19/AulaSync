<div class="w-full min-h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div
        class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="hidden lg:table-header-group @class([
                'text-black border-b border-white',
                'bg-gray-50 dark:bg-black',
                'dark:text-white' => config('app.dark_mode'),
            ])">
                <tr>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Facultad</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Piso</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Tipo</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Estado</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Puestos</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($espacios as $index => $espacio)
                    <tr class="@class([
                        'text-black',
                        'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                        'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                        'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                        'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                    ])">

                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->facultad->nombre ?? 'Sin Facultad' }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->nombre_piso ?? 'Sin piso' }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->tipo_espacio }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $espacio->estado }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->puestos_disponibles ?? 'N/A' }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button href="{{ route('espacios.edit', $espacio->id_espacio) }}"
                                    class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    Editar
                                </x-button>
                                <form action="{{ route('espacios.destroy', $espacio->id_espacio) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        Eliminar
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
