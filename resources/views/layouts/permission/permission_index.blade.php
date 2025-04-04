<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios/ Permisos') }}
            </h2>
        </div>
    </x-slot>
    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-permission')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('permissions-table')

    <x-modal name="add-permission" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('permission.add') }}">
            @csrf
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <x-form.label for="name_permission" :value="__('Nombre del Permiso')" class="text-left" />
                    <x-form.input id="name_permission" class="block w-full" type="text" name="name" required
                        autofocus placeholder="{{ __('Nombre del permiso') }}" />
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
                        <x-form.label for="name_permission_{{ $permission->id }}" :value="__('Nombre del Permiso')" class="text-left" />
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
</x-app-layout>
