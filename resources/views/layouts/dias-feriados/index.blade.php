<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-calendar-days"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Calendario Académico</h2>
                    <p class="text-sm text-gray-500">Gestiona los períodos académicos, feriados y días sin actividad universitaria</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg" role="alert">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden bg-white rounded-lg shadow-lg">
            <livewire:calendario-academico-table />
        </div>
    </div>
</x-app-layout>
