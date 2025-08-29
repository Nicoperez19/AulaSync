<style>
    .sort-icon {
        display: inline-block;
        margin-left: 5px;
        transition: transform 0.2s;
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
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="permissions-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">ID <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(1)">Nombre <span class="sort-icon">▼</span></th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permissions as $index => $permission)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $permission->id }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $permission->name }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-permission-{{ $permission->id }}')"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <x-button variant="danger" 
                                    class="px-4 py-2 delete-permission-btn" 
                                    data-permission-id="{{ $permission->id }}"
                                    data-permission-name="{{ $permission->name }}">
                                    <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron permisos</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $permissions->links('vendor.pagination.tailwind') }}
    </div>

    @foreach ($allPermissions as $permission)
        <x-modal name="edit-permission-{{ $permission->id }}" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Editar Permiso
                    </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            @endslot

            <form method="POST" action="{{ route('permissions.update', $permission->id) }}" class="p-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4">
                    <div class="space-y-2">
                        <x-form.label for="name_permission_{{ $permission->id }}" value="Nombre del Permiso *" />
                        <x-form.input id="name_permission_{{ $permission->id }}" name="name" type="text"
                            class="w-full @error('name') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: mantenedor de usuarios" value="{{ $permission->name }}" />
                        @error('name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    @endforeach
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("permissions-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        // Remover clases de ordenamiento de todas las columnas
        Array.from(table.rows[0].cells).forEach(cell => {
            cell.classList.remove("asc", "desc");
        });

        rows.sort((rowA, rowB) => {
            var cellA = rowA.cells[columnIndex].textContent.trim();
            var cellB = rowB.cells[columnIndex].textContent.trim();

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

    // Clase para manejar la tabla de permisos
    class PermissionsTableManager {
        constructor() {
            this.init();
        }

        init() {
            this.setupEventListeners();
            this.initializeTable();
        }

        setupEventListeners() {
            // Eventos de Livewire
            document.addEventListener('livewire:load', () => this.initializeTable());
            document.addEventListener('livewire:update', () => this.initializeTable());
            document.addEventListener('livewire:navigated', () => this.initializeTable());
            
            // Evento cuando se carga el DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeTable());
            } else {
                this.initializeTable();
            }
        }

        initializeTable() {
            console.log('Inicializando tabla de permisos...');
            this.setupDeleteButtons();
            console.log('Tabla de permisos inicializada correctamente');
        }

        setupDeleteButtons() {
            const buttons = document.querySelectorAll('.delete-permission-btn');
            buttons.forEach(btn => {
                // Remover event listeners anteriores si existen
                if (btn.deleteHandler) {
                    btn.removeEventListener('click', btn.deleteHandler);
                }
                
                const permissionId = btn.dataset.permissionId;
                const permissionName = btn.dataset.permissionName;
                
                const deleteHandler = (e) => {
                    e.preventDefault();
                    this.handleDelete(permissionId, permissionName);
                };

                btn.deleteHandler = deleteHandler;
                btn.addEventListener('click', deleteHandler);
            });

            console.log(`Botones de eliminación configurados: ${buttons.length}`);
        }

        async handleDelete(permissionId, permissionName) {
            console.log(`Intentando eliminar permiso: ${permissionId} - ${permissionName}`);
            
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Estás seguro de que quieres eliminar el permiso "${permissionName}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                });

                if (result.isConfirmed) {
                    this.performDelete(permissionId);
                }
            } else {
                // Fallback a confirm nativo si SweetAlert2 no está disponible
                if (confirm(`¿Estás seguro de que quieres eliminar el permiso "${permissionName}"?`)) {
                    this.performDelete(permissionId);
                }
            }
        }

        async performDelete(permissionId) {
            try {
                // Crear un formulario temporal
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/permission/permission_delete/${permissionId}`;
                form.style.display = 'none';

                // Agregar CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                // Agregar método DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                // Agregar al DOM y enviar
                document.body.appendChild(form);
                form.submit();
                
            } catch (error) {
                console.error('Error al eliminar permiso:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Error al eliminar el permiso. Intenta recargar la página.', 'error');
                } else {
                    alert('Error al eliminar el permiso. Intenta recargar la página.');
                }
            }
        }
    }

    // Inicializar cuando el script se carga
    const permissionsTable = new PermissionsTableManager();
</script>