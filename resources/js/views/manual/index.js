import '../../shared/page-config';
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
            const chapters = (window.ManualIndexConfig?.chapters ?? []);
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


Object.assign(window, { showChapter, clearSearch });
