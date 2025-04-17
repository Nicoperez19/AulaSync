<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <div class="p-4 flex items-center justify-between">
        <div class="flex items-center">
            <label for="search" class="mr-2 text-gray-700 dark:text-gray-300">Buscar:</label>
            <input wire:model.debounce.300ms="search" type="text" id="search"
                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-auto border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                   placeholder="Buscar por RUN o Nombre">
        </div>
    </div>
    <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('run')">
                    RUN
                    @if ($sortField === 'run')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('name')">
                    Nombre
                    @if ($sortField === 'name')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('email')">
                    Correo
                    @if ($sortField === 'email')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('celular')">
                    Celular
                    @if ($sortField === 'celular')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('direccion')">
                    Dirección
                    @if ($sortField === 'direccion')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('fecha_nacimiento')">
                    Fecha Nacimiento
                    @if ($sortField === 'fecha_nacimiento')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap cursor-pointer" wire:click="sortBy('anio_ingreso')">
                    Año Ingreso
                    @if ($sortField === 'anio_ingreso')
                        @if ($sortDirection === 'asc')
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    @endif
                </th>
                @auth
                    @if (auth()->user()->hasRole('Administrador'))
                        <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                    @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $index => $user)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->run }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->name }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->email }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->celular }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->direccion }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        {{ $user->fecha_nacimiento }}</td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->anio_ingreso }}
                    </td>
                    @auth
                        @if (auth()->user()->hasRole('Administrador'))
                            <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <x-button href="{{ route('users.edit', $user->id) }}"
                                              class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                        Editar
                                    </x-button>
                                    <form id="delete-form-{{ $user->id }}"
                                          action="{{ route('users.delete', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $user->id }}')"
                                                class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    @endauth
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-3 text-gray-500 dark:text-gray-400">No se encontraron usuarios.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">
        {{ $users->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }

    // Mostrar mensajes de sesión
    @if (session('success'))
        Swal.fire({
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    @endif

    @if (session('error'))
        Swal.fire({
            title: '¡Error!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    @endif

    @if (session('user_not_found'))
        Swal.fire({
            title: '¡Error!',
            text: '{{ session('user_not_found') }}',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    @endif
</script>