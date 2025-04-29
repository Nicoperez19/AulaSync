<div>
    <div class="mt-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 ">RUN</th>
                    <th class="p-3 ">Nombre</th>
                    <th class="p-3 ">Correo</th>
                    <th class="p-3 ">Celular</th>
                    <th class="p-3 ">Dirección</th>
                    <th class="p-3 ">Fecha Nacimiento</th>
                    <th class="p-3 ">Año Ingreso</th>
                    <th class="p-3 ">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200' : 'bg-gray-100' }}">
                        <td class="p-3  dark:border-white whitespace-nowrap">{{ $user->run }}</td>
                        <td class="p-3  dark:border-white whitespace-nowrap">{{ $user->name }}</td>
                        <td class="p-3  dark:border-white whitespace-nowrap">{{ $user->email }}</td>
                        <td class="p-3  dark:border-white whitespace-nowrap">{{ $user->celular }}
                        </td>
                        <td class="p-3  dark:border-white whitespace-nowrap">{{ $user->direccion }}
                        </td>
                        <td class="p-3  dark:border-white whitespace-nowrap">
                            {{ $user->fecha_nacimiento }}</td>
                        <td class="p-3  dark:border-white whitespace-nowrap">
                            {{ $user->anio_ingreso }}</td>
                        <td class="p-3  dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button variant="primary" href="{{ route('users.edit', $user->id) }}"
                                    class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    <x-icons.edit class="w-5 h-5" aria-hidden="true" />

                                </x-button>
                                <form id="delete-form-{{ $user->id }}"
                                    action="{{ route('users.delete', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="confirmDelete('{{ $user->id }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
</div>
