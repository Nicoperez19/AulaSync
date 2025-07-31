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
                <x-button href="{{ route('roles.index') }}" 
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
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                        class="mr-2" />
                                    <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">
                        <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                        <span>{{ __('Guardar Cambios') }}</span>
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
