<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chart-bar"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Estadísticas de Profesores</h2>
                    <p class="text-sm text-gray-500">Administra los registros de clases no realizadas por los profesores</p>
                </div>
            </div>
        </div>
    </x-slot>

    @livewire('clases-no-realizadas-table')
</x-app-layout>
