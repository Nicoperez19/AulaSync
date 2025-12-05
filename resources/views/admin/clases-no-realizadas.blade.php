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

    <!-- Toggle para cambiar entre tablas -->
    <div x-data="{ activeTab: 'no-realizadas' }" class="space-y-6">
        <!-- Tabs de navegación -->
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit">
            <button 
                @click="activeTab = 'no-realizadas'"
                :class="activeTab === 'no-realizadas' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-2 rounded-md font-medium transition-all duration-200 flex items-center gap-2"
            >
                <i class="fas fa-times-circle" :class="activeTab === 'no-realizadas' ? 'text-red-500' : ''"></i>
                Clases No Realizadas
            </button>
            <button 
                @click="activeTab = 'atrasos'"
                :class="activeTab === 'atrasos' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-2 rounded-md font-medium transition-all duration-200 flex items-center gap-2"
            >
                <i class="fas fa-clock" :class="activeTab === 'atrasos' ? 'text-orange-500' : ''"></i>
                Atrasos de Profesores
                @if(($totalAtrasos ?? 0) > 0)
                    <span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $totalAtrasos }}</span>
                @endif
            </button>
        </div>

        <!-- KPIs de Atrasos (solo visible en tab atrasos) -->
        <div x-show="activeTab === 'atrasos'" x-transition class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Atrasos</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $totalAtrasos ?? 0 }}</p>
                        <p class="text-xs text-gray-500">En el período actual</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Promedio de Atraso</p>
                        <p class="text-2xl font-bold text-purple-600">{{ round($promedioMinutosAtraso ?? 0) }} min</p>
                        <p class="text-xs text-gray-500">Por llegada tardía</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-hourglass-half text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de las tablas -->
        <div x-show="activeTab === 'no-realizadas'" x-transition>
            @livewire('clases-no-realizadas-table')
        </div>

        <div x-show="activeTab === 'atrasos'" x-transition>
            @livewire('profesor-atrasos-table')
        </div>
    </div>
</x-app-layout>
