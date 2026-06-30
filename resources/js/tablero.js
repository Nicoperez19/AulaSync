document.addEventListener('DOMContentLoaded', function () {
    // Inicializar configuración del tablero desde el DOM
    const configEl = document.getElementById('tablero-config');
    let horariosModulos = {};
    if (configEl && configEl.dataset.horariosModulos) {
        horariosModulos = JSON.parse(configEl.dataset.horariosModulos);
    }

    class RelojModulo {
        constructor() {
            this.dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            this.init();
        }

        init() {
            this.actualizarFechaHora();
            this.actualizarModuloActual();

            // Actualizar cada segundo
            setInterval(() => {
                this.actualizarFechaHora();
                this.actualizarModuloActual();
            }, 1000);
        }

        obtenerDiaActual() {
            return this.dias[new Date().getDay()];
        }

        obtenerModuloActual() {
            const diaActual = this.obtenerDiaActual();
            const horaActual = new Date().toTimeString().slice(0, 8);

            // Si es fin de semana, no hay módulos
            if (diaActual === 'domingo' || diaActual === 'sabado') {
                return null;
            }

            const horariosDelDia = horariosModulos[diaActual];
            if (!horariosDelDia) {
                return null;
            }

            // Buscar en qué módulo estamos
            for (let numeroModulo in horariosDelDia) {
                const modulo = horariosDelDia[numeroModulo];
                if (horaActual >= modulo.inicio && horaActual < modulo.fin) {
                    return numeroModulo;
                }
            }

            return null;
        }

        actualizarFechaHora() {
            const ahora = new Date();
            const hora = ahora.toLocaleTimeString('es-CL', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });

            // Actualizar reloj principal (desktop)
            const horaActualElement = document.getElementById('hora-actual');
            if (horaActualElement) {
                horaActualElement.textContent = hora;
            }

            // Actualizar reloj móvil
            const horaActualMobileElement = document.getElementById('hora-actual-mobile');
            if (horaActualMobileElement) {
                horaActualMobileElement.textContent = hora;
            }
        }

        actualizarModuloActual() {
            const moduloActual = this.obtenerModuloActual();
            let textoModulo = 'En break';

            if (moduloActual) {
                textoModulo = `Módulo ${moduloActual}`;
            }

            // Actualizar módulo principal (desktop)
            const moduloActualElement = document.getElementById('modulo-actual');
            if (moduloActualElement) {
                moduloActualElement.textContent = textoModulo;
            }

            // Actualizar módulo móvil
            const moduloActualMobileElement = document.getElementById('modulo-actual-mobile');
            if (moduloActualMobileElement) {
                moduloActualMobileElement.textContent = textoModulo;
            }
        }
    }

    // Inicializar reloj
    new RelojModulo();

    // Cargar datos completos de manera diferida para mejorar el rendimiento inicial (Livewire dispatch)
    const despacharCargaDatos = () => {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('cargar-datos-completos');
        }
    };

    setTimeout(despacharCargaDatos, 1000);

    // Si Livewire se carga, despachar también
    document.addEventListener('livewire:load', function () {
        setTimeout(despacharCargaDatos, 500);
    });
    
    document.addEventListener('sedes:seleccionada', function () {
        setTimeout(() => location.reload(), 3000);
    });
});
