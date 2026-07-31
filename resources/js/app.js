import collapse from '@alpinejs/collapse'
import focus from '@alpinejs/focus'
import mask from '@alpinejs/mask'
import PerfectScrollbar from 'perfect-scrollbar'
import 'perfect-scrollbar/css/perfect-scrollbar.css'

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine

    // Configuración de Alpine.js
    Alpine.plugin(collapse)
    Alpine.plugin(focus)
    Alpine.plugin(mask)

    // Estado principal de la aplicación y Sidebar
    Alpine.data('mainState', () => ({
        isDarkMode: localStorage.getItem('dark') === 'true' ||
                    (!localStorage.getItem('dark') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        isSidebarOpen: false,
        isSidebarHovered: false,
        scrollingDown: false,
        scrollingUp: false,
        lastScrollTop: 0,

        init() {
            this.handleWindowResize()
            window.addEventListener('scroll', this.handleScroll.bind(this))
            window.addEventListener('resize', this.handleWindowResize.bind(this))
        },

        toggleTheme() {
            this.isDarkMode = !this.isDarkMode
            localStorage.setItem('dark', this.isDarkMode)
        },

        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },

        handleSidebarHover(value) {
            if (window.innerWidth < 1024) return
            this.isSidebarHovered = value
        },

        handleWindowResize() {
            if (window.innerWidth < 1024) {
                this.isSidebarOpen = false
            }
        },

        handleScroll() {
            const st = window.pageYOffset || document.documentElement.scrollTop
            this.scrollingDown = st > this.lastScrollTop
            this.scrollingUp = st < this.lastScrollTop
            if (st === 0) {
                this.scrollingDown = false
                this.scrollingUp = false
            }
            this.lastScrollTop = st <= 0 ? 0 : st
        }
    }))
})

// Funciones Globales para Alertas SweetAlert
window.mostrarSweetAlertNoMapas = function(event) {
    if (event) event.preventDefault();
    if (window.Swal) {
        window.Swal.fire({
            title: 'No hay mapas disponibles',
            html: `
                <div class="text-center">
                    <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                    <p class="text-sm text-gray-600">Por favor póngase en contacto con el administrador del sistema.</p>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3B82F6'
        });
    }
}

window.mostrarSweetAlertNoProfesores = function(event) {
    if (event) event.preventDefault();
    if (window.Swal) {
        window.Swal.fire({
            title: 'No hay profesores disponibles',
            html: `
                <div class="text-center">
                    <p class="mb-4">No se han cargado datos de profesores en el sistema.</p>
                    <p class="text-sm text-gray-600">Contacte al administrador para cargar los datos de profesores.</p>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3B82F6'
        });
    }
}

window.mostrarSweetAlertNoEspacios = function(event) {
    if (event) event.preventDefault();
    if (window.Swal) {
        window.Swal.fire({
            title: 'No hay espacios disponibles',
            html: `
                <div class="text-center">
                    <p class="mb-4">No se han encontrado espacios registrados en el sistema.</p>
                    <p class="text-sm text-gray-600">Contacte al administrador para registrar los espacios.</p>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3B82F6'
        });
    }
}

// Interceptor global para expiración de sesión en peticiones AJAX
document.addEventListener('DOMContentLoaded', () => {
    if (!localStorage.getItem('intended_url')) {
        localStorage.setItem('intended_url', window.location.href);
    }

    // Intercept Fetch requests
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
        return originalFetch.apply(this, args).then(response => {
            if (response.status === 401) {
                return response.clone().json().then(data => {
                    if (data.error === 'session_expired') {
                        localStorage.setItem('intended_url', window.location.href);
                        if (window.Swal) {
                            window.Swal.fire({
                                title: 'Sesión Expirada',
                                text: data.message || 'Tu sesión ha expirado por inactividad.',
                                icon: 'warning',
                                confirmButtonText: 'Ir al Login',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = data.redirect || '/login';
                            });
                        } else {
                            window.location.href = data.redirect || '/login';
                        }
                        return Promise.reject(new Error('Session expired'));
                    }
                    return response;
                }).catch(() => {
                    if (response.headers.get('location') && response.headers.get('location').includes('login')) {
                        localStorage.setItem('intended_url', window.location.href);
                        if (window.Swal) {
                            window.Swal.fire({
                                title: 'Sesión Expirada',
                                text: 'Tu sesión ha expirado por inactividad.',
                                icon: 'warning',
                                confirmButtonText: 'Ir al Login',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = '/login';
                            });
                        } else {
                            window.location.href = '/login';
                        }
                        return Promise.reject(new Error('Session expired'));
                    }
                    return response;
                });
            }
            return response;
        });
    };

    // Intercept XMLHttpRequest
    const originalXHROpen = XMLHttpRequest.prototype.open;
    const originalXHRSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
        this._url = url;
        return originalXHROpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function (data) {
        const xhr = this;
        const originalOnReadyStateChange = xhr.onreadystatechange;

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 401) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error === 'session_expired') {
                        localStorage.setItem('intended_url', window.location.href);
                        if (window.Swal) {
                            window.Swal.fire({
                                title: 'Sesión Expirada',
                                text: response.message || 'Tu sesión ha expirado por inactividad.',
                                icon: 'warning',
                                confirmButtonText: 'Ir al Login',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = response.redirect || '/login';
                            });
                        } else {
                            window.location.href = response.redirect || '/login';
                        }
                        return;
                    }
                } catch (e) {
                    if (xhr.responseText.includes('login') || xhr.getResponseHeader('location')?.includes('login')) {
                        localStorage.setItem('intended_url', window.location.href);
                        if (window.Swal) {
                            window.Swal.fire({
                                title: 'Sesión Expirada',
                                text: 'Tu sesión ha expirado por inactividad.',
                                icon: 'warning',
                                confirmButtonText: 'Ir al Login',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = '/login';
                            });
                        } else {
                            window.location.href = '/login';
                        }
                        return;
                    }
                }
            }

            if (originalOnReadyStateChange) {
                originalOnReadyStateChange.apply(xhr, arguments);
            }
        };

        return originalXHRSend.apply(this, arguments);
    };

    // Perfect Scrollbar
    const containers = document.querySelectorAll('.ps');
    containers.forEach(container => {
        if (typeof PerfectScrollbar !== 'undefined') {
            new PerfectScrollbar(container, {
                suppressScrollX: true,
                wheelPropagation: false
            });
        }
    });

    // Lazy load images
    const images = document.querySelectorAll('img[loading="lazy"]');
    if ('loading' in HTMLImageElement.prototype) {
        images.forEach(img => {
            if (img.dataset.src) img.src = img.dataset.src;
        });
    }
});
