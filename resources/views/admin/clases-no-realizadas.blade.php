<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chart-bar"></i>
                </div>

                <div>
                    @if($isSuperAdmin ?? false)
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold leading-tight">CONTROL DE CLASES Y ATRASOS</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-300" title="La sección de Atrasos está oculta para los demás usuarios y solo visible para Superadministradores">
                                <i class="fas fa-shield-alt mr-1"></i> Modo Superadmin (Atrasos privado)
                            </span>
                        </div>
                    @else
                        <h2 class="text-2xl font-bold leading-tight">CONTROL DE CLASES</h2>
                    @endif
                    @php
                        $partesPeriodo = explode('-', $periodo ?? '');
                        $periodoFormateado = (count($partesPeriodo) === 2)
                            ? $partesPeriodo[1] . '° Semestre ' . $partesPeriodo[0]
                            : (\App\Helpers\SemesterHelper::getCurrentSemester() . '° Semestre ' . \App\Helpers\SemesterHelper::getCurrentAcademicYear());
                    @endphp
                    <div class="flex items-center gap-1.5 text-sm font-bold text-red-700 mt-1">
                        <i class="fa-solid fa-calendar-days text-red-600"></i>
                        <span>Período: {{ $periodoFormateado }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
 
    @if($isSuperAdmin ?? false)
        <!-- Vista con pestañas para Superadministrador -->
        <div x-data="{ activeTab: 'no-realizadas' }" class="space-y-6">
            <!-- Tabs de navegación (Navpills grandes) -->
            <div class="flex flex-wrap sm:inline-flex items-center gap-2 bg-gray-200/80 p-1.5 rounded-2xl border border-gray-300/70 shadow-sm">
                <button 
                    type="button"
                    @click="activeTab = 'no-realizadas'"
                    :class="activeTab === 'no-realizadas' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                    class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
                >
                    <i class="fas fa-calendar-check text-xl" :class="activeTab === 'no-realizadas' ? 'text-blue-600' : 'text-gray-400'"></i>
                    <span>Control de Clases</span>
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'atrasos'"
                    :class="activeTab === 'atrasos' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                    class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
                >
                    <i class="fas fa-clock text-xl" :class="activeTab === 'atrasos' ? 'text-orange-500' : 'text-gray-400'"></i>
                    <span>Atrasos de Profesores</span>
                    <span class="text-xs uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-purple-100 text-purple-700 border border-purple-200">Solo Superadmin</span>
                    @if(($totalAtrasos ?? 0) > 0)
                        <span class="bg-orange-500 text-white text-xs px-2.5 py-0.5 rounded-full font-bold shadow-sm">{{ $totalAtrasos }}</span>
                    @endif
                </button>
            </div>

            <!-- Contenido de las tablas -->
            <div x-show="activeTab === 'no-realizadas'" x-transition>
                @livewire('clases-no-realizadas-table')
            </div>

            <div x-show="activeTab === 'atrasos'" x-transition>
                @livewire('profesor-atrasos-table')
            </div>
        </div>
    @else
        <!-- Vista para usuarios regulares: únicamente Control de Clases -->
        <div>
            @livewire('clases-no-realizadas-table')
        </div>
    @endif
</x-app-layout>
