<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="p-4 bg-white shadow sm:p-8 sm:rounded-lg dark:bg-gray-800">
            <div class="w-full max-w-4xl mx-auto">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 bg-white shadow sm:p-8 sm:rounded-lg dark:bg-gray-800">
            <div class="w-full max-w-4xl mx-auto">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
