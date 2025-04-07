<x-perfect-scrollbar as="nav" aria-label="main" class="flex flex-col flex-1 gap-4 px-3" style="margin-top:50px;">

    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>



    @role('admin')
        <x-sidebar.dropdown title="Usuarios" :active="Str::startsWith(request()->route()->uri(), 'users')">
            <x-slot name="icon">
                <x-icons.user class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
            <x-sidebar.sublink title="Usuarios" href="{{ route('users.index') }}" :active="request()->routeIs('users.index')" />
            <x-sidebar.sublink title="Roles" href="{{ route('roles.index') }}" :active="request()->routeIs('roles.index')" />
            <x-sidebar.sublink title="Permisos" href="{{ route('permissions.index') }}" :active="request()->routeIs('permissions.index')" />
        </x-sidebar.dropdown>
        <x-sidebar.dropdown title="Universidad" :active="Str::startsWith(request()->route()->uri(), 'universidades')">
            <x-slot name="icon">
                <x-icons.university class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
            <x-sidebar.sublink title="Universidad" href="{{ route('universities.index') }}" :active="request()->routeIs('universitys.index')" />
            <x-sidebar.sublink title="Facultad" href="{{ route('faculties.index') }}" :active="request()->routeIs('faculties.index')" />
            <x-sidebar.sublink title="Áreas Académicas" href="{{ route('academic_areas.index') }}" :active="request()->routeIs('academic_areas.index')" />
            <x-sidebar.sublink title="Carreras" href="{{ route('careers.index') }}" :active="request()->routeIs('users.index')" />
            <x-sidebar.sublink title="Gestión Pisos/Espacios" href="{{ route('floors_spaces.index') }}" :active="request()->routeIs('floors_spaces.index')" /> 

        </x-sidebar.dropdown>
    @endrole

    {{-- <x-sidebar.dropdown title="Buttons" :active="Str::startsWith(request()->route()->uri(), 'buttons')">
        <x-slot name="icon">
            <x-heroicon-o-view-grid class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>

        <x-sidebar.sublink title="Text button" href="{{ route('buttons.text') }}"
            :active="request()->routeIs('buttons.text')" />
        <x-sidebar.sublink title="Icon button" href="{{ route('buttons.icon') }}"
            :active="request()->routeIs('buttons.icon')" />
        <x-sidebar.sublink title="Text with icon" href="{{ route('buttons.text-icon') }}"
            :active="request()->routeIs('buttons.text-icon')" />
    </x-sidebar.dropdown>

    <div x-transition x-show="isSidebarOpen || isSidebarHovered" class="text-sm text-gray-500">
        Dummy Links
    </div>

    @php
        $links = array_fill(0, 20, '');
    @endphp

    @foreach ($links as $index => $link)
        <x-sidebar.link title="Dummy link {{ $index + 1 }}" href="#" />
    @endforeach --}}

</x-perfect-scrollbar>
