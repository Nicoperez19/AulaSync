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
            <!-- Botón para abrir el modal con AlpineJS -->
            <x-button target="_blank" variant="primary" class="justify-end max-w-xs gap-2"
                x-on:click="$dispatch('open-modal', 'add-user')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>

        <!-- Tabla de usuarios con paginación -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
                <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
                    <thead class="text-black bg-gray-50 dark:bg-black dark:text-white">
                        <tr>
                            <th class="p-3">RUN</th>
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Correo</th>
                            <th class="p-3">Celular</th>
                            <th class="p-3">Dirección</th>
                            <th class="p-3">Fecha Nacimiento</th>
                            <th class="p-3">Año Ingreso</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr class="{{ $index % 2 === 0 ? 'bg-gray-200' : 'bg-gray-100' }}">
                                <td class="p-3">{{ $user->run }}</td>
                                <td class="p-3">{{ $user->name }}</td>
                                <td class="p-3">{{ $user->email }}</td>
                                <td class="p-3">{{ $user->celular }}</td>
                                <td class="p-3">{{ $user->direccion }}</td>
                                <td class="p-3">{{ $user->fecha_nacimiento }}</td>
                                <td class="p-3">{{ $user->anio_ingreso }}</td>
                                <td class="p-3">
                                    <div class="flex justify-end space-x-2">
                                        <x-button href="{{ route('users.edit', $user->id) }}" class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                            Editar
                                        </x-button>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.delete', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('{{ $user->id }}')" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

       
      

        <!-- Modal Agregar Usuario -->
        <x-modal name="add-user" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('users.add') }}">
                @csrf
                <div class="grid gap-6 p-6">
                    <!-- Formularios del modal -->
                    <div class="space-y-2">
                        <x-form.label for="run_add" :value="__('RUN')" class="text-left" />
                        <x-form.input id="run_add" class="block w-1/2" type="text" name="run" :value="old('run')" required autofocus placeholder="{{ __('RUN') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="name_add" :value="__('Nombre')" class="text-left" />
                        <x-form.input id="name_add" class="block w-1/2" type="text" name="name" :value="old('name')" required placeholder="{{ __('Nombre') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="email_add" :value="__('Correo')" class="text-left" />
                        <x-form.input id="email_add" class="block w-1/2" type="email" name="email" :value="old('email')" required placeholder="{{ __('Correo') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="password_add" :value="__('Contraseña')" class="text-left" />
                        <x-form.input id="password_add" class="block w-1/2" type="password" name="password" required placeholder="{{ __('Contraseña') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="celular_add" :value="__('Celular')" class="text-left" />
                        <x-form.input id="celular_add" class="block w-1/2" type="text" name="celular" :value="old('celular')" required placeholder="{{ __('Celular') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="direccion_add" :value="__('Dirección')" class="text-left" />
                        <x-form.input id="direccion_add" class="block w-1/2" type="text" name="direccion" :value="old('direccion')" required placeholder="{{ __('Dirección') }}" />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="fecha_nacimiento_add" :value="__('Fecha de Nacimiento')" class="text-left" />
                        <x-form.input id="fecha_nacimiento_add" class="block w-1/2" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="anio_ingreso_add" :value="__('Año de Ingreso')" class="text-left" />
                        <x-form.input id="anio_ingreso_add" class="block w-1/2" type="number" name="anio_ingreso" :value="old('anio_ingreso')" required placeholder="{{ __('Año de Ingreso') }}" />
                    </div>

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
</x-app-layout>
