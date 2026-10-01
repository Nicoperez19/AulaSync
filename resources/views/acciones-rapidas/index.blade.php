<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-bolt"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Acciones Rápidas</h2>
                    <p class="text-sm text-gray-500">Gestión centralizada de reservas y espacios del sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver vista principal</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

    <!-- Estadísticas Rápidas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-plus text-blue-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Reservas Hoy</p>
                        <p class="text-base sm:text-lg font-semibold text-blue-600" id="reservas-hoy">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-door-open text-green-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Espacios Libres</p>
                        <p class="text-base sm:text-lg font-semibold text-green-600" id="espacios-libres">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-door-closed text-red-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Espacios Ocupados</p>
                        <p class="text-base sm:text-lg font-semibold text-red-600" id="espacios-ocupados">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-tools text-yellow-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-xs sm:text-sm font-medium text-gray-900">En Mantención</p>
                        <p class="text-base sm:text-lg font-semibold text-yellow-600" id="espacios-mantencion">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu de Acciones Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Crear Reserva -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="p-6 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-plus text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Crear Reserva</h3>
                <p class="text-green-100 text-sm text-center mb-4">
                    Generar nueva reserva de espacio
                </p>
                <a href="{{ route('quick-actions.crear-reserva') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white text-green-600 rounded-lg hover:bg-green-50 transition-colors font-medium">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Crear Nueva
                </a>
            </div>
        </div>

        <!-- Gestionar Reservas -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="p-6 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Gestionar Reservas</h3>
                <p class="text-blue-100 text-sm text-center mb-4">
                    Editar y administrar reservas existentes
                </p>
                <a href="{{ route('quick-actions.gestionar-reservas') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Ver Lista
                </a>
            </div>
        </div>

        <!-- Gestionar Espacios -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="p-6 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Gestionar Espacios</h3>
                <p class="text-purple-100 text-sm text-center mb-4">
                    Cambiar estado y disponibilidad
                </p>
                <a href="{{ route('quick-actions.gestionar-espacios') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium">
                    <i class="fas fa-cog text-purple-600 mr-2"></i>
                    Administrar
                </a>
            </div>
        </div>

        <!-- Gestionar Reservas de Salas de Estudio -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="p-6 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-book-reader text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Salas de Estudio</h3>
                <p class="text-amber-100 text-sm text-center mb-4">
                    Gestión de reservas y accesos
                </p>
                <a href="{{ route('quick-actions.gestionar-salas-estudio') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white text-amber-600 rounded-lg hover:bg-amber-50 transition-colors font-medium">
                    <i class="fas fa-book-open text-amber-600 mr-2"></i>
                    Gestionar
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar estadísticas
    cargarEstadisticasRapidas();
    
    // Actualizar cada 30 segundos
    setInterval(cargarEstadisticasRapidas, 30000);
});

function cargarEstadisticasRapidas() {
    fetch('{{ route("quick-actions.dashboard-data") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('reservas-hoy').textContent = data.reservas_hoy || '0';
            document.getElementById('espacios-libres').textContent = data.espacios_libres || '0';
            document.getElementById('espacios-ocupados').textContent = data.espacios_ocupados || '0';
            document.getElementById('espacios-mantencion').textContent = data.espacios_mantencion || '0';
        })
        .catch(error => {
            console.error('Error al cargar estadísticas:', error);
        });
}
</script>
@endpush
</x-app-layout>
