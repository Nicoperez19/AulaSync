@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-gray-600">
            Mostrando
            <span class="font-semibold text-gray-800">{{ $paginator->firstItem() }}</span>
            a
            <span class="font-semibold text-gray-800">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-semibold text-gray-800">{{ $paginator->total() }}</span>
            resultados
        </p>

        <ul class="inline-flex -space-x-px rounded-lg shadow-sm">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true">
                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 cursor-default rounded-l-lg">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"
                        class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-l-lg hover:bg-red-50 hover:text-[#950101] focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#D12420]/30">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                </li>
            @endif

            {{-- Números --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li aria-disabled="true">
                        <span class="inline-flex items-center px-4 py-2 text-sm text-gray-500 bg-white border border-gray-300 cursor-default">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="relative z-10 inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-[#D12420] border border-[#D12420] cursor-default shadow-sm">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-red-50 hover:text-[#950101] focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#D12420]/30">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente"
                        class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-r-lg hover:bg-red-50 hover:text-[#950101] focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#D12420]/30">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </li>
            @else
                <li aria-disabled="true">
                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 cursor-default rounded-r-lg">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
