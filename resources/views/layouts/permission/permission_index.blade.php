<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Permisos</h2>
                    <p class="text-sm text-gray-500">Administra los permisos asignables a roles y usuarios</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-permission')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Permiso
            </x-button>
        </div>

        <livewire:permissions-table />

        <x-modal name="add-permission" :show="$errors->any()" focusable>
            @slot('title')
                <div class="relative bg-red-700 p-2 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-red-100 rounded-full p-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white">
                            Agregar Permiso
                        </h2>
                    </div>
                    <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                    <!-- Círculos decorativos -->
                    <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                    <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                </div>
            @endslot

            <form method="POST" action="{{ route('permission.add') }}" class="p-6">
                @csrf
                <div class="grid gap-4">
                    <div class="space-y-2">
                        <x-form.label for="name_permission" value="Nombre del Permiso *" />
                        <x-form.input id="name_permission" name="name" type="text"
                            class="w-full @error('name') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: mantenedor de usuarios" value="{{ old('name') }}" />
                        @error('name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Permiso') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.querySelector("#permissions-table");
            
            if (!table) {
                console.warn('Tabla de permisos no encontrada');
                return;
            }
            
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                if (cells.length >= 2) {
                    var id = cells[0].textContent.toLowerCase();
                    var name = cells[1].textContent.toLowerCase();

                    if (id.includes(input) || name.includes(input)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</x-app-layout>
