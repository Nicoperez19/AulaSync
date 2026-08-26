let activeTabOcupacion = 'todos';
let activeMainTab = 'ocupacion';
let statusClasesChartInstance = null;
window.carouselAutoplayInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    const configEl = document.getElementById('dashboard-config');
    if (configEl) {
        window.DashboardConfig = {
            horariosActualRoute: configEl.dataset.horariosActualRoute,
            ocupacionDatosRoute: configEl.dataset.ocupacionDatosRoute,
            statusClasesRoute: configEl.dataset.statusClasesRoute,
            horariosModulos: JSON.parse(configEl.dataset.horariosModulos)
        };
    }

    if (window.DashboardConfig) {
        window.horariosModulos = window.DashboardConfig.horariosModulos;
    }

    cargarHorarioActual();
    cargarOcupacionGrid(activeTabOcupacion);
    cargarStatusClases('semana');

    setInterval(function() {
        cargarHorarioActual();
        cargarOcupacionGrid(activeTabOcupacion, true);
    }, 3000);

    actualizarModalReloj();
    setInterval(actualizarModalReloj, 1000);
});

function switchMainDashboardTab(tabName) {
    if (activeMainTab === tabName) return;

    document.querySelectorAll('.main-dashboard-tab-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-xs');
        btn.classList.add('bg-slate-50', 'text-slate-600', 'hover:bg-slate-100');
    });

    const activeBtn = document.getElementById(`main-tab-btn-${tabName}`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-slate-50', 'text-slate-600', 'hover:bg-slate-100');
        activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-xs');
    }

    document.querySelectorAll('.main-dashboard-tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    const targetContent = document.getElementById(`main-tab-${tabName}-content`);
    if (targetContent) {
        targetContent.classList.remove('hidden');
    }

    activeMainTab = tabName;

    if (tabName === 'status-clases') {
        cargarStatusClases('semana');
    }
}

function startCarouselAutoplay() {
    stopCarouselAutoplay();
    const container = document.getElementById('carousel-container');
    if (!container) return;
    
    const total = parseInt(container.getAttribute('data-total-slides') || '1');
    if (total <= 1) return;
    
    window.carouselAutoplayInterval = setInterval(nextSlide, 7500);
}

function stopCarouselAutoplay() {
    if (window.carouselAutoplayInterval) {
        clearInterval(window.carouselAutoplayInterval);
        window.carouselAutoplayInterval = null;
    }
}

function prevSlide() {
    const container = document.getElementById('carousel-container');
    if (!container) return;
    
    let current = parseInt(container.getAttribute('data-current-slide') || '0');
    const total = parseInt(container.getAttribute('data-total-slides') || '1');
    
    current = current > 0 ? current - 1 : total - 1;
    
    container.setAttribute('data-current-slide', current);
    updateCarouselState(container, current, total);
    startCarouselAutoplay();
}

function nextSlide() {
    const container = document.getElementById('carousel-container');
    if (!container) return;
    
    let current = parseInt(container.getAttribute('data-current-slide') || '0');
    const total = parseInt(container.getAttribute('data-total-slides') || '1');
    
    current = current < total - 1 ? current + 1 : 0;
    
    container.setAttribute('data-current-slide', current);
    updateCarouselState(container, current, total);
    startCarouselAutoplay();
}

function goToSlide(index) {
    const container = document.getElementById('carousel-container');
    if (!container) return;
    
    const total = parseInt(container.getAttribute('data-total-slides') || '1');
    
    container.setAttribute('data-current-slide', index);
    updateCarouselState(container, index, total);
    startCarouselAutoplay();
}

function updateCarouselState(container, current, total) {
    const slides = document.getElementById('carousel-slides');
    if (slides) {
        slides.style.transform = `translateX(-${current * 100}%)`;
    }
    
    for (let i = 0; i < total; i++) {
        const ind = document.getElementById(`indicator-${i}`);
        if (ind) {
            if (i === current) {
                ind.className = 'h-2 rounded-full transition-all duration-200 bg-blue-600 w-6';
            } else {
                ind.className = 'h-2 rounded-full transition-all duration-200 bg-gray-300 w-2 hover:bg-gray-400';
            }
        }
    }
}

function obtenerDiaActual() {
    const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    return dias[new Date().getDay()];
}

function obtenerModuloActual(hora = null) {
    const diaActual = obtenerDiaActual();
    const horaAhora = hora || new Date().toTimeString().slice(0, 8);

    if (!window.horariosModulos || !window.horariosModulos[diaActual]) {
        return null;
    }

    for (const [num, horario] of Object.entries(window.horariosModulos[diaActual])) {
        if (horaAhora >= horario.inicio && horaAhora <= horario.fin) {
            return parseInt(num);
        }
    }
    return null;
}

function actualizarModalReloj() {
    const ahora = new Date();
    const hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    const clockEl = document.getElementById('modal-hora-actual');
    if (clockEl) {
        clockEl.textContent = hora;
    }
    
    let modulo = '-';
    const moduloNum = obtenerModuloActual();
    if (moduloNum) {
        modulo = moduloNum;
    }
    
    const moduloEl = document.getElementById('modal-modulo-actual');
    if (moduloEl) {
        moduloEl.textContent = 'Módulo actual: ' + modulo;
    }
}

function cargarHorarioActual() {
    const container = document.getElementById('horarios-actual-container');
    const syncIcon = document.getElementById('btn-sync-icon');
    
    if (syncIcon) {
        syncIcon.classList.add('animate-spin');
    }

    const route = window.DashboardConfig ? window.DashboardConfig.horariosActualRoute : '/dashboard/horarios-actual';

    fetch(route)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar la información');
            }
            return response.text();
        })
        .then(html => {
            const existingContainer = document.getElementById('carousel-container');
            const currentSlide = existingContainer ? parseInt(existingContainer.getAttribute('data-current-slide') || '0') : 0;

            container.innerHTML = html;

            const newContainer = document.getElementById('carousel-container');
            if (newContainer) {
                const total = parseInt(newContainer.getAttribute('data-total-slides') || '1');
                const targetSlide = currentSlide < total ? currentSlide : 0;
                newContainer.setAttribute('data-current-slide', targetSlide);
                updateCarouselState(newContainer, targetSlide, total);
            }
            startCarouselAutoplay();
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p class="font-medium">No se pudieron cargar los horarios del módulo actual.</p>
                    <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                </div>
            `;
        })
        .finally(() => {
            if (syncIcon) {
                setTimeout(() => {
                    syncIcon.classList.remove('animate-spin');
                }, 500);
            }
        });
}

function cambiarTabOcupacion(tipo) {
    if (activeTabOcupacion === tipo) return;
    
    // Resetear todos los botones al estado inactivo
    document.querySelectorAll('.tab-ocupacion-btn').forEach(btn => {
        btn.classList.remove('bg-[#D2091E]', 'text-white', 'shadow-sm');
        btn.classList.add('text-gray-600', 'hover:bg-white', 'hover:text-[#D2091E]', 'hover:shadow-sm');
    });
    
    // Activar el botón seleccionado
    const selectedBtn = document.getElementById(`tab-ocupacion-${tipo}`);
    if (selectedBtn) {
        selectedBtn.classList.remove('text-gray-600', 'hover:bg-white', 'hover:text-[#D2091E]', 'hover:shadow-sm');
        selectedBtn.classList.add('bg-[#D2091E]', 'text-white', 'shadow-sm');
    }
    
    activeTabOcupacion = tipo;
    cargarOcupacionGrid(tipo, false);
}

function cargarOcupacionGrid(tipo, silencioso = false) {
    const container = document.getElementById('ocupacion-grid-container');
    if (!container) return;
    
    if (!silencioso) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-sm">Cargando datos...</p>
            </div>
        `;
    }
    
    const baseRoute = window.DashboardConfig ? window.DashboardConfig.ocupacionDatosRoute : '/dashboard/ocupacion-datos';
    const url = `${baseRoute}?tipo=${tipo}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar ocupación');
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            if (!silencioso) {
                container.innerHTML = `
                    <div class="text-center py-12 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p class="font-medium">No se pudieron cargar los datos de ocupación.</p>
                        <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                    </div>
                `;
            }
        });
}

function cargarStatusClases(rango = 'semana', fechaInicio = '', fechaFin = '') {
    const container = document.getElementById('status-clases-container');
    if (!container) return;

    let route = window.DashboardConfig ? window.DashboardConfig.statusClasesRoute : '/dashboard/status-clases';
    let url = `${route}?rango=${rango}`;
    if (fechaInicio && fechaFin) {
        url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            setTimeout(initStatusClasesChart, 50);
        })
        .catch(err => {
            console.error('Error cargando status clases:', err);
            container.innerHTML = `<div class="text-center py-8 text-rose-500">Error al cargar el status de clases.</div>`;
        });
}

function filtrarStatusClases(rango) {
    cargarStatusClases(rango);
}

function filtrarStatusClasesPersonalizado() {
    const inicio = document.getElementById('status-fecha-inicio')?.value;
    const fin = document.getElementById('status-fecha-fin')?.value;
    if (inicio && fin) {
        cargarStatusClases('personalizado', inicio, fin);
    }
}

function initStatusClasesChart() {
    const canvas = document.getElementById('chart-status-clases-canvas');
    if (!canvas || !window.Chart) return;

    const realizadas = parseInt(canvas.dataset.realizadas || '0');
    const recuperadas = parseInt(canvas.dataset.recuperadas || '0');
    const noRegistradas = parseInt(canvas.dataset.noRegistradas || '0');
    const pctImpartidas = parseFloat(canvas.dataset.pctImpartidas || '0');
    const pctNoRegistradas = parseFloat(canvas.dataset.pctNoRegistradas || '0');
    const totalClases = parseInt(canvas.dataset.totalClases || '0');

    const impartidasTotal = realizadas + recuperadas;

    const centerValor = document.getElementById('center-label-valor');
    const centerSub = document.getElementById('center-label-sub');

    if (statusClasesChartInstance) {
        statusClasesChartInstance.destroy();
    }

    const ctx = canvas.getContext('2d');

    const chartData = totalClases === 0 ? [1] : [impartidasTotal, noRegistradas];
    const chartColors = totalClases === 0 ? ['#e2e8f0'] : ['#10b981', '#ef4444'];
    const chartHoverColors = totalClases === 0 ? ['#cbd5e1'] : ['#059669', '#dc2626'];

    statusClasesChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: totalClases === 0 ? ['Sin Clases en Período'] : ['Clases Impartidas / Efectivas', 'No Registradas / No Realizadas'],
            datasets: [{
                data: chartData,
                backgroundColor: chartColors,
                hoverBackgroundColor: chartHoverColors,
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            onHover: (event, activeElements) => {
                if (!centerValor || !centerSub) return;

                if (activeElements && activeElements.length > 0 && totalClases > 0) {
                    const idx = activeElements[0].index;
                    if (idx === 0) {
                        centerValor.textContent = `${impartidasTotal}`;
                        centerValor.className = 'text-3xl font-black text-emerald-600 tracking-tight leading-none';
                        centerSub.textContent = `Impartidas (${pctImpartidas}%)`;
                        centerSub.className = 'text-[11px] font-extrabold text-emerald-700 uppercase tracking-wide mt-1';
                    } else {
                        centerValor.textContent = `${noRegistradas}`;
                        centerValor.className = 'text-3xl font-black text-rose-600 tracking-tight leading-none';
                        centerSub.textContent = `Sin Registro (${pctNoRegistradas}%)`;
                        centerSub.className = 'text-[11px] font-extrabold text-rose-700 uppercase tracking-wide mt-1';
                    }
                } else {
                    centerValor.textContent = `${pctImpartidas}%`;
                    centerValor.className = 'text-3xl font-black text-slate-800 tracking-tight leading-none';
                    centerSub.textContent = 'Cumplimiento';
                    centerSub.className = 'text-[11px] font-extrabold text-emerald-600 uppercase tracking-wide mt-1';
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: totalClases > 0,
                    position: 'nearest',
                    yAlign: 'bottom',
                    caretPadding: 16,
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            if (index === 0) {
                                return [
                                    ` Realizadas Normales: ${realizadas}`,
                                    ` Recuperadas: ${recuperadas}`,
                                    ` Total Impartidas: ${impartidasTotal} (${pctImpartidas}%)`
                                ];
                            } else {
                                return [
                                    ` Sin Registro / Ausentes: ${noRegistradas} (${pctNoRegistradas}%)`
                                ];
                            }
                        }
                    }
                }
            }
        }
    });
}

window.prevSlide = prevSlide;
window.nextSlide = nextSlide;
window.goToSlide = goToSlide;
window.cambiarTabOcupacion = cambiarTabOcupacion;
window.switchMainDashboardTab = switchMainDashboardTab;
window.filtrarStatusClases = filtrarStatusClases;
window.filtrarStatusClasesPersonalizado = filtrarStatusClasesPersonalizado;
window.cargarHorarioActual = cargarHorarioActual;
window.cargarOcupacionGrid = cargarOcupacionGrid;
window.cargarStatusClases = cargarStatusClases;
