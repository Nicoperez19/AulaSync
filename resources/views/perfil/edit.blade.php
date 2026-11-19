<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Información del Perfil</h2>
                    <p class="text-sm text-gray-500">Actualice la información de su perfil y su correo electrónico.</p>
                </div>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'profile-updated')
@vite(['resources/js/views/perfil/edit.js'])
@endpush
</x-app-layout>
