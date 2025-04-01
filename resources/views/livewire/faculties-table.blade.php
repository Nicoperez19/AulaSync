<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Facultad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Ubicación</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Universidad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Logo</th> 
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facultades as $index => $facultad)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Facultad">
                        {{ $facultad->id_facultad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $facultad->nombre }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Ubicación">
                        {{ $facultad->ubicacion }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Universidad">
                        {{ $facultad->universidad->nombre_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Logo">
                        <img src="{{ asset('storage/'.$facultad->logo_facultad) }}" alt="Logo de la facultad" class="object-cover w-12 h-12 rounded">
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('faculties.edit', $facultad->id_facultad) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form method="POST" action="{{ route('faculties.delete', $facultad->id_facultad) }}">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
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
