<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Reservas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-reserva')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('reservations-table')

    {{-- Modal para agregar reserva --}}
    <x-modal name="add-reserva" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('reservas.add') }}">
            @csrf
            <div class="p-6 space-y-6">
                <!-- ID de la Reserva -->
                <div class="space-y-2">
                    <x-form.label for="id_reserva" :value="__('ID de la Reserva')" class="text-left" />
                    <x-form.input id="id_reserva" class="block w-full" type="text" name="id_reserva" required autofocus placeholder="{{ __('ID de la reserva') }}" />
                </div>

                <!-- Hora -->
                <div class="space-y-2">
                    <x-form.label for="hora" :value="__('Hora')" class="text-left" />
                    <x-form.input id="hora" class="block w-full" type="time" name="hora" required />
                </div>

                <!-- Fecha de la Reserva -->
                <div class="space-y-2">
                    <x-form.label for="fecha_reserva" :value="__('Fecha de la Reserva')" class="text-left" />
                    <x-form.input id="fecha_reserva" class="block w-full" type="date" name="fecha_reserva" required />
                </div>

                <!-- Espacio -->
                <div class="space-y-2">
                    <x-form.label for="id_espacio" :value="__('ID del Espacio')" class="text-left" />
                    <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio" required placeholder="{{ __('ID del espacio') }}" />
                </div>

                <!-- Usuario -->
                <div class="space-y-2">
                    <x-form.label for="id" :value="__('ID del Usuario')" class="text-left" />
                    <x-form.input id="id" class="block w-full" type="number" name="id" required placeholder="{{ __('ID del usuario') }}" />
                </div>

                <div class="flex justify-end">
                    <x-button class="justify-center w-full gap-2">
                        <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                        {{ __('Agregar Reserva') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>
</x-app-layout>