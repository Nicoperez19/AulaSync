@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Navegación de paginación') }}" class="flex items-center justify-between">
        {{-- Mobile --}}
        <div class="flex justify-between flex-1 sm:hidden">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-sm text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                    {{ __('Anterior') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                    {{ __('Anterior') }}
                </a>
            @endif

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="ml-3 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                    {{ __('Siguiente') }}
                </a>
            @else
                <span class="ml-3 px-4 py-2 text-sm text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                    {{ __('Siguiente') }}
                </span>
            @endif
        </div>

        {{-- Escritorio --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    {{ __('Mostrando') }}
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    {{ __('a') }}
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    {{ __('de') }}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {{ __('resultados') }}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                    {{-- Anterior --}}
                    @if ($paginator->onFirstPage())
                        <span class="px-2 py-2 text-sm text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-2 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-l-md hover:text-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Páginas --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 cursor-default dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="z-10 px-4 py-2 text-sm font-semibold text-gray-900 bg-gray-200 border border-gray-300 cursor-default dark:bg-gray-700 dark:text-white dark:border-gray-500">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:text-white">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Siguiente --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-2 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-r-md hover:text-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="px-2 py-2 text-sm text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
