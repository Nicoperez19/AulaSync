let activeTabOcupacion = 'todos';
window.carouselAutoplayInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar configuración del dashboard desde el DOM
    const configEl = document.getElementById('dashboard-config');
    if (configEl) {
        window.DashboardConfig = {
            horariosActualRoute: configEl.dataset.horariosActualRoute,
            ocupacionDatosRoute: configEl.dataset.ocupacionDatosRoute,
            horariosModulos: JSON.parse(configEl.dataset.horariosModulos)
        };
    }

    // Inicializar horarios de módulos desde la configuración
    if (window.DashboardConfig) {
        window.horariosModulos = window.DashboardConfig.horariosModulos;
    }

    // Cargar datos por primera vez
    cargarHorarioActual();
    cargarOcupacionGrid(activeTabOcupacion);
    
    // Función para actualizar todos los datos de forma sincronizada
    function actualizarTodoElDashboard() {
        cargarHorarioActual();
        cargarOcupacionGrid(activeTabOcupacion, true);
    }
    
    // Actualizar automáticamente todo junto cada 3 segundos en segundo plano
    setInterval(actualizarTodoElDashboard, 3000);

    // Reloj digital y módulo actual
    actualizarModalReloj();
    setInterval(actualizarModalReloj, 1000);
});

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
    
    // Actualizar pastillas indicadoras de paginación
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
            container.innerHTML = html;
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
    
    // Cambiar clases activas/inactivas de los botones de pestañas
    document.querySelectorAll('.tab-ocupacion-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
        btn.classList.add('bg-gray-50', 'text-gray-600', 'hover:bg-gray-100');
    });
    
    const selectedBtn = document.getElementById(`tab-ocupacion-${tipo}`);
    if (selectedBtn) {
        selectedBtn.classList.remove('bg-gray-50', 'text-gray-600', 'hover:bg-gray-100');
        selectedBtn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
    }
    
    activeTabOcupacion = tipo;
    cargarOcupacionGrid(tipo, false);
}

function cargarOcupacionGrid(tipo, silencioso = false) {
    const container = document.getElementById('ocupacion-grid-container');
    if (!container) return;
    
    // Mostrar spinner de carga solo si no es actualización silenciosa
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

// Exponer las funciones que se invocan desde eventos HTML onclick en Blade
window.prevSlide = prevSlide;
window.nextSlide = nextSlide;
window.goToSlide = goToSlide;
window.cambiarTabOcupacion = cambiarTabOcupacion;
window.cargarHorarioActual = cargarHorarioActual;
