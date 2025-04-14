<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Roles') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-role')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('roles-table')

    {{-- Modal para agregar rol --}}
    <x-modal name="add-role" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('roles.add') }}">
            @csrf
            <div class="p-6 space-y-6">
                <!-- Nombre del Rol -->
                <div class="space-y-2">
                    <x-form.label for="name" :value="__('Nombre del Rol')" class="text-left" />
                    <x-form.input id="name" class="block w-full" type="text" name="name" required autofocus placeholder="{{ __('Nombre del rol') }}" />
                </div>
    
                <div class="p-4 bg-gray-100 border rounded-lg shadow-md">
                    <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg">
                        {{ __('Permisos') }}
                    </div>
                    <div class="p-2 overflow-y-auto max-h-64">
                        <ul>
                            @foreach ($permissions as $permission)
                                <li class="flex items-center mb-2">
                                    <!-- Sin marcar por defecto -->
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="mr-2" />
                                    <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
    
                <div class="flex justify-end">
                    <x-button class="justify-center w-full gap-2">
                        <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                        {{ __('Agregar Rol') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>
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
