@extends('layouts.quick_actions.app')

@section('title', 'Crear Reserva - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Crear Nueva Reserva</h1>
                    <p class="text-gray-600 mt-1">Registrar reserva para profesor o solicitante externo</p>
                </div>
                <a href="{{ route('quick-actions.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <form id="form-crear-reserva" onsubmit="procesarCrearReserva(event)">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Información del Responsable -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Información del Responsable</h2>
                    
                    <!-- Búsqueda por RUN -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Buscar por RUN
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="run-busqueda"
                                placeholder="Ingrese RUN (sin puntos ni guión)"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                            <button 
                                type="button"
                                onclick="buscarPorRun()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                <i class="fa-solid fa-search w-4 h-4"></i>
                            </button>
                        </div>
                        <div id="resultado-busqueda" class="mt-2 text-sm"></div>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm text-gray-600 mb-4">Complete la información del responsable:</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                                <input 
                                    type="text" 
                                    id="nombre-responsable"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RUN *</label>
                                <input 
                                    type="text" 
                                    id="run-responsable"
                                    required
                                    placeholder="Sin puntos ni guión"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                <input 
                                    type="email" 
                                    id="correo-responsable"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input 
                                    type="tel" 
                                    id="telefono-responsable"
                                    placeholder="9 dígitos"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                                <select 
                                    id="tipo-responsable"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione tipo</option>
                                    <option value="profesor">Profesor</option>
                                    <option value="solicitante">Solicitante externo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de la Reserva -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Detalles de la Reserva</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                            <select 
                                id="espacio-reserva"
                                required
                                onchange="actualizarModulosDisponibles()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Cargando espacios...</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                            <input 
                                type="date" 
                                id="fecha-reserva"
                                required
                                min="{{ date('Y-m-d') }}"
                                value="{{ date('Y-m-d') }}"
                                onchange="cargarModulosParaSeleccion()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                                <select 
                                    id="modulo-inicial"
                                    required
                                    onchange="actualizarModulosFinales()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione módulo inicial</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                                <select 
                                    id="modulo-final"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione módulo final</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea 
                                id="observaciones-reserva"
                                rows="3"
                                placeholder="Observaciones adicionales..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('quick-actions.index') }}"
                       class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-plus w-4 h-4 mr-2 inline"></i>
                        Crear Reserva
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Función específica para el mantenedor
async function procesarCrearReserva(event) {
    event.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombre-responsable').value.trim(),
        run: document.getElementById('run-responsable').value.trim(),
        correo: document.getElementById('correo-responsable').value.trim(),
        telefono: document.getElementById('telefono-responsable').value.trim(),
        tipo: document.getElementById('tipo-responsable').value,
        espacio: document.getElementById('espacio-reserva').value,
        fecha: document.getElementById('fecha-reserva').value,
        modulo_inicial: parseInt(document.getElementById('modulo-inicial').value),
        modulo_final: parseInt(document.getElementById('modulo-final').value),
        observaciones: document.getElementById('observaciones-reserva').value.trim()
    };

    // Validaciones
    if (!formData.nombre || !formData.run || !formData.correo || !formData.tipo) {
        Swal.fire('Error', 'Complete todos los campos obligatorios del responsable', 'error');
        return;
    }

    if (!formData.espacio || !formData.fecha || !formData.modulo_inicial || !formData.modulo_final) {
        Swal.fire('Error', 'Complete todos los campos obligatorios de la reserva', 'error');
        return;
    }

    if (formData.modulo_inicial > formData.modulo_final) {
        Swal.fire('Error', 'El módulo inicial no puede ser mayor al módulo final', 'error');
        return;
    }

    try {
        // Mostrar loading
        Swal.fire({
            title: 'Creando reserva...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch('/api/admin/crear-reserva', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Reserva creada correctamente',
                icon: 'success',
                confirmButtonText: 'Ir a Gestión de Reservas',
                showCancelButton: true,
                cancelButtonText: 'Crear otra reserva'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('quick-actions.gestionar-reservas') }}";
                } else {
                    // Limpiar formulario para crear otra reserva
                    document.getElementById('form-crear-reserva').reset();
                    document.getElementById('resultado-busqueda').innerHTML = '';
                    document.getElementById('fecha-reserva').value = new Date().toISOString().split('T')[0];
                    cargarEspaciosDisponibles();
                    cargarModulosParaSeleccion();
                }
            });
        } else {
            Swal.fire('Error', result.mensaje || 'Error al crear la reserva', 'error');
        }
    } catch (error) {
        console.error('Error al crear reserva:', error);
        Swal.fire('Error', 'Error de conexión al crear la reserva', 'error');
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarEspaciosDisponibles();
    cargarModulosParaSeleccion();
});
</script>
@endpush
@endsection
