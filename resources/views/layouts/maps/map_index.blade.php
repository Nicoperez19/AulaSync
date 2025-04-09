<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Gestión de Mapas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-mapa')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
            {{ __('Agregar Mapa') }}
        </x-button>
    </div>

    @livewire('mapas-table')

    <!-- Modal para mostrar imagen del mapa -->
    <x-modal name="ver-mapa" focusable>
        <div class="p-6 text-center">
            <img id="imagen-mapa" src="" class="mx-auto rounded-lg shadow-lg" alt="Mapa" />
        </div>
    </x-modal>

    <!-- Modal para agregar un nuevo mapa -->
    <x-modal name="add-mapa" :show="$errors->any()" focusable>
        {{-- <form method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <x-form.label for="nombre_mapa" :value="__('Nombre del Mapa')" class="text-left" />
                    <x-form.input id="nombre_mapa" class="block w-full" type="text" name="nombre_mapa" required autofocus />
                </div>

                <div class="space-y-2">
                    <x-form.label for="ruta_mapa" :value="__('Imagen del Mapa (.png)')" class="text-left" />
                    <x-form.input id="ruta_mapa" class="block w-full" type="file" name="ruta_mapa" accept="image/*" required />
                </div>

                <div class="space-y-2">
                    <x-form.label for="id_espacio" :value="__('Espacio Asociado')" class="text-left" />
                    <select name="id_espacio" id="id_espacio" class="w-full rounded">
                        @foreach($espacios as $espacio)
                            <option value="{{ $espacio->id_espacio }}">{{ $espacio->nombre_espacio }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end">
                    <x-button class="justify-center w-full gap-2">
                        <x-heroicon-o-plus class="w-6 h-6" aria-hidden="true" />
                        {{ __('Guardar Mapa') }}
                    </x-button>
                </div>
            </div>
        </form> --}}
    </x-modal>

    <script>
        // Cuando se abre el modal de ver mapa, se puede setear la imagen por JS desde Livewire
        window.addEventListener('mostrar-mapa', event => {
            const imagen = document.getElementById('imagen-mapa');
            imagen.src = event.detail.ruta;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'ver-mapa' }));
        });
    </script>
</x-app-layout>
