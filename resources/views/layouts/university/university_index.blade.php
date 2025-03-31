<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Universidad /Universidad') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button target="_blank" variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-university')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('universitys-table')

    {{-- MODAL AGREGAR UNIVERSIDAD --}}
    <div class="space-y-1">
        <x-modal name="add-university" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('universitys.add') }}">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_universidad" :value="__('ID Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="id_universidad" class="block w-1/2" type="text"
                                name="id_universidad" :value="old('id_universidad')" required autofocus
                                placeholder="{{ __('ID Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_universidad" :value="__('Nombre Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="nombre_universidad" class="block w-1/2" type="text"
                                name="nombre_universidad" :value="old('nombre_universidad')" required
                                placeholder="{{ __('Nombre Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="direccion_universidad" :value="__('Dirección Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-home aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="direccion_universidad" class="block w-1/2" type="text"
                                name="direccion_universidad" :value="old('direccion_universidad')" required
                                placeholder="{{ __('Dirección Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="telefono_universidad" :value="__('Teléfono Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-phone aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="telefono_universidad" class="block w-1/2" type="text"
                                name="telefono_universidad" :value="old('telefono_universidad')"
                                placeholder="{{ __('Teléfono Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="comunas_id" :value="__('Comuna')" class="text-left" />
                        <x-form.select name="comunas_id" :options="$comunas->pluck('nombre_comuna', 'id')" />
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="imagen_logo" :value="__('Imagen Logo')" class="text-left" />
                        <x-form.input id="imagen_logo" class="block w-1/2" type="file" name="imagen_logo"
                            :value="old('imagen_logo')" />
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Universidad') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
