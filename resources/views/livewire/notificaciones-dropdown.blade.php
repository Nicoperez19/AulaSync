<div class="relative" x-data="{ open: @entangle('mostrarDropdown') }">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open"
        type="button" 
        class="relative p-2 text-white rounded-md hover:bg-cloud-blue-600 dark:hover:bg-dark-eval-2 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-cloud-blue-500 transition-colors duration-200"
        title="Notificaciones">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($contadorNoLeidas > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $contadorNoLeidas > 99 ? '99+' : $contadorNoLeidas }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 w-80 mt-2 origin-top-right bg-white rounded-md shadow-lg dark:bg-dark-eval-1 ring-1 ring-black ring-opacity-5"
        style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Notificaciones
                </h3>
                @if($contadorNoLeidas > 0)
                    <button 
                        wire:click="marcarTodasComoLeidas"
                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        Marcar todas como leídas
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notificaciones as $notificacion)
                <a 
                    href="{{ $notificacion->url }}"
                    wire:click="marcarComoLeida({{ $notificacion->id }})"
                    class="block px-4 py-3 transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-dark-eval-2 border-b border-gray-200 dark:border-gray-700 {{ !$notificacion->leida ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($notificacion->tipo === 'clase_no_realizada')
                                <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-full dark:bg-red-900/30">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                            @elseif($notificacion->tipo === 'clase_reagendada')
                                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full dark:bg-green-900/30">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900/30">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $notificacion->titulo }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                {{ $notificacion->mensaje }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                {{ $notificacion->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if(!$notificacion->leida)
                            <div class="ml-2">
                                <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        No tienes notificaciones
                    </p>
                </div>
            @endforelse
        </div>

        @if($notificaciones->count() > 0)
            <!-- Footer -->
            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                <a 
                    href="{{ route('recuperacion-clases.index') }}"
                    class="block text-sm text-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    Ver todas las recuperaciones
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    // Actualizar notificaciones cada 2 minutos
    setInterval(() => {
        @this.call('cargarNotificaciones');
    }, 120000);
</script>
