<nav aria-label="secondary" x-data="{ open: false }"
    class="sticky top z-50 flex items-center justify-between px-3 py-2 bg-light-cloud-blue sm:px-6 dark:bg-dark-eval-1 shadow-[0_4px_6px_rgba(255,255,255,0.3)]">
    <div class="flex items-center gap-3">

        <!-- Botón Toggle -->
        <x-button type="button" icon-only sr-text="Toggle sidebar" class="bg-cloud-blue-500 dark:bg-dark-eval-1"
            x-on:click="isSidebarOpen = !isSidebarOpen">
            <x-icons.menu-fold-right x-show="!isSidebarOpen" aria-hidden="true" class="w-6 h-6 lg:block" />
            <x-icons.menu-fold-left x-show="isSidebarOpen" aria-hidden="true" class="w-6 h-6 lg:block" />
        </x-button>

        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <x-application-logo />
        </a>
    </div>

    <div class="flex items-center gap-3">
        <x-button type="button" class="md:hidden" icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button>
    </div>

    <div class="flex items-center gap-3">
        <x-button type="button" class="hidden md:inline-flex " icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button>

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
                    {{ __('Perfil') }}
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

<div
    class="fixed inset-x-0 bottom-0 flex items-center justify-between px-4 py-4 bg-light-cloud-blue sm:px-6 md:hidden dark:bg-dark-eval-1">
    <x-button type="button" icon-only variant="secondary" sr-text="Search">
        <x-heroicon-o-search aria-hidden="true" class="w-6 h-6" />
    </x-button>

    <a href="{{ route('dashboard') }}">
        <x-application-logo aria-hidden="true" class="w-10 h-10" />
        <span class="sr-only">Dashboard</span>
    </a>

    <x-button type="button" icon-only variant="secondary" sr-text="Open main menu"
        x-on:click="isSidebarOpen = !isSidebarOpen">
        <x-heroicon-o-menu x-show="!isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
        <x-heroicon-o-x x-show="isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
    </x-button>
</div>
