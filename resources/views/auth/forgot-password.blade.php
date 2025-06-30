<x-guest-layout>
    <x-auth-card>
        <div class="mb-4 text-gray-600 dark:text-gray-400 max-w-xl" style="text-align: justify;">
            {{ __('¿Olvidó su contraseña? No hay problema. Simplemente indíquenos su dirección de correo institucional y le enviaremos un enlace para restablecer su contraseña que le permitirá elegir una nueva.') }}
        </div>
        
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="grid gap-6">
                <!-- Email Address -->
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
                    <x-button class="justify-center w-full">
                        {{ __('Enviar correo con enlace de recuperación') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>