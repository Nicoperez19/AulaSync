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
                        <x-button variant="login" class="justify-center w-full gap-1">
                            <x-heroicon-o-mail class="w-7 h-7" aria-hidden="true" />
                            <span>{{ __('Enviar correo con enlace de recuperación') }}</span>
                        </x-button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-sm text-[#D2091E] hover:text-[#B50718] hover:underline dark:text-red-400">
                            {{ __('Volver al Inicio') }}
                        </a>
                    </div>
                    </div>
                </form>
    </x-auth-card>

    <script>
        window.ForgotPasswordConfig = {
            hasStatus: @json((bool) session('status')),
            loginUrl: "{{ route('login') }}"
        };
    </script>
    @vite(['resources/js/views/auth/forgot-password.js'])
</x-guest-layout>
