<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios / Usuarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mt-4">
            <!-- Buscador pequeño a la izquierda -->
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por RUN o Nombre"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button target="_blank" variant="add" class="max-w-xs gap-2"
                x-on:click="$dispatch('open-modal', 'add-user')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>

        </div>

        <livewire:users-table />

        <x-modal name="add-user" :show="$errors->any()" focusable>
            @slot('title')
            <h1 class="text-lg font-medium text-white dark:text-gray-100">
                Agregar Usuario </h1>
            @endslot

            <form id="add-user-form" method="POST" action="{{ route('users.add') }}" class="needs-validation"
                novalidate>
                @csrf
                <div class="grid gap-6 p-6">
                    <!-- Campo RUN -->
                    <div class="space-y-2">
                        <x-form.label for="run_add" :value="__('RUN')" class="text-left" />
                        <x-form.input id="run_add" class="block w-full" type="text" name="run"
                            value="{{ old('run', '') }}" placeholder="RUN" maxlength="8" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                        <div id="run-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Campo Nombre -->
                    <div class="space-y-2">
                        <x-form.label for="name_add" :value="__('Nombre')" class="text-left" />
                        <x-form.input id="name_add" class="block w-full" type="text" name="name"
                            value="{{ old('name', '') }}" placeholder="Nombre" required />
                        <div id="name-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Campo Correo -->
                    <div class="space-y-2">
                        <x-form.label for="email_add" :value="__('Correo')" class="text-left" />
                        <x-form.input id="email_add" class="block w-full" type="email" name="email"
                            value="{{ old('email', '') }}" placeholder="Correo" required />
                        <div id="email-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Campo Celular -->
                    <div class="space-y-2">
                        <x-form.label for="celular_add" :value="__('Celular')" class="text-left" />
                        <x-form.input id="celular_add" class="block w-full" type="text" name="celular"
                            value="{{ old('celular', '') }}" placeholder="Celular (opcional)" maxlength="9"
                            pattern="9[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        <div id="celular-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Campo Dirección -->
                    <div class="space-y-2">
                        <x-form.label for="direccion_add" :value="__('Dirección')" class="text-left" />
                        <x-form.input id="direccion_add" class="block w-full" type="text" name="direccion"
                            value="{{ old('direccion', '') }}" placeholder="Dirección (opcional)" />
                        <div id="direccion-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Campo Fecha de Nacimiento -->
                    <div class="space-y-2">
                        <x-form.label for="fecha_nacimiento_add" :value="__('Fecha de Nacimiento')" class="text-left" />
                        <x-form.input id="fecha_nacimiento_add" class="block w-full" type="date" name="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento', '') }}" />
                        <div id="fecha_nacimiento-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Año de Ingreso como select -->
                    <div class="space-y-2">
                        <x-form.label for="anio_ingreso_add" :value="__('Año de Ingreso')" class="text-left" />
                        <select id="anio_ingreso_add" name="anio_ingreso"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Seleccione un año (opcional)</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ old('anio_ingreso') == $year ? 'selected' : '' }}>{{ $year }}
                                </option>
                            @endforeach
                        </select>
                        <div id="anio_ingreso-error" class="mt-1 text-xs text-red-500"></div>
                    </div>

                    <!-- Botón de Enviar -->
                    <div>
                        <x-button type="submit" variant="primary" class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('add-user-form');
            const submitButton = form.querySelector('button[type="submit"]');

            // Función para validar el formulario
            function validateForm() {
                let isValid = true;
                const requiredFields = ['run', 'name', 'email'];

                // Limpiar mensajes de error anteriores
                document.querySelectorAll('.text-red-500').forEach(el => el.textContent = '');

                // Validar campos requeridos
                requiredFields.forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    const errorElement = document.getElementById(`${field}-error`);

                    if (!input.value.trim()) {
                        errorElement.textContent = 'Este campo es obligatorio';
                        isValid = false;
                    }
                });

                // Validar RUN
                const run = form.querySelector('input[name="run"]').value;
                if (run && !/^\d{7,8}$/.test(run)) {
                    document.getElementById('run-error').textContent = 'El RUN debe ser un número de 7 u 8 dígitos';
                    isValid = false;
                }

                // Validar email
                const email = form.querySelector('input[name="email"]').value;
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.getElementById('email-error').textContent = 'Ingrese un correo electrónico válido';
                    isValid = false;
                }

                // Validar celular solo si se ingresa
                const celular = form.querySelector('input[name="celular"]').value;
                if (celular && !/^9\d{8}$/.test(celular)) {
                    document.getElementById('celular-error').textContent =
                        'El celular debe comenzar con 9 y tener 9 dígitos';
                    isValid = false;
                }

                return isValid;
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message || 'Usuario creado exitosamente',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        let errorMessage = 'Ha ocurrido un error';
                        if (data.errors) {
                            // Traducir mensajes de error comunes
                            const errorTranslations = {
                                'validation.required': 'Este campo es obligatorio',
                                'validation.email': 'Ingrese un correo electrónico válido',
                                'validation.unique': 'Este valor ya está registrado',
                                'validation.min': 'El valor debe tener al menos :min caracteres',
                                'validation.max': 'El valor no debe exceder :max caracteres',
                                'validation.numeric': 'Este campo debe ser numérico'
                            };

                            // Procesar errores y traducirlos
                            const translatedErrors = {};
                            Object.keys(data.errors).forEach(field => {
                                // Tomar solo el primer error para cada campo
                                const firstError = data.errors[field][0];
                                for (const [key, translation] of Object.entries(
                                    errorTranslations)) {
                                    if (firstError.includes(key)) {
                                        translatedErrors[field] = translation;
                                        break;
                                    }
                                }
                                if (!translatedErrors[field]) {
                                    translatedErrors[field] = firstError;
                                }
                            });

                            // Mostrar errores en los campos correspondientes
                            Object.keys(translatedErrors).forEach(field => {
                                const errorElement = document.getElementById(`${field}-error`);
                                if (errorElement) {
                                    errorElement.textContent = translatedErrors[field];
                                }
                            });

                            // Mostrar solo el primer error en el SweetAlert
                            errorMessage = Object.values(translatedErrors)[0];
                        } else if (data.message) {
                            errorMessage = data.message;
                        }

                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Ha ocurrido un error al procesar la solicitud',
                        icon: 'error'
                    });
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Guardar';
                }
            });
        });

        function deleteUser(run) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + run).submit();
                }
            });
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.querySelector('table');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < td.length; j++) {
                    const cell = td[j];
                    if (cell) {
                        const txtValue = cell.textContent || cell.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>

</x-app-layout>