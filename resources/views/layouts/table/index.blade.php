<x-table-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-table"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Estado de Espacios</h2>
                    <p class="text-sm text-gray-500">Visualiza el estado de todos los espacios en el módulo actual</p>
                </div>
            </div>

        </div>
    </x-slot>

    <livewire:modulos-actuales-table />

    <div id="modal-reloj"
        class="fixed top-1 right-8 z-50 bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-5 flex flex-col items-center gap-1 min-w-[162px] text-white">
        <span class="px-3 font-mono text-lg font-bold text-white" id="hora-actual"></span>
        <span class="px-3 font-mono text-sm text-white " id="modulo-actual"></span>
    </div>
    <script>
        const horariosModulos = {
            lunes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            martes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            miercoles: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            jueves: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            viernes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            }
        };

        
        function obtenerDiaActual() {
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return dias[new Date().getDay()];
        }

        function obtenerModuloActual() {
            const diaActual = obtenerDiaActual();
            const horaActual = new Date().toTimeString().slice(0, 8); // Formato HH:MM:SS

            // Si es domingo o sábado, no hay módulos
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

            return null; // Fuera del horario de módulos
        }

        function actualizarFechaHora() {
            const ahora = new Date();
            const hora = ahora.toLocaleTimeString('es-CL', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            document.getElementById('hora-actual').textContent = hora;
        }

        function actualizarModuloActual() {
            const moduloActual = obtenerModuloActual();
            if (moduloActual) {
                document.getElementById('modulo-actual').textContent = `Módulo ${moduloActual}`;
            } else {
                document.getElementById('modulo-actual').textContent = 'Sin módulo';
            }
        }

        setInterval(actualizarFechaHora, 1000);
        setInterval(actualizarModuloActual, 1000); // Actualizar módulo cada segundo para mayor precisión
        actualizarFechaHora();
        actualizarModuloActual();
    </script>
</x-table-layout>
