<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad / Universidad') }}
            </h2>
        </div>  
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-university')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('universitys-table')

    <div class="space-y-1">
        <x-modal name="add-university" :show="$errors->any()" focusable>
            <form id="add-university-form" method="POST" action="{{ route('universities.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_universidad" :value="__('ID Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="id_universidad" class="block w-full" type="text" name="id_universidad"
                                :value="old('id_universidad')" required autofocus placeholder="{{ __('ID Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_universidad" :value="__('Nombre Universidad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="nombre_universidad" class="block w-full" type="text"
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
                            <x-form.input id="direccion_universidad" class="block w-full" type="text"
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
                            <x-form.input id="telefono_universidad" class="block w-full" type="text"
                                name="telefono_universidad" :value="old('telefono_universidad')"
                                placeholder="{{ __('Teléfono Universidad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="comunas_id" :value="__('Comuna')" class="text-left" />
                        <select name="comunas_id" id="comunas_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="" disabled selected>Seleccionar Comuna</option>
                            @foreach ($comunas as $comuna)
                                <option value="{{ $comuna->id }}"
                                    {{ old('comunas_id') == $comuna->id ? 'selected' : '' }}>
                                    {{ $comuna->nombre_comuna }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="imagen_logo" :value="__('Imagen Logo')" class="text-left" />
                        <x-form.input id="imagen_logo" class="block w-full" type="file" name="imagen_logo"
                            accept="image/*" />
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2" id="submit-btn">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Universidad') }}</span>
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
            text: @json(session('success')),
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    @endif

    @if (session('error'))
        Swal.fire({
            title: '¡Error!',
            text: @json(session('error')),
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    @endif
</script>
</x-app-layout>
