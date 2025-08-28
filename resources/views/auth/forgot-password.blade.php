@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <x-application-logo class="mx-auto h-12 w-auto" />
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    {{ __('Recuperar Contraseña') }}
                </h2>
            </div>

            <div class="bg-white dark:bg-dark-eval-1 shadow-xl rounded-xl px-8 py-6">
                <div class="mb-4 text-gray-600 dark:text-gray-400 text-sm text-center">
                    {{ __('¿Olvidó su contraseña? No hay problema. Simplemente indíquenos su dirección de correo institucional y le enviaremos un enlace para restablecer su contraseña que le permitirá elegir una nueva.') }}
                </div>
                
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
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

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ __('Volver al login') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection