<x-guest-layout>
    <x-auth-card>
        <!-- Session Status -->
        <x-auth-session-status class="m-4" :status="session('status')" />

        @if (session('session_expired'))
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800">
                            {{ session('session_expired') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="grid gap-6 mt-8">
                <div class="space-y-2">
                    <x-form.label for="run" :value="__('RUN')" />

                    <x-form.input-with-icon-wrapper>
                     
                        <x-form.input withicon id="run" class="block w-full" type="text" name="run"
                            :value="old('run')" placeholder="{{ __('Ej: 12345678') }}" required autofocus x-data
                            x-on:input="
                                let value = $event.target.value.replace(/[^0-9]/g, '');
                                if (value.length > 8) {
                                    value = value.slice(0, 8);
                                }
                                $event.target.value = value;
                            "
                            maxlength="8" />
                    </x-form.input-with-icon-wrapper>
                </div>

                <div class="space-y-2">
                    <x-form.label for="password" :value="__('Contraseña')" />

                    <x-form.input-with-icon-wrapper>
                        <x-slot name="icon">
                            <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                        </x-slot>

                        <x-form.input withicon id="password" class="block w-full" type="password" name="password"
                            required autocomplete="current-password" placeholder="{{ __('Contraseña') }}" />
                    </x-form.input-with-icon-wrapper>
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="border-gray-300 rounded text-dark-royal-blue-500 focus:border-purple-300 focus:ring focus:bg-dark-royal-blue-500 dark:border-gray-600 dark:bg-dark-eval-1 dark:focus:ring-offset-dark-eval-1"
                            name="remember">

                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Recordar') }}
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-blue-500 hover:underline" href="{{ route('password.request') }}">
                            {{ __('¿Olvidaste la contraseña?') }}
                        </a>
                    @endif
                </div>

                <div>
                    <x-button class="justify-center w-full gap-1">
                        <x-heroicon-o-login class="w-7 h-7" aria-hidden="true" />
                        <span>{{ __('Ingresar') }}</span>
                    </x-button>
                </div>
            </div>
        </form>
    </x-auth-card>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const intendedUrl = localStorage.getItem('intended_url');
            
            if (intendedUrl) {
                localStorage.removeItem('intended_url');
                
                const form = document.querySelector('form[action="{{ route("login") }}"]');
                if (form) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'intended_url';
                    hiddenInput.value = intendedUrl;
                    form.appendChild(hiddenInput);
                }
            }
        });
    </script>
</x-guest-layout>
