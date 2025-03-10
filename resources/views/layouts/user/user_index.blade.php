<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Usuarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button target="_blank" variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-user')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>




    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <div class="flex justify-center">
            <table class="min-w-full table-auto text-center">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">RUN</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-black-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="p-3">{{ $user->run }}</td>
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">{{ $user->email }}</td>
                            <td>
                                <x-button target="_blank" href="" variant="primary"
                                    class="justify-center max-w-xs gap-2" data-id="{{ $user->id }}"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-user')">
                                    <x-icons.edit class="w-6 h-6" aria-hidden="true" />
                                </x-button>

                                <form action="{{ route('users.delete', $user->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-button target="_blank" href="" variant="danger"
                                        class="justify-center max-w-xs gap-2" data-id="{{ $user->id }}">
                                        <x-icons.delete class="w-6 h-6" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- MODAL AGREGAR  --}}
    <div class="space-y-6">
        <x-modal name="add-user" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('users.add') }}">
                @csrf

                <div class="grid gap-6 p-6">
                    <!-- RUN -->
                    <div class="space-y-2">
                        <x-form.label for="run" :value="__('RUN')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="run" class="block w-full" type="text" name="run"
                                :value="old('run')" required autofocus placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>


                    <!-- Name -->
                    <div class="space-y-2">
                        <x-form.label for="name" :value="__('Nombre')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="name" class="block w-full" type="text" name="name"
                                :value="old('name')" required autofocus placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <x-form.label for="email" :value="__('Correo')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="email" class="block w-full" type="email" name="email"
                                :value="old('email')" required placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <x-form.label for="password" :value="__('Contraseña')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="password" class="block w-full" type="password" name="password"
                                required autocomplete="new-password" placeholder="{{ __('Contraseña') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />

                            <span>{{ __('Agregar') }}</span>
                        </x-button>
                    </div>


                </div>
            </form>
        </x-modal>
    </div>

    {{-- MODAL EDITAR --}}
    <div class="space-y-6">
        <x-modal name="edit-user" :show="$errors->any()" focusable>
            <form method="" action="">
                @csrf

                <div class="grid gap-6 p-6">
                    <!-- RUN -->
                    <div class="space-y-2">
                        <x-form.label for="run" :value="__('RUN')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="run" class="block w-full" type="text" name="run"
                                :value="old('run')" required autofocus placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>


                    <!-- Name -->
                    <div class="space-y-2">
                        <x-form.label for="name" :value="__('Nombre')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="name" class="block w-full" type="text" name="name"
                                :value="old('name')" required autofocus placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <x-form.label for="email" :value="__('Correo')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="email" class="block w-full" type="email" name="email"
                                :value="old('email')" required placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <x-form.label for="password" :value="__('Contraseña')" />

                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                            </x-slot>

                            <x-form.input withicon id="password" class="block w-full" type="password"
                                name="password" required autocomplete="new-password"
                                placeholder="{{ __('Contraseña') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Editar') }}</span>
                        </x-button>
                    </div>


                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
