<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-users"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Usuarios</h2>
                    <p class="text-sm text-gray-500">Administra los usuarios registrados en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por RUN o Nombre"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click="$dispatch('open-modal', 'add-user')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Usuario
            </x-button>
        </div>

        <livewire:users-table />

        <x-modal name="add-user" :show="$errors->any()" focusable>
            @slot('title')
                <div class="relative bg-red-700 p-2 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-red-100 rounded-full p-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white">
                            Agregar Usuario
                        </h2>
                    </div>
                    <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                    <!-- Círculos decorativos -->
                    <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                    <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                </div>
            @endslot

            <form id="add-user-form" method="POST" action="{{ route('users.add') }}" class="needs-validation p-6" novalidate>
                @csrf
                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="run_add" value="RUN *" />
                            <x-form.input id="run_add" name="run" type="text"
                                class="w-full @error('run') border-red-500 @enderror" required maxlength="8" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Ej: 12345678"
                                value="{{ old('run', '') }}" />
                            @error('run')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="name_add" value="Nombre *" />
                            <x-form.input id="name_add" name="name" type="text"
                                class="w-full @error('name') border-red-500 @enderror" required
                                placeholder="Ej: Juan Pérez" value="{{ old('name', '') }}" />
                            @error('name')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="email_add" value="Correo *" />
                            <x-form.input id="email_add" name="email" type="email"
                                class="w-full @error('email') border-red-500 @enderror" required
                                placeholder="Ej: juan.perez@email.com" value="{{ old('email', '') }}" />
                            @error('email')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="celular_add" value="Celular" />
                            <x-form.input id="celular_add" name="celular" type="text"
                                class="w-full @error('celular') border-red-500 @enderror" maxlength="9"
                                pattern="9[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="Ej: 912345678" value="{{ old('celular', '') }}" />
                            @error('celular')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="password_add" value="Contraseña *" />
                        <x-form.input id="password_add" name="password" type="password"
                            class="w-full @error('password') border-red-500 @enderror" required minlength="8"
                            placeholder="Mínimo 8 caracteres" />
                        @error('password')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Usuario') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("user-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var run = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var email = cells[2].textContent.toLowerCase();

                if (run.includes(input) || name.includes(input) || email.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function deleteUser(run) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
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
</x-app-layout>