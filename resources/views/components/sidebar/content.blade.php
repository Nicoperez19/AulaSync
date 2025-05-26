<nav aria-label="main" class="flex flex-col gap-4 my-[2rem]" style="color:white;">
    <!-- Dashboard -->
    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Monitoreo de Espacios -->
    <x-sidebar.link title="Monitoreo de Espacios" href="{{ route('plano.index') }}" :isActive="request()->routeIs('plano.*')">
        <x-slot name="icon">
            <x-icons.location class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Horarios -->
    <x-sidebar.link title="Horarios de Uso" href="" :isActive="request()->routeIs('')">
        <x-slot name="icon">
            <x-icons.clock class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Gestión de Reservas -->
    <x-sidebar.link title="Reservas" href="" :isActive="request()->routeIs('')">
        <x-slot name="icon">
            <x-icons.calendar class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <!-- Mapa -->
    <x-sidebar.link title="Mapa de Espacios" href="" :isActive="request()->routeIs('')">
        <x-slot name="icon">
            <x-icons.map class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>
    <!-- Reportería -->
    <x-sidebar.link title="Reportes" href="" :isActive="request()->routeIs('')">
        <x-slot name="icon">
            <x-icons.chart-bar class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

      <!-- Horarios Profesores -->
    <x-sidebar.link title="Horarios_Profesores" href="{{ route('horarios.index') }}" :isActive="request()->routeIs('horarios.index')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    @role('Administrador')
        <x-sidebar.dropdown title="Mantenedores" :active="Str::startsWith(request()->route()->uri(), 'users')">
            <x-slot name="icon">
                <x-icons.config class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>

            {{-- Gestión de Accesos --}}
            <x-sidebar.sublink title="Usuarios" href="{{ route('users.index') }}" :isActive="request()->routeIs('users.index')" />
            <x-sidebar.sublink title="Roles" href="{{ route('roles.index') }}" :isActive="request()->routeIs('roles.index')" />
            <x-sidebar.sublink title="Permisos" href="{{ route('permissions.index') }}" :isActive="request()->routeIs('permissions.index')" />

            {{-- Estructura Académica --}}
            <x-sidebar.sublink title="Universidad" href="{{ route('universities.index') }}" :isActive="request()->routeIs('universitys.index')" />
            <x-sidebar.sublink title="Facultad" href="{{ route('faculties.index') }}" :isActive="request()->routeIs('faculties.index')" />
            <x-sidebar.sublink title="Áreas Académicas" href="{{ route('academic_areas.index') }}" :isActive="request()->routeIs('academic_areas.index')" />
            <x-sidebar.sublink title="Carreras" href="{{ route('careers.index') }}" :isActive="request()->routeIs('careers.index')" />
            <x-sidebar.sublink title="Asignaturas" href="{{ route('asignaturas.index') }}" :isActive="request()->routeIs('asignaturas.index')" />

            {{-- Infraestructura --}}
            <x-sidebar.sublink title="Pisos" href="{{ route('floors_index') }}" :isActive="request()->routeIs('floors_index')" />
            <x-sidebar.sublink title="Espacios" href="{{ route('spaces_index') }}" :isActive="request()->routeIs('espacios.index')" />
            <x-sidebar.sublink title="Mapa" href="{{ route('mapas.index') }}" :isActive="request()->routeIs('maps.index')" />

            {{-- Operaciones --}}
            <x-sidebar.sublink title="Reservas" href="{{ route('reservas.index') }}" :isActive="request()->routeIs('reservas.index')" />

            {{-- Carga Masiva --}}
            <x-sidebar.sublink title="Carga Masiva" href="{{ route('data.index') }}" :isActive="request()->routeIs('data.index')" />
        </x-sidebar.dropdown>
    @endrole

    @role('Auxiliar')
        <x-sidebar.dropdown title="Visualizador" :active="Str::startsWith(request()->route()->uri(), 'universidades')">
            <x-slot name="icon">
                <x-icons.university class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
            <x-sidebar.sublink title="Mapa" href="{{ route('mapas.index') }}" :isActive="request()->routeIs('maps.index')" />
        </x-sidebar.dropdown>
    @endrole
</nav>
