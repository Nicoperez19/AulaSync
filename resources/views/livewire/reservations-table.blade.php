<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Reserva</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Hora</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Fecha Reserva</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Espacio</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Usuario</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservas as $index => $reserva)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Reserva">
                        {{ $reserva->id_reserva }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Hora">
                        {{ $reserva->hora }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Fecha Reserva">
                        {{ $reserva->fecha_reserva }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Espacio">
                        {{ $reserva->id_espacio }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Usuario">
                        {{ optional($reserva->user)->name ?? 'Usuario no asignado' }}
                    </td>

                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('reservas.edit', $reserva->id_reserva) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}">
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