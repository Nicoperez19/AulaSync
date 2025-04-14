<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">RUN</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Correo</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Celular</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Dirección</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Fecha Nacimiento</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Año Ingreso</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $index => $user)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->run }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->name }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->email }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->celular }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->direccion }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->fecha_nacimiento }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->anio_ingreso }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('users.edit', $user->id) }}" class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form action="{{ route('users.delete', $user->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
