<div>
    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                           class="w-full py-2 pl-10 pr-10 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
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
                            class="w-full py-2 px-3 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todos los Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3 text-sm text-gray-500 dark:text-gray-400 shrink-0">
                <div wire:loading class="text-[#D12420]">
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
    </div>

    <!-- Card Tabla de Usuarios Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700" wire:loading.class="opacity-60">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('run')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                RUN
                                @if($sortField === 'run')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('name')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Nombre
                                @if($sortField === 'name')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('email')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Correo
                                @if($sortField === 'email')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-44 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Rol
                        </th>
                        <th scope="col" class="w-48 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($users as $user)
                        <tr wire:key="user-row-{{ $user->run }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $user->run }}</span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">{{ $user->name }}</span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</span>
                            </td>
                            <td class="w-44 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="px-2.5 py-0.5 text-xs font-semibold text-slate-800 bg-slate-100 border border-slate-300 rounded-full dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Sin rol</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="w-48 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('users.edit', $user->run) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                       title="Editar usuario">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </a>

                                    <form id="delete-form-{{ $user->run }}" action="{{ route('users.delete', $user->run) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="deleteUser('{{ $user->run }}', '{{ addslashes($user->name) }}')"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                                title="Eliminar usuario">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-users-row">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-users-slash text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron usuarios</p>
                                    @if(!empty($search) || !empty($roleFilter))
                                        <p class="text-xs text-gray-400 mt-1">No hay resultados para "{{ $search ?: $roleFilter }}"</p>
                                        <button wire:click="clearFilters" class="mt-3 px-3 py-1.5 text-xs font-semibold text-[#950101] bg-[#EDB9B9]/30 hover:bg-[#EDB9B9]/50 rounded-lg transition dark:bg-red-950 dark:text-red-300">
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

        {{-- Footer de paginación integrado --}}
        <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
            {{ $users->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

<script>
    function deleteUser(run, name) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará al usuario "${name}" y no se puede deshacer`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + run).submit();
            }
        });
    }
</script>
