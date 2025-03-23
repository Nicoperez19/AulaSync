<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Roles') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-role')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
        <div class="flex justify-center">
            <table class="min-w-full text-center table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-black-200">
                    @foreach ($roles as $role)
                        <tr>
                            <td class="p-3">{{ $role->id }}</td>
                            <td class="p-3">{{ $role->name }}</td>
                            <td>
                                <x-button href="{{ route('roles.edit', $role->id) }}" variant="primary" class="gap-2">
                                    <x-icons.edit class="w-6 h-6" aria-hidden="true" />
                                </x-button>

                                <form action="{{ route('roles.delete', $role->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" class="gap-2">
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
    
</x-app-layout>
