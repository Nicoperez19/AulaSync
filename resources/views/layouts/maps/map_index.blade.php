<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Gestión de Mapas') }}
            </h2>
        </div>
    </x-slot>
    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="window.location.href='{{ route('mapas.add') }}'" variant="primary"
            class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>

    </div>

    @livewire('mapas-table')



</x-app-layout>
