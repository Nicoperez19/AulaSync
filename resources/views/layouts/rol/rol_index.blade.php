<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Roles</h2>
                    <p class="text-sm text-gray-500">Administra los roles del sistema y sus permisos asociados</p>
                </div>
            </div>

        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-end mb-4">
            <x-button target="_blank" variant="add" class="max-w-xs gap-2"
                x-on:click.prevent="$dispatch('open-modal', 'add-role')" variant="add" class="max-w-xs gap-2">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>

        <livewire:roles-table />

        <x-modal name="add-role" :show="$errors->any()" focusable>

            @slot('title')
            <h1 class="text-lg font-medium text-white dark:text-gray-100">
                {{ __('Agregar Rol') }}
            </h1>
            @endslot
            <form method="POST" action="{{ route('roles.add') }}">
                @csrf
                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <x-form.label for="name" :value="__('Nombre del Rol')" class="text-left" />
                        <x-form.input id="name" class="block w-full" type="text" name="name" required autofocus
                            placeholder="{{ __('Nombre del rol') }}" />
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
                                            class="mr-2" />
                                        <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-button class="justify-center w-full gap-2" variant="add">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            {{ __('Agregar Rol') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

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
    </script>

</x-app-layout>