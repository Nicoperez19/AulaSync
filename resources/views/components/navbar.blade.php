<nav aria-label="secondary" x-data="{ open: false }"
    class="sticky top z-50 flex items-center justify-between px-3 py-2 bg-light-cloud-blue sm:px-6 dark:bg-dark-eval-1 shadow-[0_4px_6px_rgba(255,255,255,0.3)]">
    <div class="flex items-center gap-3">

        <!-- Botón Toggle (Sin interacción de colores, solo alterna estado expandido/colapsado) -->
        <button type="button" 
            aria-label="Alternar menú lateral" 
            title="Alternar menú lateral"
            class="inline-flex items-center justify-center p-2 text-white hover:bg-white/10 active:bg-white/20 rounded-md focus:outline-none transition-all duration-150"
            x-on:click="isSidebarOpen = !isSidebarOpen">
            <x-icons.menu-fold-right x-show="!isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
            <x-icons.menu-fold-left x-show="isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
        </button>

        <!-- Logo -->
        <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : (auth()->user()->hasRole('Profesor') && !auth()->user()->hasAnyRole(['Administrador', 'Supervisor', 'Director', 'Estudiante en Práctica']) ? route('docente.dashboard') : route('dashboard')) }}" class="flex items-center">
            <x-application-logo-navbar />
        </a>
    </div>

    {{-- <div class="flex items-center gap-3">
        <x-button type="button" class="md:hidden" icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button>
    </div> --}}

    <div class="flex items-center gap-3">
        {{-- <x-button type="button" class="hidden md:inline-flex " icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button> --}}

        <!-- Botón Acciones Rápidas - Para usuarios con permiso -->
        @can('acciones rapidas')
            <a href="{{ route('quick-actions.index') }}" 
               class="hidden md:inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-white/20 border border-white/40 rounded-md hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-cloud-blue-500 transition-colors duration-200"
               title="Acciones Rápidas">
                <svg class="w-4 h-4 mr-2 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="hidden lg:inline">Acciones Rápidas</span>
            </a>
        @endcan

        <!-- Notificaciones - Para supervisores y administradores -->
        @hasanyrole('Supervisor|Administrador')
            @livewire('notificaciones-dropdown')
        @endhasanyrole

   

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="flex items-center p-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:text-white focus:outline-none focus:ring focus:ring-white focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-dark-eval-1 dark:text-white dark:hover:text-white">
                    <div>{{ Auth::user()->name }}</div>

                    <div class="ml-1">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    <x-heroicon-s-user class="w-4 h-4 mr-2 inline" aria-hidden="true" />
                    {{ __('Perfil') }}
                </x-dropdown-link>

                @if(Auth::user()->isSuperAdmin() || (Auth::user()->hasRole('Administrador') && !Auth::user()->id_sede))
                    <x-dropdown-link :href="route('sedes.selection')">
                        <x-heroicon-s-office-building class="w-4 h-4 mr-2 inline" aria-hidden="true" />
                        {{ __('Cambiar Sede') }}
                    </x-dropdown-link>
                @endif

                <x-dropdown-link :href="route('manual.index')">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    {{ __('Ayuda') }}
                </x-dropdown-link>

                <x-dropdown-link :href="route('soporte.index')">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    {{ __('Contactar Soporte') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</nav>

<script>
window.NavbarConfig = {
    horariosModulos: @json(\App\Helpers\ModulosHelper::getHorariosModulos())
};
</script>
@vite(['resources/js/views/components/navbar.js'])
