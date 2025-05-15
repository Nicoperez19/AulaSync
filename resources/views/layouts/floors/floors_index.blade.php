<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl italic font-semibold leading-tight">
                {{ __('Facultades y Pisos') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-gray-100 rounded-lg shadow-lg">
        <div class="w-full space-y-6">
            <h3 class="text-lg font-semibold">{{ __('Listado de Facultades') }}</h3>
            <div class="p-4 bg-white rounded shadow">
                <form method="GET" action="{{ route('floors_index') }}" class="space-y-4">
                    <div>
                        <label for="universidad" class="block text-sm font-medium text-gray-700">Universidad</label>
                        <select id="universidad" name="universidad"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
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

                    <div class="flex justify-end gap-2 pt-4">
                        <a href="{{ route('floors_index') }}"
                           class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="p-4 bg-white border rounded">
                <h4 class="mb-4 text-lg font-semibold">Facultades</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border border-collapse rounded">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left border">Facultad</th>
                                <th class="px-4 py-2 text-left border">Número de Pisos</th>
                                <th class="px-4 py-2 text-center border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facultades as $facultad)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $facultad->nombre_facultad }}</td>
                                    <td class="px-4 py-2 border">{{ $facultad->pisos_count }}</td>
                                    <td class="px-4 py-2 text-center border">
                                        <div class="flex justify-center gap-2">
                                            <form
                                                action="{{ route('floors.agregarPiso', ['facultadId' => $facultad->id_facultad, 'universidad' => request('universidad')]) }}"
                                                method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-2 text-white bg-green-500 rounded hover:bg-green-600"
                                                        title="Agregar Piso">
                                                    +
                                                </button>
                                            </form>
                                            <form
                                                action="{{ route('floors.eliminarPiso', ['facultadId' => $facultad->id_facultad, 'universidad' => request('universidad')]) }}"
                                                method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-2 text-white bg-red-500 rounded hover:bg-red-600"
                                                        title="Eliminar Piso">
                                                    -
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif
    </script>
</x-app-layout>
