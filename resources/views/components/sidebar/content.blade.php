<nav aria-label="main" class="flex flex-col gap-4 my-[2rem] pt-2" style="color:white;">
    <!-- Dashboard - Solo Administrador y Supervisor (NO Usuario) -->
    @role('Administrador|Supervisor')
    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>
    @endrole

    <!-- Monitoreo de Espacios - Todos los roles -->
    @can('monitoreo de espacios')
        <x-sidebar.link title="Monitoreo de Espacios"
            href="{{ $primerMapa ? route('plano.show', $primerMapa->id_mapa) : '#' }}"
            :isActive="request()->routeIs('plano.show')"
            onclick="{{ !$primerMapa ? 'mostrarSweetAlertNoMapas(event)' : '' }}">
            <x-slot name="icon">
                <x-icons.location class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endcan

    <!-- Horarios Espacios - Solo Administrador y Supervisor -->
    @can('horarios por espacios')
        <x-sidebar.link title="Horarios por Espacios"
            href="{{ $sede ? route('espacios.show', $sede->id_sede) : (auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard')) }}"
            :isActive="request()->routeIs('espacios.show')">
            <x-slot name="icon">
                <x-icons.clock class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endcan

    <!-- Horarios Profesores - Solo Administrador y Supervisor -->
    @can('horarios profesores')
        <x-sidebar.link title="Horarios Profesores" href="{{ route('horarios.index') }}"
            :isActive="request()->routeIs('horarios.index')">
            <x-slot name="icon">
                <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endcan

    <!-- Carga Masiva - Solo Administrador -->
    @can('mantenedor de carga de datos')
        <x-sidebar.link title="Carga Masiva" href="{{ route('data.index') }}" :isActive="request()->routeIs('data.index')">
            <x-slot name="icon">
                <x-icons.upload class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endcan

    <!-- Tablero Académico - Todos los roles -->
    @can('tablero academico')
        <x-sidebar.link title="Tablero Académico" href="{{ route('modulos.actuales') }}"
            :isActive="request()->routeIs('modulos.actuales')">
            <x-slot name="icon">
                <x-icons.table class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endcan

    <!-- Reportería - Solo Administrador y Supervisor -->
    @can('reportes')
        <x-sidebar.dropdown title="Reportes" :active="Str::startsWith(request()->route()->uri(), 'reportes')">
            <x-slot name="icon">
                <x-icons.chart-bar class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>

            <x-sidebar.sublink title="Accesos registrados" href="{{ route('reportes.accesos') }}"
                :isActive="request()->routeIs('reportes.accesos')" />
            <x-sidebar.sublink title="Análisis por tipo de espacio" href="{{ route('reportes.tipo-espacio') }}"
                :isActive="request()->routeIs('reportes.tipo-espacio')" />
            <x-sidebar.sublink title="Análisis por espacios" href="{{ route('reportes.espacios') }}"
                :isActive="request()->routeIs('reportes.espacios')" />
            {{-- <x-sidebar.sublink title="Por unidad académica" href="{{ route('reportes.unidad-academica') }}"
                :isActive="request()->routeIs('reportes.unidad-academica')" /> --}}
        </x-sidebar.dropdown>
    @endcan

    <!-- Mantenedores - Solo Administrador -->
    @canany(['mantenedor de roles', 'mantenedor de permisos', 'mantenedor de universidades', 'mantenedor de facultades', 'mantenedor de areas academicas', 'mantenedor de carreras', 'mantenedor de pisos', 'mantenedor de espacios', 'mantenedor de reservas', 'mantenedor de asignaturas', 'mantenedor de mapas', 'mantenedor de campus', 'mantenedor de sedes', 'mantenedor de profesores'])
        <x-sidebar.dropdown title="Mantenedores" :active="Str::startsWith(request()->route()->uri(), 'users')">
            <x-slot name="icon">
                <x-icons.config class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>

            @can('mantenedor de areas academicas')
                <x-sidebar.sublink title="Áreas Académicas" href="{{ route('academic_areas.index') }}"
                    :isActive="request()->routeIs('academic_areas.index')" />
            @endcan

            @can('mantenedor de asignaturas')
                <x-sidebar.sublink title="Asignaturas" href="{{ route('asignaturas.index') }}"
                    :isActive="request()->routeIs('asignaturas.index')" />
            @endcan

            @can('mantenedor de campus')
                <x-sidebar.sublink title="Campus" href="{{ route('campus.index') }}"
                    :isActive="request()->routeIs('campus.index')" />
            @endcan

            @can('mantenedor de carreras')
                <x-sidebar.sublink title="Carreras" href="{{ route('careers.index') }}"
                    :isActive="request()->routeIs('careers.index')" />
            @endcan

            @can('mantenedor de espacios')
                <x-sidebar.sublink title="Espacios" href="{{ route('spaces_index') }}"
                    :isActive="request()->routeIs('espacios.index')" />
            @endcan

            @can('mantenedor de facultades')
                <x-sidebar.sublink title="Facultades" href="{{ route('faculties.index') }}"
                    :isActive="request()->routeIs('faculties.index')" />
            @endcan

            @can('mantenedor de mapas')
                <x-sidebar.sublink title="Mapa" href="{{ route('mapas.index') }}"
                    :isActive="request()->routeIs('maps.index')" />
            @endcan

            @can('mantenedor de permisos')
                <x-sidebar.sublink title="Permisos" href="{{ route('permissions.index') }}"
                    :isActive="request()->routeIs('permissions.index')" />
            @endcan

            @can('mantenedor de pisos')
                <x-sidebar.sublink title="Pisos" href="{{ route('floors_index') }}"
                    :isActive="request()->routeIs('floors_index')" />
            @endcan

            @can('mantenedor de profesores')
                <x-sidebar.sublink title="Profesores" href="{{ route('professors.index') }}"
                    :isActive="request()->routeIs('professors.index')" />
            @endcan

            @can('mantenedor de reservas')
                <x-sidebar.sublink title="Reservas" href="{{ route('reservas.index') }}"
                    :isActive="request()->routeIs('reservas.index')" />
            @endcan

            @can('mantenedor de roles')
                <x-sidebar.sublink title="Roles" href="{{ route('roles.index') }}"
                    :isActive="request()->routeIs('roles.index')" />
            @endcan

            @can('mantenedor de sedes')
                <x-sidebar.sublink title="Sedes" href="{{ route('sedes.index') }}"
                    :isActive="request()->routeIs('sedes.index')" />
            @endcan

            @can('mantenedor de universidades')
                <x-sidebar.sublink title="Universidades" href="{{ route('universities.index') }}"
                    :isActive="request()->routeIs('universities.index')" />
            @endcan

            @can('mantenedor de usuarios')
                <x-sidebar.sublink title="Usuarios" href="{{ route('users.index') }}"
                    :isActive="request()->routeIs('users.index')" />
            @endcan
        </x-sidebar.dropdown>
    @endcanany

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