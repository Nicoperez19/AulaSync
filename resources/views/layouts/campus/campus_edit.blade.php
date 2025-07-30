<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Campus</h2>
                    <p class="text-sm text-gray-500">Administra los campus disponibles en el sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('campus.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-m font-medium border border-gray-300 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-campus-form" action="{{ route('campus.update', $campus->id_campus) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_campus" :value="__('ID Campus')" />
                        <x-form.input id="id_campus" class="block w-full" type="text" name="id_campus"
                            value="{{ old('id_campus', $campus->id_campus) }}" required />
                    </div>

                    <div>
                        <x-form.label for="nombre_campus" :value="__('Nombre Campus')" />
                        <x-form.input id="nombre_campus" class="block w-full" type="text" name="nombre_campus"
                            value="{{ old('nombre_campus', $campus->nombre_campus) }}" required />
                    </div>
                </div>

                <div>
                    <x-form.label for="id_sede" :value="__('Sede')" />
                    <select name="id_sede" id="id_sede"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                        required>
                        <option value="" disabled>{{ __('Seleccionar Sede') }}</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id_sede }}" 
                                {{ $campus->id_sede == $sede->id_sede ? 'selected' : '' }}>
                                {{ $sede->nombre_sede }} - {{ $sede->universidad->nombre_universidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-campus-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Seguro de editar?',
                        text: "Estás a punto de guardar los cambios.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, editar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout> 