<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-edit"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Editar Reserva #{{ $reserva->id_reserva }}</h2>
                    <p class="text-sm text-gray-500">Modificar detalles de la reserva activa</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('quick-actions.gestionar-reservas') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </x-slot>

    @push('styles')
    <style>
        #autocomplete-results {
            z-index: 1000;
            top: 100%;
            left: 0;
            right: 0;
            transition: opacity 0.2s ease-in-out;
        }
        #autocomplete-results .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }
        #autocomplete-results.hidden {
            opacity: 0;
            pointer-events: none;
        }
        #autocomplete-results:not(.hidden) {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/admin-panel.js') }}"></script>
    @endpush

    <div class="space-y-6">

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    <!-- Información del Responsable - Editable -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Responsable de la Reserva</h2>
            <p class="text-xs text-gray-400 mb-4">Puedes reasignar la reserva a otro docente buscando por nombre, correo o RUN.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Responsable actual:</span>
                    <span class="text-gray-900 ml-1">
                        {{ $reserva->profesor
                            ? ($reserva->profesor->nombres ?? '') . ' ' . ($reserva->profesor->apellidos ?? $reserva->profesor->name ?? '')
                            : ($reserva->solicitante ? $reserva->solicitante->nombre : 'No especificado') }}
                    </span>
                    <span class="ml-2 text-xs text-gray-400">({{ $reserva->run_profesor ?: $reserva->run_solicitante }})</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Tipo:</span>
                    <span class="text-gray-900 ml-1">{{ $reserva->profesor ? 'Profesor' : 'Solicitante externo' }}</span>
                </div>
            </div>

            {{-- Campo de reasignación --}}
            <div class="mt-4 pt-4 border-t border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Reasignar a otro docente
                    <span class="text-xs text-gray-400 font-normal ml-1">(opcional — dejar vacío para mantener el actual)</span>
                </label>
                <div class="relative">
                    <input id="nuevo-docente-buscar"
                           type="search"
                           autocomplete="off"
                           placeholder="Buscar por nombre, correo o RUN..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <input type="hidden" id="nuevo-docente-run" name="nuevo_run_profesor" />
                    <div id="nuevo-docente-sugerencias"
                         class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                    </div>
                </div>
                <div id="nuevo-docente-confirmado" class="hidden mt-2 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="nuevo-docente-confirmado-texto"></span>
                    <button type="button" onclick="limpiarNuevoDocente()" class="ml-auto text-emerald-600 hover:text-emerald-800 font-bold">✕</button>
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
                            id="id-espacio"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione espacio</option>
                            @foreach($espacios as $espacio)
                                <option value="{{ $espacio->id_espacio }}" 
                                    {{ $reserva->id_espacio == $espacio->id_espacio ? 'selected' : '' }}>
                                    {{ $espacio->id_espacio }} - {{ $espacio->nombre_espacio }}
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
                            value="{{ $reserva->fecha_reserva->format('Y-m-d') }}"
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
                            max="15"
                            value="{{ $reserva->modulos ?? 1 }}"
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
window.EditarReservaConfig = {
    reservaId: @json($reserva->id_reserva),
    gestionarReservasUrl: "{{ route('quick-actions.gestionar-reservas') }}"
};
</script>
@vite(['resources/js/views/acciones-rapidas/editar-reserva.js'])
@endpush
</x-app-layout>
