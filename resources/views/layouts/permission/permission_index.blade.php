<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Permisos</h2>
                    <p class="text-sm text-gray-500">Administra los permisos asignables a roles y usuarios</p>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-end mb-4">
            <x-button variant="add" class="justify-end max-w-xs gap-2"
                x-on:click.prevent="$dispatch('open-modal', 'add-permission')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>
        <livewire:permissions-table />


        <x-modal name="add-permission" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('permission.add') }}">
                @csrf
                @slot('title')
                    <h1 class="text-lg font-medium text-white dark:text-gray-100">
                        Agregar Permiso </h1>
                @endslot
                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <x-form.label for="name_permission" :value="__('Nombre del Permiso')" class="text-left" />
                        <x-form.input id="name_permission" class="block w-full" type="text" name="name" required
                            autofocus placeholder="{{ __('Nombre del permiso') }}" />
                    </div>
                    <div class="flex justify-end">
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            {{ __('Agregar Permiso') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif

        const form = document.getElementById('edit-permission-form');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Seguro de editar?',
                    text: "Estás a punto de guardar los cambios.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, editar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    </script>
</x-app-layout>
