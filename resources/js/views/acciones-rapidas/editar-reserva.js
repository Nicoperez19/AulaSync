const RESERVA_ID = (window.EditarReservaConfig?.reservaId ?? '');

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
                .then(() => { window.location.href = (window.EditarReservaConfig?.gestionarReservasUrl ?? '/quick-actions/gestionar-reservas'); });
        } else {
            throw new Error(data.mensaje || 'Error al actualizar la reserva');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Ocurrió un error al guardar los cambios' });
        btnGuardar.disabled = false;
    }
}

if (typeof limpiarNuevoDocente === 'function') window.limpiarNuevoDocente = limpiarNuevoDocente;
