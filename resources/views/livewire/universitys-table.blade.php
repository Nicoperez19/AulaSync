<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Universidad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Dirección</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Teléfono</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Imagen</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($universidades as $index => $universidad)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Universidad">
                        {{ $universidad->id_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $universidad->nombre_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Dirección">
                        {{ $universidad->direccion_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Teléfono">
                        {{ $universidad->telefono_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Imagen">
                        <img src="{{ asset('storage/' . $universidad->imagen_logo) }}" alt="Logo"
                            class="object-cover w-16 h-16 rounded">
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('universitys.edit', $universidad->id_universidad) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form action="{{ route('universitys.delete', $universidad->id_universidad) }}"
                                method="POST" style="display: inline;">
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
