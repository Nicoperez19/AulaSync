<nav aria-label="main" class="flex flex-col gap-4 my-[2rem] pt-2" style="color:white;">
    <!-- Dashboard -->
    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Monitoreo de Espacios -->
    <x-sidebar.link title="Monitoreo de Espacios"
        href="{{ $primerMapa ? route('plano.show', $primerMapa->id_mapa) : '#' }}"
        :isActive="request()->routeIs('plano.show')"
        onclick="{{ !$primerMapa ? 'mostrarSweetAlertNoMapas(event)' : '' }}">
        <x-slot name="icon">
            <x-icons.location class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Horarios Espacios -->
    <x-sidebar.link title="Horarios por Espacios"
        href="{{ $sede ? route('espacios.show', $sede->id_sede) : route('dashboard') }}"
        :isActive="request()->routeIs('espacios.show')">
        <x-slot name="icon">
            <x-icons.clock class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Horarios Profesores -->
    <x-sidebar.link title="Horarios Profesores" href="{{ route('horarios.index') }}"
        :isActive="request()->routeIs('horarios.index')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Carga Masiva -->

    <x-sidebar.link title="Carga Masiva" href="{{ route('data.index') }}" :isActive="request()->routeIs('data.index')">
        <x-slot name="icon">
            <x-icons.upload class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Tablero Académico -->
    <x-sidebar.link title="Tablero Académico" href="{{ route('modulos.actuales') }}"
        :isActive="request()->routeIs('modulos.actuales')">
        <x-slot name="icon">
            <x-icons.table class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Reportería -->
    <x-sidebar.dropdown title="Reportes" :active="Str::startsWith(request()->route()->uri(), 'reporteria')">
        <x-slot name="icon">
            <x-icons.chart-bar class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>

        <x-sidebar.sublink title="Accesos registrados" href="{{ route('reporteria.accesos') }}"
            :isActive="request()->routeIs('reporteria.accesos')" />
        <x-sidebar.sublink title="Análisis por tipo de espacio" href="{{ route('reporteria.tipo-espacio') }}"
            :isActive="request()->routeIs('reporteria.tipo-espacio')" />
        <x-sidebar.sublink title="Análisis por espacios" href="{{ route('reporteria.espacios') }}"
            :isActive="request()->routeIs('reporteria.espacios')" />
        {{-- <x-sidebar.sublink title="Por unidad académica" href="{{ route('reporteria.unidad-academica') }}"
            :isActive="request()->routeIs('reporteria.unidad-academica')" /> --}}
    </x-sidebar.dropdown>





    @role('Administrador')
    <x-sidebar.dropdown title="Mantenedores" :active="Str::startsWith(request()->route()->uri(), 'users')">
        <x-slot name="icon">
            <x-icons.config class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
        <x-sidebar.sublink title="Asignaturas" href="{{ route('asignaturas.index') }}"
            :isActive="request()->routeIs('asignaturas.index')" />
        <x-sidebar.sublink title="Carreras" href="{{ route('careers.index') }}"
            :isActive="request()->routeIs('careers.index')" />
        <x-sidebar.sublink title="Espacios" href="{{ route('spaces_index') }}"
            :isActive="request()->routeIs('espacios.index')" />
        <x-sidebar.sublink title="Mapa" href="{{ route('mapas.index') }}"
            :isActive="request()->routeIs('maps.index')" />
        <x-sidebar.sublink title="Permisos" href="{{ route('permissions.index') }}"
            :isActive="request()->routeIs('permissions.index')" />
        <x-sidebar.sublink title="Pisos" href="{{ route('floors_index') }}"
            :isActive="request()->routeIs('floors_index')" />
        <x-sidebar.sublink title="Reservas" href="{{ route('reservas.index') }}"
            :isActive="request()->routeIs('reservas.index')" />
        <x-sidebar.sublink title="Roles" href="{{ route('roles.index') }}"
            :isActive="request()->routeIs('roles.index')" />
        <x-sidebar.sublink title="Usuarios" href="{{ route('users.index') }}"
            :isActive="request()->routeIs('users.index')" />
    </x-sidebar.dropdown>
    @endrole

    @role('Auxiliar')
    <x-sidebar.dropdown title="Visualizador" :active="Str::startsWith(request()->route()->uri(), 'universidades')">
        <x-slot name="icon">
            <x-icons.university class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
        <x-sidebar.sublink title="Mapa" href="{{ route('mapas.index') }}"
            :isActive="request()->routeIs('maps.index')" />
    </x-sidebar.dropdown>
    @endrole
</nav>