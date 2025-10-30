@extends('layouts.quick_actions.app')

@section('title', 'Acciones Rápidas - AulaSync')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6 text-gray-900">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Acciones Rápidas</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-2">Gestión centralizada de reservas y espacios del sistema</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:space-x-4 sm:gap-0">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        {{ date('d/m/Y H:i') }}
                    </div>
                    <div class="flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                        <i class="fas fa-circle mr-2" style="font-size: 8px;"></i>
                        Sistema Activo
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
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
                    <i class="fas fa-edit mr-2"></i>
                    Administrar
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
                    Administrar estados de espacios
                </p>
                <a href="{{ route('quick-actions.gestionar-espacios') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium">
                    <i class="fas fa-cogs mr-2"></i>
                    Configurar
                </a>
            </div>
        </div>

    </div>

    <!-- Accesos Rápidos a Otras Secciones -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-external-link-alt mr-2 text-gray-600"></i>
                Accesos Rápidos
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-tachometer-alt text-gray-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Dashboard Principal</span>
                </a>
                
                @can('monitoreo de espacios')
                <a href="{{ route('plano.index') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-map text-gray-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Plano Digital</span>
                </a>
                @endcan
                
                @can('reportes')
                <a href="{{ route('reportes.accesos') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chart-bar text-gray-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Reportes</span>
                </a>
                @endcan
                
                @can('mantenedor de usuarios')
                <a href="{{ route('users.index') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-users text-gray-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Usuarios</span>
                </a>
                @endcan
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
@endsection
