<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manual de Usuario — {{ config('app.name', 'SIA | Sistema de Información de Aulas') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Estilos para el contenido markdown renderizado */
        .manual-content h1 { font-size: 1.875rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #7f1d1d; border-bottom: 2px solid #ef4444; padding-bottom: 0.5rem; }
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
        .manual-content a { color: #dc2626; text-decoration: underline; }
        .manual-content img { max-width: 100%; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 1rem 0; border: 1px solid #fee2e2; }
        .manual-content table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .manual-content th, .manual-content td { border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; text-align: left; }
        .manual-content th { background-color: #fef2f2; font-weight: 600; color: #991b1b; }

        .chapter-section { display: none; }
        .chapter-section.active { display: block; }

        .toc-item { transition: all 0.15s ease; }
        .toc-item.active { background-color: #fee2e2; border-left: 3px solid #dc2626; }
        .toc-item:not(.active):hover { background-color: #fef2f2; }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100">

    <!-- Barra superior -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-red-800 shadow-md flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
            <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}"
               class="flex items-center gap-2 text-white hover:text-red-200 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
                <span class="text-sm font-medium hidden sm:inline">Volver</span>
            </a>
            <div class="w-px h-5 bg-white/30 hidden sm:block"></div>
            <div class="flex items-center gap-2">
                <i class="fas fa-book-open text-red-300 text-lg"></i>
                <span class="text-white font-semibold text-lg">Manual de Usuario</span>
                <span class="text-red-300 text-xs ml-1 hidden sm:inline">— SIA | Sistema de Información de Aulas</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- Buscador interno -->
            <div class="relative hidden md:flex items-center">
                <i class="fas fa-search absolute left-3 text-red-300 text-sm"></i>
                <input
                    type="text"
                    id="manual-search"
                    placeholder="Buscar en el manual..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-md bg-white/10 text-white placeholder-red-200 border border-white/20 focus:outline-none focus:bg-white/20 focus:border-white/40 w-56"
                >
            </div>
            <span id="manual-version" class="text-red-200 text-xs hidden sm:inline">
                Actualizado: {{ date('d/m/Y', filemtime(base_path('docs/MANUAL.md'))) }}
            </span>
        </div>
    </header>

    <!-- Layout principal -->
    <div class="flex min-h-screen pt-14">

        <!-- Sidebar / TOC -->
        <aside
            id="manual-sidebar"
            class="fixed left-0 top-14 bottom-0 w-72 bg-white border-r border-gray-200 flex flex-col shadow-sm z-40 overflow-hidden transition-transform duration-300"
        >
            <!-- Encabezado sidebar -->
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Contenidos</span>
                <span class="text-xs text-gray-400">{{ count($chapters) }} capítulos</span>
            </div>

            <!-- Lista de capítulos -->
            <nav class="flex-1 overflow-y-auto py-2" id="toc-nav">
                @foreach ($chapters as $index => $chapter)
                    <button
                        onclick="showChapter('{{ $chapter['slug'] }}')"
                        data-slug="{{ $chapter['slug'] }}"
                        class="toc-item w-full text-left px-4 py-2.5 flex items-start gap-2.5 border-l-3 border-transparent {{ $index === 0 ? 'active' : '' }}"
                    >
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm text-gray-700 font-medium leading-snug">{{ $chapter['title'] }}</span>
                    </button>
                @endforeach
            </nav>

            <!-- Footer sidebar -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 text-xs text-gray-500 hover:text-red-600 transition-colors">
                    <i class="fas fa-home"></i>
                    <span>Ir al inicio</span>
                </a>
            </div>
        </aside>

        <!-- Botón toggle sidebar (móvil) -->
        <button
            id="sidebar-toggle"
            onclick="document.getElementById('manual-sidebar').classList.toggle('-translate-x-full')"
            class="fixed bottom-4 left-4 z-50 md:hidden bg-red-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg"
        >
            <i class="fas fa-list"></i>
        </button>

        <!-- Contenido principal -->
        <main class="ml-72 flex-1 px-6 py-8 max-w-none" id="manual-main">
            <div class="max-w-4xl mx-auto">

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

            </div>
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

        // ===== RESPONSIVE: ocultar sidebar en móvil al seleccionar capítulo =====
        document.querySelectorAll('.toc-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    document.getElementById('manual-sidebar').classList.add('-translate-x-full');
                }
            });
        });

        // Ajustar margen del main en móvil
        function handleResize() {
            const main = document.getElementById('manual-main');
            const sidebar = document.getElementById('manual-sidebar');
            if (window.innerWidth < 768) {
                main.style.marginLeft = '0';
                sidebar.classList.add('-translate-x-full');
            } else {
                main.style.marginLeft = '18rem';
                sidebar.classList.remove('-translate-x-full');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
</body>
</html>
