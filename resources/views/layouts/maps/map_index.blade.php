<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-map text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Gestión de Mapas</h2>
                    <p class="text-gray-500 text-sm">Visualiza, edita y administra la ubicación de los espacios en el
                        mapa institucional</p>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-lg">

        @if (auth()->user()->hasRole('Administrador'))
            <div class="flex justify-end mb-4">
                <x-button x-on:click.prevent="window.location.href='{{ route('mapas.add') }}'" variant="add"
                    class="max-w-xs gap-2">
                    <x-icons.add class="w-6 h-6" aria-hidden="true" />
                </x-button>
            </div>
        @endif
        <livewire:mapas-table />
    </div>
</x-app-layout>
