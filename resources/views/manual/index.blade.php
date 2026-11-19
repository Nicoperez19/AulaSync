<x-app-layout>
    @push('styles')
    @vite(['resources/css/views/manual/index.css'])
    @endpush

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-[#D2091E] p-2 text-white">
                    <i class="fa-solid fa-book-open text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold leading-tight text-gray-800">Manual de Usuario</h1>
                    <p class="text-sm text-gray-500">SIA (Sistema de Información de Aulas)</p>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between lg:justify-end">
                <label for="manual-search" class="sr-only">Buscar en el manual</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input
                        type="search"
                        id="manual-search"
                        placeholder="Buscar en el manual..."
                        class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-4 text-sm shadow-sm focus:border-[#D2091E] focus:ring-[#D2091E] sm:w-64"
                    >
                </div>
                <span id="manual-version" class="text-xs text-gray-500 sm:whitespace-nowrap">
                Actualizado: {{ date('d/m/Y', filemtime(base_path('docs/MANUAL.md'))) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 pb-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside id="manual-sidebar" class="self-start overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:sticky lg:top-4 lg:max-h-[calc(100vh-6rem)]">
            <!-- Encabezado sidebar -->
            <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Contenidos</span>
                <span class="text-xs text-gray-400">{{ count($chapters) }} capítulos</span>
            </div>

            <!-- Lista de capítulos -->
            <nav class="max-h-64 overflow-y-auto py-2 lg:max-h-[calc(100vh-10rem)]" id="toc-nav" aria-label="Capítulos del manual">
                @foreach ($chapters as $index => $chapter)
                    <button
                        onclick="showChapter('{{ $chapter['slug'] }}')"
                        data-slug="{{ $chapter['slug'] }}"
                        class="toc-item w-full border-l-[3px] border-transparent px-4 py-2.5 text-left flex items-start gap-2.5 {{ $index === 0 ? 'active' : '' }}"
                    >
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm text-gray-700 font-medium leading-snug">{{ $chapter['title'] }}</span>
                    </button>
                @endforeach
            </nav>

            <!-- Footer sidebar -->
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                <span class="text-xs text-gray-500">SIA (Sistema de Información de Aulas)</span>
            </div>
        </aside>

        <main class="min-w-0" id="manual-main">

                <!-- Panel de búsqueda (resultados) -->
                <div id="search-results" class="hidden mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800 mb-2">Resultados de búsqueda:</p>
                    <div id="search-results-list" class="space-y-2"></div>
                    <button onclick="clearSearch()" class="mt-2 text-xs text-yellow-700 underline">Limpiar búsqueda</button>
                </div>

                @foreach ($chapters as $index => $chapter)
                    <section
                        id="chapter-{{ $chapter['slug'] }}"
                        class="chapter-section {{ $index === 0 ? 'active' : '' }}"
                    >
                        <!-- Header de capítulo -->
                        <div class="mb-6 pb-4 border-b-2 border-red-100">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-600 text-white text-sm font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-xs font-medium text-red-500 uppercase tracking-wide">Capítulo {{ $index + 1 }}</span>
                            </div>
                        </div>

                        <!-- Contenido renderizado del capítulo -->
                        <div class="manual-content bg-white rounded-xl shadow-sm p-6 sm:p-8">
                            {!! $chapter['html'] !!}
                        </div>

                        <!-- Navegación entre capítulos -->
                        <div class="flex items-center justify-between mt-6 pt-4">
                            @if ($index > 0)
                                <button
                                    onclick="showChapter('{{ $chapters[$index - 1]['slug'] }}')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 bg-white rounded-lg shadow-sm hover:bg-gray-50 border border-gray-200 transition-colors"
                                >
                                    <i class="fas fa-arrow-left text-xs"></i>
                                    <span class="max-w-[160px] truncate">{{ $chapters[$index - 1]['title'] }}</span>
                                </button>
                            @else
                                <div></div>
                            @endif

                            @if ($index < count($chapters) - 1)
                                <button
                                    onclick="showChapter('{{ $chapters[$index + 1]['slug'] }}')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 bg-white rounded-lg shadow-sm hover:bg-gray-50 border border-gray-200 transition-colors"
                                >
                                    <span class="max-w-[160px] truncate">{{ $chapters[$index + 1]['title'] }}</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </button>
                            @else
                                <div></div>
                            @endif
                        </div>
                    </section>
                @endforeach

        </main>
    </div>

<div data-page-config="ManualIndexConfig" data-config="{{ json_encode(['chapters' => $chaptersSearch]) }}" class="hidden"></div>
@vite(['resources/js/views/manual/index.js'])
@endpush
</x-app-layout>
