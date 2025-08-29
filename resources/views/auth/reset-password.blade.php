<x-guest-layout>
    <x-auth-card>
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('Restablecer Contraseña') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Ingresa tu nueva contraseña para restablecer tu cuenta.') }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <input type="hidden" name="email" value="{{ $request->email }}">

            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-form.label for="password" :value="__('Nueva Contraseña')" />

                    <x-form.input-with-icon-wrapper>
                        <x-slot name="icon">
                            <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                        </x-slot>

                        <x-form.input withicon id="password" class="block w-full" type="password" name="password"
                            required autocomplete="new-password" placeholder="{{ __('Nueva contraseña') }}" />
                    </x-form.input-with-icon-wrapper>
                </div>

                <div class="space-y-2">
                    <x-form.label for="password_confirmation" :value="__('Confirmar Contraseña')" />

                    <x-form.input-with-icon-wrapper>
                        <x-slot name="icon">
                            <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                        </x-slot>

                        <x-form.input withicon id="password_confirmation" class="block w-full" type="password" name="password_confirmation"
                            required autocomplete="new-password" placeholder="{{ __('Confirmar contraseña') }}" />
                    </x-form.input-with-icon-wrapper>
                </div>

                <div>
                    <x-button class="justify-center w-full gap-1" type="submit">
                        <x-heroicon-o-key class="w-7 h-7" aria-hidden="true" />
                        <span>{{ __('Restablecer Contraseña') }}</span>
                    </x-button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ __('Volver al Login') }}
                    </a>
                </div>
            </div>
        </form>
    </x-auth-card>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            
            form.addEventListener('submit', function(e) {
                // Prevenir el envío normal del formulario
                e.preventDefault();
                
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Restableciendo tu contraseña',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar el formulario
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Contraseña Restablecida! 🎉',
                            text: 'Tu contraseña ha sido actualizada exitosamente. Serás redirigido al login.',
                            confirmButtonText: '¡Perfecto!',
                            confirmButtonColor: '#10B981'
                        }).then((result) => {
                            window.location.href = '{{ route("login") }}';
                        });
                    } else {
                        // Error
                        Swal.fire({
                            icon: 'error',
                            title: '¡Ups! Algo salió mal 😅',
                            text: data.message || 'Hubo un problema al restablecer tu contraseña. Inténtalo de nuevo.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#EF4444'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Verifica tu conexión e inténtalo de nuevo.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#EF4444'
                    });
                });
            });
        });
    </script>
</x-guest-layout>
