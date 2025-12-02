<style>
    .sort-icon {
        display: none;
        margin-left: 5px;
        transition: transform 0.2s;
    }

    .asc .sort-icon,
    .desc .sort-icon {
        display: inline-block;
    }

    .asc .sort-icon {
        transform: rotate(180deg);
    }

    .desc .sort-icon {
        transform: rotate(0deg);
    }

    th {
        cursor: pointer;
        user-select: none;
    }

    th:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

<div>
    <div class="mt-4 mb-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="user-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">RUN <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(1)">Nombre <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(2)">Correo <span class="sort-icon">▼</span></th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $user->run }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $user->name }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $user->email }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('users.edit', $user->run) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                @can('generar qr personal')
                                <button type="button" onclick="abrirModalQr('{{ $user->run }}', '{{ $user->name }}', {{ $user->tieneQrPersonal() ? 'true' : 'false' }})"
                                    class="inline-flex items-center px-3 py-2 text-white rounded {{ $user->tieneQrPersonal() ? 'bg-green-600 hover:bg-green-700' : 'bg-purple-600 hover:bg-purple-700' }}"
                                    title="{{ $user->tieneQrPersonal() ? 'Ver/Gestionar QR Personal' : 'Generar QR Personal' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </button>
                                @endcan

                                <form id="delete-form-{{ $user->run }}" action="{{ route('users.delete', $user->run) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button" onclick="deleteUser('{{ $user->run }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("user-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        // Remover clases de ordenamiento de todas las columnas
        Array.from(table.rows[0].cells).forEach(cell => {
            cell.classList.remove("asc", "desc");
        });

        rows.sort((rowA, rowB) => {
            var cellA = rowA.cells[columnIndex].textContent.trim();
            var cellB = rowB.cells[columnIndex].textContent.trim();

            if (columnIndex === 5 || columnIndex === 6) {
                cellA = new Date(cellA);
                cellB = new Date(cellB);
            }

            if (cellA < cellB) {
                return isAscending ? -1 : 1;
            }
            if (cellA > cellB) {
                return isAscending ? 1 : -1;
            }
            return 0;
        });

        rows.forEach(row => table.appendChild(row));

        table.rows[0].cells[columnIndex].classList.add(isAscending ? "desc" : "asc");
    }
    function deleteUser(run, name) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará al usuario "${name}" y no se puede deshacer`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + run).submit();
            }
        });
    }

    // =====================================================
    // FUNCIONES PARA QR PERSONAL
    // =====================================================
    
    let qrModalRun = null;
    let qrModalTieneQr = false;

    function abrirModalQr(run, nombre, tieneQr) {
        qrModalRun = run;
        qrModalTieneQr = tieneQr;

        if (tieneQr) {
            // Si ya tiene QR, mostrar modal con opciones de ver, descargar o anular
            Swal.fire({
                title: `QR Personal de ${nombre}`,
                html: `
                    <div class="text-center">
                        <p class="mb-4 text-gray-600">Este usuario ya tiene un QR personal generado.</p>
                        <div id="qr-preview-container" class="flex justify-center mb-4">
                            <div class="animate-pulse bg-gray-200 w-[200px] h-[200px] rounded"></div>
                        </div>
                        <p id="qr-fecha" class="text-sm text-gray-500 mb-4"></p>
                        <div class="flex justify-center gap-3 mt-4">
                            <button onclick="verQrEnModal('${run}', '${nombre}')" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                <i class="fas fa-eye mr-2"></i>Ver en grande
                            </button>
                        </div>
                    </div>
                `,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download mr-2"></i> Descargar QR',
                denyButtonText: '<i class="fas fa-trash mr-2"></i> Anular QR',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#10b981',
                denyButtonColor: '#ef4444',
                didOpen: () => {
                    cargarPreviewQr(run);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    descargarQr(run);
                } else if (result.isDenied) {
                    anularQr(run, nombre);
                }
            });
        } else {
            // Si no tiene QR, preguntar si desea generarlo
            Swal.fire({
                title: `Generar QR Personal`,
                html: `
                    <p class="text-gray-600">¿Deseas generar un QR personal para <strong>${nombre}</strong> (RUN: ${run})?</p>
                    <p class="mt-3 text-sm text-gray-500">El QR quedará asociado a este usuario y podrá ser utilizado para identificación.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-qrcode mr-2"></i> Generar QR',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#8b5cf6',
            }).then((result) => {
                if (result.isConfirmed) {
                    generarQr(run, nombre);
                }
            });
        }
    }

    async function cargarPreviewQr(run) {
        try {
            const response = await fetch(`/user/qr-personal/${run}/preview`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('qr-preview-container').innerHTML = `
                    <img src="data:image/png;base64,${data.qr_base64}" alt="QR Personal" class="rounded shadow-lg" style="max-width: 200px;">
                `;
                if (data.usuario.fecha_creacion) {
                    document.getElementById('qr-fecha').textContent = `Generado: ${data.usuario.fecha_creacion}`;
                }
            } else {
                document.getElementById('qr-preview-container').innerHTML = `
                    <p class="text-red-500">Error al cargar el QR</p>
                `;
            }
        } catch (error) {
            console.error('Error al cargar preview:', error);
            document.getElementById('qr-preview-container').innerHTML = `
                <p class="text-red-500">Error de conexión</p>
            `;
        }
    }

    async function generarQr(run, nombre) {
        try {
            Swal.fire({
                title: 'Generando QR...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`/user/qr-personal/${run}/generar`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: '¡QR Generado!',
                    html: `<p>El QR personal para <strong>${nombre}</strong> ha sido generado exitosamente.</p>`,
                    icon: 'success',
                    confirmButtonText: 'Descargar QR',
                    showCancelButton: true,
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#10b981',
                }).then((result) => {
                    if (result.isConfirmed) {
                        descargarQr(run);
                    }
                    // Recargar la página para actualizar el estado del botón
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo generar el QR',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error al generar QR:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión al generar el QR',
                icon: 'error'
            });
        }
    }

    async function descargarQr(run) {
        try {
            Swal.fire({
                title: 'Preparando descarga...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Usar fetch para obtener el archivo como blob
            const response = await fetch(`/user/qr-personal/${run}/descargar`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Error al obtener el archivo');
            }

            // Convertir respuesta a blob
            const blob = await response.blob();
            
            // Crear URL del blob y descargar
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `qr_personal_${run}.png`;
            document.body.appendChild(link);
            link.click();
            
            // Limpiar
            window.URL.revokeObjectURL(url);
            document.body.removeChild(link);

            Swal.fire({
                title: '¡Descarga completada!',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error al descargar QR:', error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo descargar el QR. Intenta de nuevo.',
                icon: 'error'
            });
        }
    }

    async function verQrEnModal(run, nombre) {
        try {
            Swal.fire({
                title: 'Cargando QR...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`/user/qr-personal/${run}/preview`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: `QR Personal de ${nombre}`,
                    html: `
                        <div class="text-center">
                            <div class="flex justify-center mb-4">
                                <img src="data:image/png;base64,${data.qr_base64}" alt="QR Personal" class="rounded shadow-lg" style="max-width: 280px;">
                            </div>
                            <p class="text-sm text-gray-500 mb-2">RUN: ${data.usuario.run}</p>
                            <p class="text-sm text-gray-500">Generado: ${data.usuario.fecha_creacion || 'N/A'}</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-download mr-2"></i> Descargar',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#10b981',
                }).then((result) => {
                    if (result.isConfirmed) {
                        descargarQr(run);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo cargar el QR',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error al ver QR:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión al cargar el QR',
                icon: 'error'
            });
        }
    }

    async function anularQr(run, nombre) {
        const confirmResult = await Swal.fire({
            title: '¿Anular QR Personal?',
            html: `
                <p class="text-gray-600">Esta acción <strong>eliminará permanentemente</strong> el QR personal de <strong>${nombre}</strong>.</p>
                <p class="mt-2 text-sm text-red-500">El QR dejará de funcionar inmediatamente.</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular QR',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
        });

        if (!confirmResult.isConfirmed) return;

        try {
            Swal.fire({
                title: 'Anulando QR...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`/user/qr-personal/${run}/anular`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: 'QR Anulado',
                    text: 'El QR personal ha sido anulado exitosamente.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Recargar la página para actualizar el estado del botón
                location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo anular el QR',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error al anular QR:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión al anular el QR',
                icon: 'error'
            });
        }
    }

</script>