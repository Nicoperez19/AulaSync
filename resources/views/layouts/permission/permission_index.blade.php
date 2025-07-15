<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Permisos</h2>
                    <p class="text-sm text-gray-500">Administra los permisos asignables a roles y usuarios</p>
                </div>
            </div>

        </div>
    </x-slot>
    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-permission')" variant="primary"
            class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
        <div class="flex justify-center">
            <table class="w-full text-center border-collapse table-auto min-w-max">
                <thead class="hidden lg:table-header-group @class([
                    'text-black border-b border-white',
                    'bg-gray-50 dark:bg-black',
                    'dark:text-white' => config('app.dark_mode'),
                ])">
                    <tr>
                        <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID</th>
                        <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                        <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $index => $permission)
                        <tr id="permission-row-{{ $permission->id }}" class="@class([
                            'text-black',
                            'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                            'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                            'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                            'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                        ])">
                            <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="RUN">
                                {{ $permission->id }}
                            </td>
                            <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                                {{ $permission->name }}
                            </td>
                            <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                                <x-button
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-permission-{{ $permission->id }}')"
                                    variant="primary" class="gap-2">
                                    <x-icons.edit class="w-6 h-6" aria-hidden="true" />
                                </x-button>

                                <form action="{{ route('permission.delete', $permission->id) }}" method="POST"
                                    id="delete-form-{{ $permission->id }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')

                                    <x-button type="button"
                                        onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')"
                                        variant="danger" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-6 h-6" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-modal name="add-permission" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('permission.add') }}">
            @csrf
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <x-form.label for="name_permission" :value="__('Nombre del Permiso')" class="text-left" />
                    <x-form.input id="name_permission" class="block w-full" type="text" name="name" required autofocus
                        placeholder="{{ __('Nombre del permiso') }}" />
                </div>
                <div class="flex justify-end">
                    <x-button class="justify-center w-full gap-2">
                        <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                        {{ __('Agregar Permiso') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    @foreach ($permissions as $permission)
        <x-modal name="edit-permission-{{ $permission->id }}" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <x-form.label for="name_permission_{{ $permission->id }}" :value="__('Nombre del Permiso')"
                            class="text-left" />
                        <x-form.input id="name_permission_{{ $permission->id }}" class="block w-full" type="text"
                            name="name" value="{{ $permission->name }}" required autofocus
                            placeholder="{{ __('Nombre del permiso') }}" />
                    </div>
                    <div class="flex justify-end">
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-pencil class="w-6 h-6" aria-hidden="true" />
                            {{ __('Actualizar Permiso') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    @endforeach

    <!-- Incluir SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif
        const form = document.getElementById('edit-permission-form');

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Evita el envío inmediato

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
                        form.submit(); // Envía el formulario si se confirma
                    }
                });
            });
        }
        // Función para confirmar la eliminación
        function confirmDelete(permissionID) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esta acción!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, enviar el formulario
                    document.getElementById('delete-form-' + permissionID).submit();
                }
            });
        }
    </script>
</x-app-layout>