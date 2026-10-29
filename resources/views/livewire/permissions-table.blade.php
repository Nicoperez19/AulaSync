<div>
    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="relative w-full md:w-1/2">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por Nombre de Permiso o ID..."
                       class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <span class="bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                    Total: <strong class="text-gray-800 dark:text-white">{{ $permissions->total() }}</strong> permisos
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table id="permissions-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('id')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>ID</span>
                                @if($sortField === 'id')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('name')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Nombre</span>
                                @if($sortField === 'name')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center w-48">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($permissions as $permission)
                        <tr wire:key="permission-row-{{ $permission->id }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-[#950101] dark:text-red-400 align-middle whitespace-nowrap">
                                {{ $permission->id }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-middle whitespace-nowrap">
                                {{ $permission->name }}
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button"
                                        x-on:click.prevent="$dispatch('open-modal', 'edit-permission-{{ $permission->id }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                        title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </button>

                                    <button type="button" 
                                        class="delete-permission-btn inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs" 
                                        data-permission-id="{{ $permission->id }}"
                                        data-permission-name="{{ $permission->name }}"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                        <span>Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-permissions-row">
                            <td colspan="3" class="p-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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

        @if($permissions->hasPages())
            <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>

    @foreach ($allPermissions as $permission)
        <x-modal name="edit-permission-{{ $permission->id }}" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between p-4 bg-[#D12420] rounded-t-lg">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-white/20 rounded-full text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">
                        Editar Permiso
                    </h2>
                </div>
                <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200">&times;</button>
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

                    <div class="flex justify-end gap-2 mt-6">
                        <x-button type="button" @click="show = false" variant="cancel">Cancelar</x-button>
                        <x-button variant="save">{{ __('Guardar Cambios') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    @endforeach
</div>

<script>
    class PermissionsTableManager {
        constructor() {
            this.init();
        }

        init() {
            this.setupEventListeners();
            this.initializeTable();
        }

        setupEventListeners() {
            document.addEventListener('livewire:load', () => this.initializeTable());
            document.addEventListener('livewire:update', () => this.initializeTable());
            document.addEventListener('livewire:navigated', () => this.initializeTable());
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeTable());
            } else {
                this.initializeTable();
            }
        }

        initializeTable() {
            this.setupDeleteButtons();
        }

        setupDeleteButtons() {
            const buttons = document.querySelectorAll('.delete-permission-btn');
            buttons.forEach(btn => {
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
        }

        async handleDelete(permissionId, permissionName) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Estás seguro de que quieres eliminar el permiso "${permissionName}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#D12420',
                    cancelButtonColor: '#8D8D8D',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                });

                if (result.isConfirmed) {
                    this.performDelete(permissionId);
                }
            } else {
                if (confirm(`¿Estás seguro de que quieres eliminar el permiso "${permissionName}"?`)) {
                    this.performDelete(permissionId);
                }
            }
        }

        async performDelete(permissionId) {
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/permission/permission_delete/${permissionId}`;
                form.style.display = 'none';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

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

    const permissionsTable = new PermissionsTableManager();
</script>
