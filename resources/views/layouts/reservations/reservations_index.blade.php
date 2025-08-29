<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Reservas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-reserva')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
            <span>Nueva Reserva</span>
        </x-button>
    </div>

    @livewire('reservations-table')

    <x-modal name="add-reserva" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('reservas.store') }}">
            @csrf
            <!-- Ajuste de estilo para parecerse al modal del plano digital -->
            <div class="p-6 space-y-6 bg-white rounded-lg max-w-4xl mx-auto">
                <!-- Filtros Universiad y Facultad -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="universidad" :value="__('Universidad')" />
                        <select id="universidad" name="universidad" class="block w-full border-gray-300 rounded-md"
                            required>
                            <option value="">Seleccione universidad</option>
                            @foreach ($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="facultad" :value="__('Facultad')" />
                        <select id="facultad" name="facultad" class="block w-full border-gray-300 rounded-md" required>
                            <option value="">Seleccione facultad</option>
                        </select>
                    </div>
                </div>

                <!-- Espacios Disponibles (aparecerá dinámicamente) -->
                <div id="espacios-container" class="hidden">
                    <div class="mb-4 space-y-2">
                        <x-form.label :value="__('Espacio a reservar')" />
                        <div id="espacios-grid" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Espacios se cargarán aquí -->
                        </div>
                    </div>
                </div>

                <!-- Fecha y Módulo -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="fecha_reserva" :value="__('Fecha de Reserva')" />
                        <x-form.input id="fecha_reserva" name="fecha_reserva" type="date" class="block w-full"
                            required />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="hora_select" :value="__('Módulo / Hora de inicio')" />
                        <!-- Select visible con módulos disponibles; el valor enviado será el hidden 'hora' -->
                        <select id="hora_select" class="block w-full border-gray-300 rounded-md" aria-label="Módulo de inicio">
                            <option value="">Seleccione módulo</option>
                        </select>
                        <!-- Hidden con la hora real (HH:MM) que espera el backend -->
                        <input type="hidden" id="hora" name="hora" />

                        <!-- Módulos seleccionables (se mostrará al elegir un espacio) -->
                        <div id="modulos-container" class="space-y-2 hidden mt-2">
                            <x-form.label for="modulos" :value="__('Cantidad de Módulos')" />
                            <input id="input-modulos" name="modulos" type="number" min="1" value="1" class="w-24 px-3 py-2 border rounded" />
                            <p class="text-sm text-gray-500">Módulo actual: <span id="modulo-actual-display">-</span></p>
                        </div>
                    </div>
                </div>

                <!-- Usuario (autocompletar por correo) -->
                <div class="space-y-2">
                    <x-form.label for="usuario_buscar" :value="__('Correo del usuario')" />
                    <input id="usuario_buscar" name="usuario_buscar" type="search" autocomplete="off"
                        class="block w-full border-gray-300 rounded-md px-3 py-2" placeholder="Escribe correo del usuario..." />
                    <!-- Hidden con el id real que llegará al servidor -->
                    <input type="hidden" id="id_usuario" name="id_usuario" />
                    <div id="usuario_suggestions" class="mt-1 bg-white border rounded shadow-sm max-h-48 overflow-auto hidden"></div>
                </div>

                <!-- Botón de Submit -->
                <!-- Módulos seleccionables (se mostrará al elegir un espacio) -->
                <div id="modulos-container" class="space-y-2 hidden">
                    <x-form.label for="modulos" :value="__('Cantidad de Módulos')" />
                    <input id="input-modulos" name="modulos" type="number" min="1" value="1" class="w-24 px-3 py-2 border rounded" />
                    <p class="text-sm text-gray-500">Módulo actual: <span id="modulo-actual-display">-</span></p>
                </div>

                <!-- Hidden para hora_salida calculada -->
                <input type="hidden" id="hora_salida" name="hora_salida" />
                <div class="flex justify-end pt-4">
                    <x-button id="btn-submit-reserva" type="submit" class="gap-2">
                        <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                        {{ __('Agregar Reserva') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        // Notificar otras pestañas cuando se crea una nueva reserva desde este modal
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btn-submit-reserva');
            if (!btn) return;
            const form = btn.closest('form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                // Validación cliente para evitar errores de validación del servidor
                const fechaInput = form.querySelector('[name="fecha_reserva"]');
                const espacioChecked = form.querySelector('input[name="id_espacio"]:checked');
                const horaInput = form.querySelector('[name="hora"]'); // hidden que debe llenarse desde el select
                const idUsuarioInput = form.querySelector('[name="id_usuario"]');

                if (!fechaInput || !fechaInput.value) {
                    e.preventDefault();
                    alert('Debe seleccionar una fecha para la reserva.');
                    return;
                }

                if (!espacioChecked) {
                    e.preventDefault();
                    alert('Debe seleccionar un espacio para reservar.');
                    return;
                }

                if (!horaInput || !horaInput.value) {
                    e.preventDefault();
                    alert('Debe seleccionar el módulo (hora de inicio) antes de enviar.');
                    return;
                }

                if (!idUsuarioInput || !idUsuarioInput.value) {
                    e.preventDefault();
                    alert('Debe seleccionar un usuario válido desde las sugerencias (correo).');
                    return;
                }

                // Guardar info en localStorage como antes
                const idEspacio = espacioChecked ? espacioChecked.value : null;
                localStorage.setItem('reserva_creada', JSON.stringify({ id_espacio: idEspacio, ts: Date.now() }));
            });
        });
    </script>

    <!-- removed duplicate universidad change handler to avoid duplicated facultad options -->

    <script>
        // JS adicional: habilitar facultad y autocompletar usuarios
        document.addEventListener('DOMContentLoaded', function() {
            const universidadSelect = document.getElementById('universidad');
            const facultadSelect = document.getElementById('facultad');

            // Asegurar que el select de facultad esté habilitado (en caso de estilos/atributos previos)
            if (facultadSelect) {
                facultadSelect.disabled = false;
            }

            // Re-attach change handler para universidad (por si otro script lo sobrescribió)
            if (universidadSelect) {
                universidadSelect.addEventListener('change', function() {
                    facultadSelect.innerHTML = '<option value="">Seleccione facultad</option>';
                    const espaciosContainer = document.getElementById('espacios-container');
                    if (espaciosContainer) espaciosContainer.classList.add('hidden');

                    if (!this.value) return;

                    fetch(`/facultades/${this.value}`)
                        .then(res => res.json())
                        .then(facultades => {
                            facultades.forEach(f => {
                                const opt = document.createElement('option');
                                opt.value = f.id_facultad;
                                opt.textContent = f.nombre_facultad;
                                facultadSelect.appendChild(opt);
                            });
                            facultadSelect.disabled = false;
                        })
                        .catch(() => {
                            facultadSelect.disabled = false;
                        });
                });
            }

            // Cargar espacios disponibles cuando cambia la facultad
            if (facultadSelect) {
                facultadSelect.addEventListener('change', function() {
                    const espaciosContainer = document.getElementById('espacios-container');
                    const espaciosGrid = document.getElementById('espacios-grid');
                    if (espaciosGrid) espaciosGrid.innerHTML = '';
                    if (espaciosContainer) espaciosContainer.classList.add('hidden');

                    if (!this.value || !universidadSelect || !universidadSelect.value) return;

                    fetch(`/espacios-disponibles?universidad=${encodeURIComponent(universidadSelect.value)}&facultad=${encodeURIComponent(this.value)}`)
                        .then(res => res.json())
                        .then(espacios => {
                            if (!Array.isArray(espacios) || espacios.length === 0) {
                                if (espaciosGrid) {
                                    espaciosGrid.innerHTML = '<p class="text-gray-500">No hay espacios disponibles</p>';
                                }
                                if (espaciosContainer) espaciosContainer.classList.remove('hidden');
                                return;
                            }

                            espacios.forEach(espacio => {
                                const card = document.createElement('label');
                                card.className = 'block cursor-pointer';

                                const codigoHtml = `<div class="text-sm text-gray-700 font-mono">${espacio.id_espacio}</div>`;

                                card.innerHTML = `
                                    <input type="radio" name="id_espacio" value="${espacio.id_espacio}" class="hidden peer" required>
                                    <div class="p-4 transition-all duration-200 bg-white border-2 rounded-lg shadow-sm hover:border-blue-400 hover:shadow-md peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:transform peer-checked:-translate-y-1">
                                        <h4 class="font-semibold text-gray-800">${espacio.tipo_espacio || espacio.nombre_espacio}</h4>
                                        ${codigoHtml}
                                        <p class="text-sm text-gray-600">Capacidad: ${espacio.puestos_disponibles ?? espacio.capacidad ?? 'N/A'}</p>
                                        <p class="text-sm text-gray-600">Piso: ${espacio.piso_numero ?? espacio.piso ?? 'N/A'}</p>
                                    </div>
                                `;
                                espaciosGrid.appendChild(card);
                            });

                            if (espaciosContainer) espaciosContainer.classList.remove('hidden');
                        })
                        .catch(() => {
                            // Mostrar contenedor aunque haya error para que el usuario sepa que no hay datos
                            if (espaciosGrid) espaciosGrid.innerHTML = '<p class="text-gray-500">No se pudieron cargar los espacios</p>';
                            if (espaciosContainer) espaciosContainer.classList.remove('hidden');
                        });
                        
                    // Delegated listener: cuando se selecciona un espacio, pedir módulos disponibles
                    espaciosGrid.addEventListener('change', async function (e) {
                        const target = e.target;
                        if (!target || target.name !== 'id_espacio') return;
                        const idEspacio = target.value;

                        // Llamar al endpoint que retorna modulos disponibles
                        try {
                            const now = new Date();
                            const horaActual = now.toTimeString().split(' ')[0];
                            const diaActual = now.toLocaleDateString('es-CL', { weekday: 'long' });
                            const res = await fetch(`/api/espacio/${encodeURIComponent(idEspacio)}/modulos-disponibles?hora_actual=${encodeURIComponent(horaActual)}&dia_actual=${encodeURIComponent(diaActual)}`);
                            const data = await res.json();
                            // Mostrar max modulos y módulo actual
                            const modulosContainer = document.getElementById('modulos-container');
                            const inputModulos = document.getElementById('input-modulos');
                            const moduloActualDisplay = document.getElementById('modulo-actual-display');
                            const horaSelect = document.getElementById('hora_select');
                            const horaHidden = document.getElementById('hora');
                            const horaSalidaInput = document.getElementById('hora_salida');

                            // Construir modulosDetalle usando respuesta o fallbacks y poblar el select
                            function buildModulosDetalle(responseData) {
                                let detalle = (responseData && Array.isArray(responseData.modulos_detalle)) ? responseData.modulos_detalle.slice() : [];

                                function normalizeDia(d) {
                                    if (!d) return d;
                                    const l = d.toString().toLowerCase();
                                    return l.replace(/á/g,'a').replace(/é/g,'e').replace(/í/g,'i').replace(/ó/g,'o').replace(/ú/g,'u');
                                }
                                const diaNormalizado = normalizeDia(diaActual);

                                if ((!Array.isArray(detalle) || detalle.length === 0)) {
                                    // intentar usar horarios globales si están presentes
                                    const globalHorarios = (typeof window !== 'undefined' && window.horariosModulos) ? window.horariosModulos : (typeof horariosModulos !== 'undefined' ? horariosModulos : null);
                                    if (globalHorarios && globalHorarios[diaNormalizado]) {
                                        const horariosDia = globalHorarios[diaNormalizado];
                                        const moduloActualResp = (responseData && responseData.modulo_actual) ? responseData.modulo_actual : 1;
                                        const maxResp = (responseData && responseData.max_modulos) ? responseData.max_modulos : 15;
                                        detalle = [];
                                        for (let i = 0; i < maxResp; i++) {
                                            const m = moduloActualResp + i;
                                            const h = horariosDia[m] || horariosDia[String(m)];
                                            if (h) detalle.push({ modulo: m, inicio: h.inicio, fin: h.fin });
                                        }
                                    }
                                }

                                if ((!Array.isArray(detalle) || detalle.length === 0)) {
                                    // fallback completo 1..15
                                    const horariosComplete = {
                                        1: { inicio: '08:10:00', fin: '09:00:00' },
                                        2: { inicio: '09:10:00', fin: '10:00:00' },
                                        3: { inicio: '10:10:00', fin: '11:00:00' },
                                        4: { inicio: '11:10:00', fin: '12:00:00' },
                                        5: { inicio: '12:10:00', fin: '13:00:00' },
                                        6: { inicio: '13:10:00', fin: '14:00:00' },
                                        7: { inicio: '14:10:00', fin: '15:00:00' },
                                        8: { inicio: '15:10:00', fin: '16:00:00' },
                                        9: { inicio: '16:10:00', fin: '17:00:00' },
                                        10: { inicio: '17:10:00', fin: '18:00:00' },
                                        11: { inicio: '18:10:00', fin: '19:00:00' },
                                        12: { inicio: '19:10:00', fin: '20:00:00' },
                                        13: { inicio: '20:10:00', fin: '21:00:00' },
                                        14: { inicio: '21:10:00', fin: '22:00:00' },
                                        15: { inicio: '22:10:00', fin: '23:00:00' }
                                    };
                                    detalle = Object.keys(horariosComplete).map(k => ({ modulo: parseInt(k,10), inicio: horariosComplete[k].inicio, fin: horariosComplete[k].fin }));
                                }

                                return detalle;
                            }

                            const modulosDetalle = buildModulosDetalle(data || {});

                            // Poblar el select
                            if (horaSelect) {
                                horaSelect.innerHTML = '<option value="">Seleccione módulo</option>';
                                modulosDetalle.forEach((m, idx) => {
                                    const inicio = m.inicio ? m.inicio.slice(0,5) : '';
                                    const fin = m.fin ? m.fin.slice(0,5) : '';
                                    const text = `Módulo ${m.modulo} — ${inicio}${fin ? ' - ' + fin : ''}`;
                                    const opt = document.createElement('option');
                                    opt.value = idx; // índice
                                    opt.textContent = text;
                                    horaSelect.appendChild(opt);
                                });
                            }

                            function actualizarHorasSeleccionadas() {
                                const selectedIdx = horaSelect ? horaSelect.value : null;
                                const cantidad = inputModulos ? parseInt(inputModulos.value || '1', 10) : 1;

                                if (selectedIdx === null || selectedIdx === '' || !Array.isArray(modulosDetalle) || modulosDetalle.length === 0) {
                                    // fallback: hora actual
                                    if (horaHidden) horaHidden.value = horaActual.slice(0,5);
                                    if (horaSalidaInput) horaSalidaInput.value = horaActual.slice(0,5);
                                    return;
                                }

                                const startIdx = parseInt(selectedIdx, 10);
                                const inicio = modulosDetalle[startIdx] && modulosDetalle[startIdx].inicio ? modulosDetalle[startIdx].inicio : null;
                                const endIdx = Math.min(startIdx + cantidad - 1, modulosDetalle.length - 1);
                                const fin = modulosDetalle[endIdx] && modulosDetalle[endIdx].fin ? modulosDetalle[endIdx].fin : inicio;

                                if (horaHidden && inicio) horaHidden.value = inicio.slice(0,5);
                                if (horaSalidaInput && fin) horaSalidaInput.value = fin.slice(0,5);
                            }

                            // Inicializar
                            if (inputModulos) inputModulos.max = Math.max(1, modulosDetalle.length);
                            if (inputModulos) inputModulos.value = 1;
                            if (horaSelect) horaSelect.selectedIndex = 0;

                            // Eventos: cambio de módulo de inicio o de cantidad
                            if (horaSelect) {
                                horaSelect.addEventListener('change', function () {
                                    actualizarHorasSeleccionadas();
                                });
                            }
                            if (inputModulos) {
                                inputModulos.addEventListener('input', function () {
                                    // asegurar valor válido
                                    let v = parseInt(this.value || '1', 10);
                                    if (isNaN(v) || v < 1) v = 1;
                                    if (this.max) v = Math.min(v, parseInt(this.max,10));
                                    this.value = v;
                                    actualizarHorasSeleccionadas();
                                });
                            }

                            // Mostrar contenedor
                            if (modulosContainer) modulosContainer.classList.remove('hidden');
                        } catch (err) {
                            console.error('Error al obtener modulos disponibles', err);
                        }
                    });
                });
            }

            // Autocomplete simple para usuarios
            const inputUsuario = document.getElementById('usuario_buscar');
            const suggestionsBox = document.getElementById('usuario_suggestions');
            const hiddenUsuario = document.getElementById('id_usuario');

            let currentFocus = -1;

            function clearSuggestions() {
                if (!suggestionsBox) return;
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('hidden');
            }

            function selectSuggestion(item) {
                if (!inputUsuario || !hiddenUsuario) return;
                // mostrar el correo en el input visible
                inputUsuario.value = item.dataset.email || (item.textContent || '').trim();
                hiddenUsuario.value = item.dataset.id;
                clearSuggestions();
            }

            if (inputUsuario) {
                inputUsuario.addEventListener('input', function (e) {
                        const q = this.value.trim();
                        hiddenUsuario.value = '';
                        // permitir 1 carácter mínimo para facilitar pruebas; ajustar según prefieras
                        if (!q || q.length < 1) {
                            clearSuggestions();
                            return;
                        }

                        // Llamada a endpoint de autocomplete (email o nombre)
                        fetch(`{{ url('/api/usuarios/autocomplete') }}?q=${encodeURIComponent(q)}`)
                            .then(res => {
                                if (!res.ok) throw new Error('Network response was not ok');
                                return res.json();
                            })
                            .then(data => {
                                if (!Array.isArray(data)) {
                                    suggestionsBox.innerHTML = '<div class="p-2 text-sm text-gray-500">No se pudieron procesar los resultados</div>';
                                    suggestionsBox.classList.remove('hidden');
                                    return;
                                }

                                suggestionsBox.innerHTML = '';
                                currentFocus = -1;

                                if (data.length === 0) {
                                    suggestionsBox.innerHTML = '<div class="p-2 text-sm text-gray-500">No se encontraron usuarios</div>';
                                    suggestionsBox.classList.remove('hidden');
                                    return;
                                }

                                data.forEach(u => {
                                    const div = document.createElement('div');
                                    div.className = 'p-2 cursor-pointer hover:bg-gray-100 flex flex-col';
                                    const fuenteLabel = u.fuente ? `<small class="text-xs text-indigo-600">${u.fuente}</small>` : '';
                                    div.innerHTML = `<div class="flex items-center justify-between"><span class="text-sm font-medium">${u.email}</span>${fuenteLabel}</div><small class="text-xs text-gray-500">${u.nombre}</small>`;
                                    div.dataset.id = u.id;
                                    div.dataset.email = u.email;
                                    div.dataset.fuente = u.fuente || '';
                                    div.addEventListener('click', function () { selectSuggestion(this); });
                                    suggestionsBox.appendChild(div);
                                });
                                suggestionsBox.classList.remove('hidden');
                            })
                            .catch((err) => {
                                console.error('Error en autocomplete usuarios:', err);
                                suggestionsBox.innerHTML = '<div class="p-2 text-sm text-gray-500">Error al buscar usuarios</div>';
                                suggestionsBox.classList.remove('hidden');
                            });
                });

                // Soporte teclado
                inputUsuario.addEventListener('keydown', function(e) {
                    const items = suggestionsBox ? Array.from(suggestionsBox.children) : [];
                    if (!items.length) return;
                    if (e.key === 'ArrowDown') {
                        currentFocus++; if (currentFocus >= items.length) currentFocus = 0;
                        items.forEach(i => i.classList.remove('bg-blue-100'));
                        items[currentFocus].classList.add('bg-blue-100');
                        e.preventDefault();
                    } else if (e.key === 'ArrowUp') {
                        currentFocus--; if (currentFocus < 0) currentFocus = items.length -1;
                        items.forEach(i => i.classList.remove('bg-blue-100'));
                        items[currentFocus].classList.add('bg-blue-100');
                        e.preventDefault();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentFocus > -1) {
                            selectSuggestion(items[currentFocus]);
                        }
                    }
                });

                // Click fuera cierra (mejor comprobación usando contains)
                document.addEventListener('click', function(e) {
                    if (e.target === inputUsuario) return;
                    if (suggestionsBox && !suggestionsBox.contains(e.target)) {
                        clearSuggestions();
                    }
                });
            }
        });
    </script>
</x-app-layout>
