<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Roles</h2>
                    <p class="text-sm text-gray-500">Administra los roles y permisos del sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('roles.index') }}" variant="back">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form method="POST" id="edit-role-form" action="{{ route('roles.update', $role->id) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
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

                <div class="p-4 border rounded-lg shadow-lg">
                    <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg">
                        {{ __('Permisos') }}
                    </div>
                    <div class="p-2 overflow-y-auto max-h-64">
                        <ul>
                            @foreach ($permissions as $permission)
                                <li class="flex items-center mb-2">
                                    <input type="checkbox" 
                                        id="permission-{{ $permission->id }}"
                                        name="permissions[]" 
                                        value="{{ $permission->id }}"
                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                                    <label for="permission-{{ $permission->id }}" class="cursor-pointer select-none">{{ $permission->name }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-button href="{{ route('roles.index') }}" variant="cancel">
                        {{ __('Cancelar') }}
                    </x-button>
                    <x-button type="submit" variant="save">
                        <i class="mr-2 fa-solid fa-floppy-disk"></i>
                        {{ __('Guardar Cambios') }}
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-role-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: 'Estás a punto de guardar los cambios realizados.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#8D8D8D',
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
