<x-guest-layout>
    <x-auth-card>
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ __('Recuperar Contraseña') }}
                </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('¿Olvidó su contraseña? No hay problema. Simplemente indíquenos su dirección de correo institucional y le enviaremos un enlace para restablecer su contraseña que le permitirá elegir una nueva.') }}
            </p>
                </div>

                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                    @csrf

            <div class="grid gap-6">
                <div class="space-y-2">
                        <x-form.label for="email" :value="__('Correo')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="email" class="block w-full" type="email" name="email"
                                :value="old('email')" required autofocus placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                    <x-button class="justify-center w-full gap-1">
                        <x-heroicon-o-mail class="w-7 h-7" aria-hidden="true" />
                        <span>{{ __('Enviar correo con enlace de recuperación') }}</span>
                        </x-button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ __('Volver al Inicio') }}
                        </a>
                </div>
                    </div>
                </form>
    </x-auth-card>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            
            // Función para refrescar el token CSRF
            function refreshCsrfToken() {
                return fetch('/csrf-token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.token) {
                        document.querySelector('input[name="_token"]').value = data.token;
                    }
                })
                .catch(error => {
                    console.error('Error refreshing CSRF token:', error);
                });
            }
            
            // Verificar si hay mensaje de sesión (éxito)
            @if(session('status'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Mensaje enviado! ',
                    text: 'Hemos enviado el enlace de recuperación a tu correo. ¡Revisa tu bandeja de entrada (y la carpeta de spam, por si acaso)! 🚀',
                    confirmButtonText: '¡Perfecto!',
                    confirmButtonColor: '#10B981'
                }).then((result) => {
                    window.location.href = '{{ route("login") }}';
                });
            @endif
            
            form.addEventListener('submit', function(e) {
                // Prevenir el envío normal del formulario
                e.preventDefault();
                
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Enviando enlace de recuperación',
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
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 419) {
                            // Token CSRF expirado - intentar refrescar automáticamente
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesión Expirada',
                                text: 'Refrescando tu sesión automáticamente...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Intentar refrescar el token CSRF
                            refreshCsrfToken().then(() => {
                                Swal.close();
                                // Reintentar el envío del formulario
                                form.dispatchEvent(new Event('submit'));
                            }).catch(() => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Sesión',
                                    text: 'No se pudo refrescar tu sesión. Por favor, recarga la página.',
                                    confirmButtonText: 'Recargar Página',
                                    confirmButtonColor: '#EF4444'
                                }).then(() => {
                                    window.location.reload();
                                });
                            });
                            return;
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        // Éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Mensaje enviado!',
                            text: 'Hemos enviado el enlace de recuperación a tu correo. ¡Revisa tu bandeja de entrada (y la carpeta de spam, por si acaso)! 🚀',
                            confirmButtonText: '¡Perfecto!',
                            confirmButtonColor: '#10B981'
                        }).then((result) => {
                            window.location.href = '{{ route("login") }}';
                        });
                    } else if (data) {
                        // Error
                        Swal.fire({
                            icon: 'error',
                            title: '¡Ups! Algo salió mal',
                            text: data.message || 'Hubo un problema al enviar el correo. Inténtalo de nuevo.',
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
