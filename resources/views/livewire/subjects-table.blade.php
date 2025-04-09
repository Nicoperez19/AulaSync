<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Asignatura</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Horas Directas</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Horas Indirectas</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Área de Conocimiento</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Periodo</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Docente Responsable</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Carrera</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($asignaturas as $index => $asignatura)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Asignatura">
                        {{ $asignatura->id_asignatura }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $asignatura->nombre }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Horas Directas">
                        {{ $asignatura->horas_directas }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Horas Indirectas">
                        {{ $asignatura->horas_indirectas }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Área Conocimiento">
                        {{ $asignatura->area_conocimiento }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Periodo">
                        {{ $asignatura->periodo }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Docente Responsable">
                        {{ optional($asignatura->usuario)->name ?? 'No asignado' }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Carrera">
                        {{ optional($asignatura->carrera)->nombre ?? 'No asignada' }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('asignaturas.edit', $asignatura->id_asignatura) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form method="POST" action="{{ route('asignaturas.destroy', $asignatura->id_asignatura) }}">
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
