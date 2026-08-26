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
    <div class="flex flex-col gap-4 mb-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-3/4">
            <!-- Barra de búsqueda principal -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por Nombre, RUN, Correo, Rol..."
                       class="w-full py-2 pl-10 pr-10 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
                @if(!empty($search))
                    <button wire:click="$set('search', '')" title="Limpiar búsqueda" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Filtro por Rol -->
            <div class="sm:w-52 shrink-0">
                <select wire:model.live="roleFilter"
                        class="w-full py-2 px-3 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-3 text-sm text-gray-500 dark:text-gray-400 shrink-0">
            <div wire:loading class="text-blue-600">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                Total: <strong class="text-gray-800 dark:text-white">{{ $users->total() }}</strong> usuarios
            </span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700" wire:loading.class="opacity-60">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('run')">
                        RUN
                        @if($sortField === 'run')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('name')">
                        Nombre
                        @if($sortField === 'name')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('email')">
                        Correo
                        @if($sortField === 'email')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Rol</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $index => $user)
                    <tr wire:key="user-row-{{ $user->run }}" class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-gray-700 dark:text-blue-400">
                            {{ $user->run }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap text-gray-800 dark:text-gray-200">
                            {{ $user->name }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap text-gray-600 dark:text-gray-400">
                            {{ $user->email }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex flex-wrap justify-center gap-1">
                                @forelse($user->roles as $role)
                                    <span class="px-2.5 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Sin rol</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('users.edit', $user->run) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $user->run }}" action="{{ route('users.delete', $user->run) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button" onclick="deleteUser('{{ $user->run }}', '{{ addslashes($user->name) }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr wire:key="empty-users-row">
                        <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="font-medium text-gray-600 dark:text-gray-300">No se encontraron usuarios</p>
                                @if(!empty($search) || !empty($roleFilter))
                                    <p class="text-xs text-gray-400 mt-1">No hay resultados para "{{ $search ?: $roleFilter }}"</p>
                                    <button wire:click="clearFilters" class="mt-3 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition dark:bg-blue-900/30 dark:text-blue-300">
                                        Limpiar filtros
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
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
</script>
