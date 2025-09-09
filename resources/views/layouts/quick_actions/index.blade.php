@extends('layouts.quick_actions.app')

@section('title', 'Acciones Rápidas - AulaSync')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Acciones Rápidas</h1>
                    <p class="text-gray-600 mt-1">Gestión centralizada de reservas y espacios</p>
                </div>
                <div class="text-sm text-gray-500">
                    {{ date('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Menu de Acciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Crear Reserva -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <i class="fa-solid fa-plus text-4xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Crear Reserva</h3>
                <p class="text-gray-600 text-sm text-center mb-4">
                    Crear nueva reserva de espacio
                </p>
                <a href="{{ route('quick-actions.crear-reserva') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fa-solid fa-plus w-4 h-4 mr-2"></i>
                    Nueva Reserva
                </a>
            </div>
        </div>

        <!-- Gestionar Reservas -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <i class="fa-solid fa-calendar text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Gestionar Reservas</h3>
                <p class="text-gray-600 text-sm text-center mb-4">
                    Editar y administrar reservas existentes
                </p>
                <a href="{{ route('quick-actions.gestionar-reservas') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-pencil w-4 h-4 mr-2"></i>
                    Gestionar
                </a>
            </div>
        </div>

        <!-- Gestionar Espacios -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <i class="fa-solid fa-building text-4xl text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Gestionar Espacios</h3>
                <p class="text-gray-600 text-sm text-center mb-4">
                    Administrar estados de espacios
                </p>
                <a href="{{ route('quick-actions.gestionar-espacios') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fa-solid fa-gear w-4 h-4 mr-2"></i>
                    Administrar
                </a>
            </div>
        </div>

    </div>

    <!-- Acciones Adicionales -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Adicionales</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>
                    Volver al Dashboard
                </a>
                
                <a href="{{ route('plano.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fa-solid fa-map w-4 h-4 mr-2"></i>
                    Plano Digital
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
