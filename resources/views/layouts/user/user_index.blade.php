<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios / Usuarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-gray-100 rounded-lg shadow-lg">
        <div class="flex justify-end mb-4">
            <x-button target="_blank" variant="primary" class="max-w-xs gap-2"
                x-on:click="$dispatch('open-modal', 'add-user')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>

        @livewire('users-table')

        <!-- Modal Agregar Usuario -->
        <x-modal name="add-user" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('users.add') }}">
                @csrf
                <div class="grid gap-6 p-6">
                    @foreach ([
                        'run' => __('RUN'),
                        'name' => __('Nombre'),
                        'email' => __('Correo'),
                        'celular' => __('Celular'),
                        'direccion' => __('Dirección'),
                        'fecha_nacimiento' => __('Fecha de Nacimiento'),
                        'anio_ingreso' => __('Año de Ingreso')
                    ] as $field => $label)
                        <div class="space-y-2">
                            <x-form.label for="{{ $field }}_add" :value="$label" class="text-left" />
                            <x-form.input id="{{ $field }}_add" class="block w-full sm:w-1/2" 
                                type="{{ $field === 'fecha_nacimiento' ? 'date' : ($field === 'anio_ingreso' ? 'number' : 'text') }}"
                                name="{{ $field }}" :value="old($field)" required placeholder="{{ $label }}" />
                        </div>
                    @endforeach

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function confirmDelete(userId) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + userId).submit();
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
