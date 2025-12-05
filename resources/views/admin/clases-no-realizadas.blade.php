<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chart-bar"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Clases no realizadas y Atrasos</h2>
                    <p class="text-sm text-gray-500">Administra los registros de clases no realizadas y atrasos de profesores - Período: {{ $periodo ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- KPIs de Atrasos -->
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Atrasos</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $totalAtrasos ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Período actual</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Atrasos Sin Justificar</p>
                    <p class="text-2xl font-bold text-red-600">{{ $atrasosNoJustificados ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Pendientes</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Atrasos Justificados</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $atrasosJustificados ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Con justificación</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Promedio de Atraso</p>
                    <p class="text-2xl font-bold text-purple-600">{{ round($promedioMinutosAtraso ?? 0) }} min</p>
                    <p class="text-xs text-gray-500">Por evento</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-hourglass-half text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    @livewire('clases-no-realizadas-table')
</x-app-layout>
