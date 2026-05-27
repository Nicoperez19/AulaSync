<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Ausencias y Recuperación de Clases</h2>
                    <p class="text-sm text-gray-500">Gestiona las ausencias de profesores y el reagendamiento de clases pendientes</p>
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

        {{-- Determinar tab inicial basado en permisos --}}
        @php
            $canAusencias = auth()->user()->can('gestionar licencias profesores');
            $canRecuperacion = auth()->user()->can('gestionar recuperacion clases');
            $defaultTab = $canAusencias ? 'ausencias' : 'recuperacion';
        @endphp

        <div x-data="{ activeTab: '{{ request()->get('tab', $defaultTab) }}' }" class="space-y-6">
            {{-- Tabs de navegación --}}
            <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit">
                @can('gestionar licencias profesores')
                <button 
                    @click="activeTab = 'ausencias'"
                    :class="activeTab === 'ausencias' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    class="px-5 py-2.5 rounded-md font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <i class="fa-solid fa-calendar-xmark" :class="activeTab === 'ausencias' ? 'text-red-500' : ''"></i>
                    Ausencias de Profesores
                </button>
                @endcan

                @can('gestionar recuperacion clases')
                <button 
                    @click="activeTab = 'recuperacion'"
                    :class="activeTab === 'recuperacion' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    class="px-5 py-2.5 rounded-md font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <i class="fa-solid fa-calendar-check" :class="activeTab === 'recuperacion' ? 'text-emerald-500' : ''"></i>
                    Recuperación de Clases
                </button>
                @endcan
            </div>

            {{-- Tab: Ausencias de Profesores --}}
            @can('gestionar licencias profesores')
            <div x-show="activeTab === 'ausencias'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <livewire:licencias-profesores-table />
                </div>
            </div>
            @endcan

            {{-- Tab: Recuperación de Clases --}}
            @can('gestionar recuperacion clases')
            <div x-show="activeTab === 'recuperacion'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <livewire:recuperacion-clases-table />
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
