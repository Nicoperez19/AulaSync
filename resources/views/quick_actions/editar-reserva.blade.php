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
const RESERVA_ID = '{{ $reserva->id_reserva }}';

// ── Autocomplete reasignar docente ───────────────────────────────────────────
(function () {
    const input = document.getElementById('nuevo-docente-buscar');
    const hiddenRun = document.getElementById('nuevo-docente-run');
    const sugerenciasBox = document.getElementById('nuevo-docente-sugerencias');
    const confirmadoBox = document.getElementById('nuevo-docente-confirmado');
    const confirmadoTexto = document.getElementById('nuevo-docente-confirmado-texto');
    if (!input) return;
    let debounceTimer;

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        hiddenRun.value = '';
        confirmadoBox.classList.add('hidden');
        if (q.length < 2) {
            sugerenciasBox.innerHTML = ''; sugerenciasBox.classList.add('hidden'); return;
        }
        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/api/usuarios/autocomplete?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                sugerenciasBox.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    sugerenciasBox.innerHTML = '<div class="p-3 text-sm text-gray-400">No se encontraron docentes</div>';
                    sugerenciasBox.classList.remove('hidden'); return;
                }
                data.forEach(u => {
                    const div = document.createElement('div');
                    div.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 border-b last:border-b-0';
                    div.innerHTML = `<div class="flex items-center justify-between"><span class="text-sm font-semibold text-gray-800">${u.nombre}</span><span class="text-xs text-gray-400">${u.id}</span></div><span class="text-xs text-gray-500">${u.email}</span>`;
                    div.addEventListener('click', () => {
                        hiddenRun.value = u.id;
                        input.value = u.nombre + ' (' + u.email + ')';
                        confirmadoTexto.textContent = `Se reasignará a: ${u.nombre} — RUN: ${u.id}`;
                        confirmadoBox.classList.remove('hidden');
                        sugerenciasBox.classList.add('hidden'); sugerenciasBox.innerHTML = '';
                    });
                    sugerenciasBox.appendChild(div);
                });
                sugerenciasBox.classList.remove('hidden');
            } catch (e) { console.error('Autocomplete error:', e); }
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!sugerenciasBox.contains(e.target) && e.target !== input) sugerenciasBox.classList.add('hidden');
    });
})();

function limpiarNuevoDocente() {
    document.getElementById('nuevo-docente-run').value = '';
    document.getElementById('nuevo-docente-buscar').value = '';
    document.getElementById('nuevo-docente-confirmado').classList.add('hidden');
    document.getElementById('nuevo-docente-sugerencias').innerHTML = '';
    document.getElementById('nuevo-docente-sugerencias').classList.add('hidden');
}

// ── Guardar ──────────────────────────────────────────────────────────────────
async function procesarEditarReserva(event) {
    event.preventDefault();
    const idEspacio = document.getElementById('id-espacio').value;
    const fecha = document.getElementById('fecha').value;
    const hora = document.getElementById('hora').value;
    const modulos = document.getElementById('modulos').value;
    const observaciones = document.getElementById('observaciones').value;
    const nuevoRunProfesor = document.getElementById('nuevo-docente-run').value || null;

    if (!idEspacio || !fecha || !hora || !modulos) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Por favor complete todos los campos requeridos' });
        return;
    }

    const btnGuardar = document.getElementById('btn-guardar');
    btnGuardar.disabled = true;
    Swal.fire({ title: 'Guardando cambios...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const body = { id_espacio: idEspacio, fecha, hora: hora + ':00', modulos: parseInt(modulos), observaciones };
        if (nuevoRunProfesor) body.nuevo_run_profesor = nuevoRunProfesor;

        const response = await fetch(`/quick-actions/api/reserva/${RESERVA_ID}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(body)
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.mensaje || 'Reserva actualizada correctamente', showConfirmButton: false, timer: 1800 })
                .then(() => { window.location.href = '{{ route("quick-actions.gestionar-reservas") }}'; });
        } else {
            throw new Error(data.mensaje || 'Error al actualizar la reserva');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Ocurrió un error al guardar los cambios' });
        btnGuardar.disabled = false;
    }
}
</script>
@endpush
@endsection

