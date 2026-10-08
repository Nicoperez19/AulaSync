<x-app-layout>
    @push('styles')
    <style>
        /* Estilos para el contenido markdown renderizado */
        .manual-content h1 { font-size: 1.875rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #7f1d1d; border-bottom: 2px solid #D2091E; padding-bottom: 0.5rem; }
        .manual-content h2 { font-size: 1.5rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem; color: #991b1b; }
        .manual-content h3 { font-size: 1.25rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #b91c1c; }
        .manual-content h4 { font-size: 1.1rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.5rem; color: #dc2626; }
        .manual-content p { margin-top: 0.5rem; margin-bottom: 1rem; line-height: 1.75; color: #374151; }
        .manual-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .manual-content ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
        .manual-content li { margin-bottom: 0.25rem; line-height: 1.75; color: #374151; }
        .manual-content strong { font-weight: 700; color: #111827; }
        .manual-content em { font-style: italic; }
        .manual-content code { background-color: #fef2f2; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875rem; font-family: monospace; color: #b91c1c; }
        .manual-content pre { background-color: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin-bottom: 1rem; }
        .manual-content pre code { background-color: transparent; color: inherit; padding: 0; }
        .manual-content blockquote { border-left: 4px solid #ef4444; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
        .manual-content hr { border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0; }
        .manual-content a { color: #D2091E; text-decoration: underline; }
        .manual-content img { max-width: 100%; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 1rem 0; border: 1px solid #fee2e2; }
        .manual-content table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .manual-content th, .manual-content td { border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; text-align: left; }
        .manual-content th { background-color: #fef2f2; font-weight: 600; color: #991b1b; }

        .chapter-section { display: none; }
        .chapter-section.active { display: block; }

        .toc-item { transition: all 0.15s ease; }
        .toc-item.active { background-color: #fee2e2; border-left: 3px solid #D2091E; }
        .toc-item:not(.active):hover { background-color: #fef2f2; }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-[#D2091E] p-2 text-white">
                    <i class="fa-solid fa-book-open text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold leading-tight text-gray-800">Manual de Usuario</h1>
                    <p class="text-sm text-gray-500">SIA | Sistema de Información de Aulas</p>
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
                <span class="text-xs text-gray-500">SIA | Sistema de Información de Aulas</span>
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

    <script>
        // ===== NAVEGACIÓN DE CAPÍTULOS =====
        function showChapter(slug) {
            // Ocultar todas las secciones
            document.querySelectorAll('.chapter-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.toc-item').forEach(el => el.classList.remove('active'));

            // Mostrar sección activa
            const section = document.getElementById('chapter-' + slug);
            if (section) {
                section.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            // Activar item del TOC
            const tocItem = document.querySelector(`.toc-item[data-slug="${slug}"]`);
            if (tocItem) {
                tocItem.classList.add('active');
                tocItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            // Actualizar URL hash sin recargar
            history.replaceState(null, null, '#' + slug);

            // Ocultar resultados de búsqueda si estaban visibles
            clearSearch();
        }

        // Cargar capítulo desde hash de URL al iniciar
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                showChapter(hash);
            }
        });

        // ===== BUSCADOR INTERNO =====
        const searchInput = document.getElementById('manual-search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                if (query.length < 2) {
                    clearSearch();
                    return;
                }
                performSearch(query);
            });
        }

        function performSearch(query) {
            const chapters = @json($chaptersSearch);
            const results = [];

            chapters.forEach(chapter => {
                const titleMatch = chapter.title.toLowerCase().includes(query);
                const contentLower = chapter.text.toLowerCase();
                const idx = contentLower.indexOf(query);

                if (titleMatch || idx !== -1) {
                    let excerpt = '';
                    if (idx !== -1) {
                        const start = Math.max(0, idx - 60);
                        const end = Math.min(chapter.text.length, idx + query.length + 80);
                        excerpt = (start > 0 ? '…' : '') + chapter.text.slice(start, end) + (end < chapter.text.length ? '…' : '');
                    }
                    results.push({ title: chapter.title, slug: chapter.slug, excerpt });
                }
            });

            const resultsContainer = document.getElementById('search-results');
            const resultsList = document.getElementById('search-results-list');

            if (results.length === 0) {
                resultsList.innerHTML = '<p class="text-sm text-yellow-700">Sin resultados para "<strong>' + query + '</strong>"</p>';
            } else {
                resultsList.innerHTML = results.map(r => `
                    <div class="cursor-pointer hover:bg-yellow-100 p-2 rounded" onclick="showChapter('${r.slug}')">
                        <p class="text-sm font-semibold text-yellow-900">${r.title}</p>
                        ${r.excerpt ? `<p class="text-xs text-yellow-700 mt-0.5">${r.excerpt}</p>` : ''}
                    </div>
                `).join('');
            }

            resultsContainer.classList.remove('hidden');
        }

        function clearSearch() {
            const resultsContainer = document.getElementById('search-results');
            if (resultsContainer) resultsContainer.classList.add('hidden');
        }

    </script>
</x-app-layout>
