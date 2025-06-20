<section>
    <header>
        <h2 class="text-lg font-medium">
            {{ __('Actualizar Contraseña') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerse segura.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-2">
            <x-form.label for="current_password" :value="__('Contraseña Actual')" />

            <x-form.input id="current_password" name="current_password" type="password" class="block w-full"
                autocomplete="current-password" />

            <x-form.error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="space-y-2">
            <x-form.label for="password" :value="__('Nueva Contraseña')" />

            <x-form.input id="password" name="password" type="password" class="block w-full"
                autocomplete="new-password" />

            <x-form.error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="space-y-2">
            <x-form.label for="password_confirmation" :value="__('Confirmar Contraseña')" />

            <x-form.input id="password_confirmation" name="password_confirmation" type="password" class="block w-full"
                autocomplete="new-password" />

            <x-form.error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <x-button>
                {{ __('Guardar') }}
            </x-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Guardado.') }}
                </p>
            @endif
        </div>
    </form>
</section>
