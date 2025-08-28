<div>
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Reserva</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Hora</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Fecha Reserva</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Espacio</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Usuario</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservas as $index => $reserva)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Reserva">
                        {{ $reserva->id_reserva }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Hora">
                        {{ $reserva->hora }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Fecha Reserva">
                        {{ $reserva->fecha_reserva }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Espacio">
                        {{ $reserva->id_espacio }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Usuario">
                        {{ $reserva->nombre_usuario ?? 'Usuario no asignado' }}
                    </td>

                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <button type="button"
                                class="open-edit-modal px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700"
                                data-id="{{ $reserva->id_reserva }}"
                                data-hora="{{ $reserva->hora }}"
                                data-fecha="{{ $reserva->fecha_reserva }}"
                                data-espacio="{{ $reserva->id_espacio }}"
                                data-usuario="{{ $reserva->nombre_usuario }}">
                                Editar
                            </button>
                            <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}" class="reserva-delete-form">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700 btn-delete-reserva" data-espacio="{{ $reserva->id_espacio }}">
                                    Eliminar
                                </x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // Interceptar clicks en botones de eliminar reservas para notificar otras pestañas
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-reserva').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const espacioId = this.getAttribute('data-espacio');
                const form = this.closest('form');
                if (!form) return;

                // Guardar en localStorage para notificar otras pestañas
                localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));

                // Enviar el formulario para eliminar en esta pestaña
                form.submit();
            });
        });
    });
</script>

<!-- Edit Reserva Modal -->
<div id="editReservaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg">
        <div class="px-6 py-4 border-b dark:border-gray-700">
            <h3 class="text-lg font-semibold">Editar Reserva</h3>
        </div>
        <form id="editReservaForm" class="px-6 py-4" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id_reserva" id="edit_id_reserva">

            <div class="mb-4">
                <label class="block text-sm font-medium">Hora</label>
                <input type="text" name="hora" id="edit_hora" class="w-full mt-1 px-3 py-2 border rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Fecha Reserva</label>
                <input type="date" name="fecha_reserva" id="edit_fecha_reserva" class="w-full mt-1 px-3 py-2 border rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">ID Espacio</label>
                <input type="text" name="id_espacio" id="edit_id_espacio" class="w-full mt-1 px-3 py-2 border rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Usuario</label>
                <input type="text" name="nombre_usuario" id="edit_nombre_usuario" class="w-full mt-1 px-3 py-2 border rounded" />
            </div>

            <div class="flex justify-end space-x-2 pt-2 pb-4 px-6">
                <button type="button" id="cancelEditReserva" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('editReservaModal');
        const form = document.getElementById('editReservaForm');

        function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function closeModal() { modal.classList.remove('flex'); modal.classList.add('hidden'); }

        document.querySelectorAll('.open-edit-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const hora = this.getAttribute('data-hora');
                const fecha = this.getAttribute('data-fecha');
                const espacio = this.getAttribute('data-espacio');
                const usuario = this.getAttribute('data-usuario');

                document.getElementById('edit_id_reserva').value = id;
                document.getElementById('edit_hora').value = hora;
                document.getElementById('edit_fecha_reserva').value = fecha;
                document.getElementById('edit_id_espacio').value = espacio;
                document.getElementById('edit_nombre_usuario').value = usuario ?? '';

                openModal();
            });
        });

        document.getElementById('cancelEditReserva').addEventListener('click', function () { closeModal(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('edit_id_reserva').value;
            const url = `/reservas/${id}`;

            const payload = new FormData(form);

            // Fetch with PUT using POST + _method already present in form; send as form data
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: payload
            }).then(resp => {
                if (resp.ok) {
                    // close modal and reload to reflect changes (could be improved to update row inline)
                    closeModal();
                    location.reload();
                } else {
                    // Try to parse as JSON first, then fallback to text
                    resp.json().then(json => {
                        const message = json.message || json.error || 'Error desconocido';
                        alert('Error al actualizar reserva: ' + message);
                    }).catch(() => {
                        // If not JSON, parse as text
                        resp.text().then(t => alert('Error al actualizar reserva: ' + t));
                    });
                }
            }).catch(err => alert('Error de red: ' + err));
        });
    });
</script>
</div>
