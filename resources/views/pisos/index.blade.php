<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Facultades y Pisos') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Filtro de Universidad -->
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Filtrar por Universidad') }}</h3>
            <form method="GET" action="{{ route('floors_index') }}" class="space-y-4">
                <div>
                    <label for="universidad" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Universidad</label>
                    <select id="universidad" name="universidad"
                            class="block w-full max-w-md text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            onchange="this.form.submit()">
                        <option value="" disabled selected>Selecciona una Universidad</option>
                        @foreach ($universidades as $universidad)
                            <option value="{{ $universidad->id_universidad }}"
                                    {{ request('universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                {{ $universidad->nombre_universidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('floors_index') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none transition-colors dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                        <i class="mr-2 fa-solid fa-rotate-left text-xs"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Listado de Facultades y Pisos -->
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Listado de Facultades</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Facultad</th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Número de Pisos</th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center w-56">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse ($facultades as $facultad)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 align-middle">{{ $facultad->nombre_facultad }}</td>
                                <td class="px-4 py-3 text-center align-middle font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $facultad->pisos_count }} {{ $facultad->pisos_count == 1 ? 'piso' : 'pisos' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <form action="{{ route('floors.agregarPiso', ['facultadId' => $facultad->id_facultad, 'universidad' => request('universidad')]) }}"
                                              method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg border border-emerald-300 transition-colors shadow-xs"
                                                    title="Agregar Piso">
                                                <i class="fa-solid fa-plus text-xs"></i>
                                                <span>Agregar Piso</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('floors.eliminarPiso', ['facultadId' => $facultad->id_facultad, 'universidad' => request('universidad')]) }}"
                                              method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                                    title="Eliminar Piso">
                                                <i class="fa-solid fa-minus text-xs"></i>
                                                <span>Eliminar Piso</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    No se encontraron facultades
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
