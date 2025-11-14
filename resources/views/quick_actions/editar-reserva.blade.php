@extends('layouts.quick_actions.app')

@section('title', 'Editar Reserva - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Editar Reserva #{{ $reserva->id_reserva }}</h1>
                    <p class="text-gray-600 mt-1">Modificar detalles de la reserva activa</p>
                </div>
                <a href="{{ route('quick-actions.gestionar-reservas') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    <!-- Información del Responsable (solo lectura) -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Responsable</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Nombre:</span>
                    <span class="text-gray-900">
                        {{ $reserva->profesor ? $reserva->profesor->nombres . ' ' . $reserva->profesor->apellidos : 
                           ($reserva->solicitante ? $reserva->solicitante->nombre : 'No especificado') }}
                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">RUN:</span>
                    <span class="text-gray-900">{{ $reserva->run_usuario }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Tipo:</span>
                    <span class="text-gray-900">{{ $reserva->profesor ? 'Profesor' : 'Solicitante externo' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de edición -->
    <form id="form-editar-reserva" onsubmit="procesarEditarReserva(event)">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Detalles de la Reserva</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                        <select 
                            id="codigo-espacio"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione espacio</option>
                            @foreach($espacios as $espacio)
                                <option value="{{ $espacio->codigo_espacio }}" 
                                    {{ $reserva->espacio->codigo_espacio == $espacio->codigo_espacio ? 'selected' : '' }}>
                                    {{ $espacio->codigo_espacio }} - {{ $espacio->nombre_espacio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                        <input 
                            type="date" 
                            id="fecha"
                            required
                            value="{{ $reserva->fecha }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hora *</label>
                        <input 
                            type="time" 
                            id="hora"
                            required
                            value="{{ substr($reserva->hora, 0, 5) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad de módulos *</label>
                        <input 
                            type="number" 
                            id="modulos"
                            required
                            min="1"
                            max="12"
                            value="{{ $reserva->cant_modulos }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <textarea 
                            id="observaciones"
                            rows="3"
                            placeholder="Observaciones adicionales..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $reserva->observaciones }}</textarea>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('quick-actions.gestionar-reservas') }}"
                       class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        id="btn-guardar"
                        class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-save w-4 h-4 mr-2"></i>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const RESERVA_ID = '{{ $reserva->id_reserva }}';

async function procesarEditarReserva(event) {
    event.preventDefault();
    
    // Validar formulario
    const codigoEspacio = document.getElementById('codigo-espacio').value;
    const fecha = document.getElementById('fecha').value;
    const hora = document.getElementById('hora').value;
    const modulos = document.getElementById('modulos').value;
    const observaciones = document.getElementById('observaciones').value;
    
    if (!codigoEspacio || !fecha || !hora || !modulos) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor complete todos los campos requeridos'
        });
        return;
    }

    // Deshabilitar botón
    const btnGuardar = document.getElementById('btn-guardar');
    btnGuardar.disabled = true;
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando cambios...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch(`/quick-actions/api/reserva/${RESERVA_ID}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                codigo_espacio: codigoEspacio,
                fecha: fecha,
                hora: hora + ':00',
                modulos: parseInt(modulos),
                observaciones: observaciones
            })
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.mensaje || 'Reserva actualizada correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '{{ route("quick-actions.gestionar-reservas") }}';
            });
        } else {
            throw new Error(data.mensaje || 'Error al actualizar la reserva');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al guardar los cambios'
        });
        btnGuardar.disabled = false;
    }
}
</script>
@endpush
@endsection
