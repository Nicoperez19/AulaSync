<x-table-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <!-- Botón Volver -->
                <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                    </svg>
                    Volver
                </a>

                <!-- Logo y título -->
                <div class="p-3 rounded-xl bg-light-cloud-blue shadow-lg">
                    <i class="text-2xl text-white fa-solid fa-table"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Estado de Espacios</h2>
                    <p class="text-sm text-gray-600">Visualiza el estado de todos los espacios en el módulo actual</p>
                </div>
            </div>

            <!-- Información del módulo actual -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hora Actual</div>
                        <div class="text-lg font-mono font-bold text-gray-900" id="hora-actual">--:--:--</div>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Módulo</div>
                        <div class="text-lg font-bold text-light-cloud-blue" id="modulo-actual">--</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Componente Livewire principal -->
    <livewire:modulos-actuales-table />

    <!-- Reloj flotante para pantallas pequeñas -->
    <div id="reloj-flotante" 
         class="fixed top-4 right-4 z-50 md:hidden bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-4 py-3 flex flex-col items-center gap-1 min-w-[140px] text-white">
        <span class="px-2 font-mono text-lg font-bold text-white" id="hora-actual-mobile"></span>
        <span class="px-2 font-mono text-sm text-white" id="modulo-actual-mobile"></span>
    </div>

    <!-- Scripts mejorados -->
    <script>
        // Configuración de horarios de módulos
        const HORARIOS_MODULOS = {
            lunes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            martes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            miercoles: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            jueves: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            viernes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            }
        };

        // Clase para manejar el reloj y módulos
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

                const horariosDelDia = HORARIOS_MODULOS[diaActual];
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
                let textoModulo = 'Sin módulo';

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

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            new RelojModulo();
            
            // Cargar datos completos de manera diferida para mejorar el rendimiento inicial
            setTimeout(() => {
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('cargar-datos-completos');
                }
            }, 1000);
        });

        // Inicializar también cuando Livewire se cargue
        document.addEventListener('livewire:load', function() {
            new RelojModulo();
            
            // Cargar datos completos después de que Livewire esté listo
            setTimeout(() => {
                Livewire.dispatch('cargar-datos-completos');
            }, 500);
        });
    </script>
</x-table-layout>