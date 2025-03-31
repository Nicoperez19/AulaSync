<div class="w-full min-h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Buscar por nombre..."
            class="w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
    </div>
    <div
        class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="hidden lg:table-header-group @class([
                'text-black border-b border-white',
                'bg-gray-50 dark:bg-black',
                'dark:text-white' => config('app.dark_mode'),
            ])">
                <tr>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $index => $permission)
                    <tr class="@class([
                        'text-black',
                        'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                        'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                        'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                        'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                    ])">
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="RUN">
                            {{ $permission->id }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                            {{ $permission->name }}
                        </td>


                        <td>
                            <x-button
                                x-on:click.prevent="$dispatch('open-modal', 'edit-permission-{{ $permission->id }}')"
                                variant="primary" class="gap-2">
                                <x-icons.edit class="w-6 h-6" aria-hidden="true" />
                            </x-button>

                            <form action="{{ route('permissions.delete', $permission->id) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" class="gap-2">
                                    <x-icons.delete class="w-6 h-6" aria-hidden="true" />
                                </x-button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $permissions->links() }}
    </div>
</div>