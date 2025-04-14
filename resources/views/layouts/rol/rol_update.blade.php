<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios/ Roles/ Edición') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-center">
        <form method="POST" id="edit-role-form" action="{{ route('roles.update', $role->id) }}"
            class="w-full max-w-lg p-6 bg-white rounded-lg shadow-md">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <!-- Nombre -->
                <div class="space-y-2">
                    <x-form.label for="name_rol" :value="__('Nombre del Rol')" />
                    <x-form.input-with-icon-wrapper>
                        <x-slot name="icon">
                            <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                        </x-slot>
                        <x-form.input withicon id="name_rol_update" class="block w-full" type="text" name="name_rol"
                            value="{{ old('name_rol', $role->name ?? '') }}" required autofocus
                            placeholder="{{ __('Nombre del Rol') }}" />

                    </x-form.input-with-icon-wrapper>
                </div>

                <div class="p-4 bg-gray-100 border rounded-lg shadow-md">
                    <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg">
                        {{ __('Permisos') }}
                    </div>
                    <div class="p-2 overflow-y-auto max-h-64">
                        <ul>
                            @foreach ($permissions as $permission)
                                <li class="flex items-center mb-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                        class="mr-2" />
                                    <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="flex justify-center">
                    <x-button class="flex justify-center w-full gap-2">
                        <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                        <span>{{ __('Editar') }}</span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>
    <!-- 1. Carga SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 2. Tu script personalizado -->
    <script>
        const form = document.getElementById('edit-role-form');

        if (form) {
            form.addEventListener('submit', function(e) {
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
    </script>


</x-app-layout>
