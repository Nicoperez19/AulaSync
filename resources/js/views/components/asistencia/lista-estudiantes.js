function asistenciaListHandler() {
    return {
        filtro: '',
        mostrarModalNuevo: false,
        nuevoRun: '',
        nuevoNombre: '',
        estudiantes: (window.ListaEstudiantesConfig?.estudiantes ?? []),
        nuevosEstudiantes: [],
        ultimoEscaneado: null,
        estudianteDestacado: null,

        init() {
            // Exponer método global para permitir marcado en tiempo real desde WebSocket, Polling o Lector QR
            window.asistenciaMarcarRun = (run, nombre, metodo, hora) => {
                return this.marcarPorRun(run, nombre, metodo, hora);
            };
        },

        normalizarRun(run) {
            return String(run || '').toUpperCase().replace(/[^0-9K]/g, '');
        },

        coincideRun(runA, runB) {
            const a = this.normalizarRun(runA);
            const b = this.normalizarRun(runB);
            if (!a || !b) return false;
            if (a === b) return true;

            // Comparación tolerando Dígito Verificador (ej: cuerpo de 8 dígitos vs 9 con DV)
            if (a.length === b.length + 1 && a.startsWith(b)) return true;
            if (b.length === a.length + 1 && b.startsWith(a)) return true;
            if (a.length === 8 && b.length === 7 && a.startsWith(b)) return true;
            if (b.length === 8 && a.length === 7 && b.startsWith(a)) return true;

            return false;
        },

        marcarPorRun(runBuscado, nombreOpcional = null, metodo = 'QR', horaOpcional = null) {
            if (!runBuscado) return false;
            const runNorm = this.normalizarRun(runBuscado);
            const horaMostrar = horaOpcional || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            // 1. Buscar en lista oficial de estudiantes
            let est = this.estudiantes.find(e => this.coincideRun(e.run, runNorm));
            if (est) {
                const estabaPresente = Boolean(est.presente);
                est.presente = true;
                if (!estabaPresente) {
                    this.destacarEstudiante(est.id || est.run);
                    this.ultimoEscaneado = {
                        nombre: est.nombre,
                        run: est.run,
                        hora: horaMostrar
                    };
                }
                return !estabaPresente;
            }

            // 2. Buscar en estudiantes agregados en caliente
            let nuevo = this.nuevosEstudiantes.find(n => this.coincideRun(n.run, runNorm));
            if (nuevo) {
                const estabaPresente = Boolean(nuevo.presente);
                nuevo.presente = true;
                if (!estabaPresente) {
                    this.destacarEstudiante(runNorm);
                    this.ultimoEscaneado = {
                        nombre: nuevo.nombre,
                        run: nuevo.run,
                        hora: horaMostrar
                    };
                }
                return !estabaPresente;
            }

            // 3. Estudiante no listado: agregar automáticamente a la lista y marcar presente
            const nuevoEst = {
                run: runNorm,
                nombre: nombreOpcional || ('Estudiante ' + runNorm),
                presente: true,
                inscrito: false
            };
            this.nuevosEstudiantes.push(nuevoEst);
            this.destacarEstudiante(runNorm);
            this.ultimoEscaneado = {
                nombre: nuevoEst.nombre,
                run: nuevoEst.run,
                hora: horaMostrar
            };
            return true;
        },

        destacarEstudiante(identificador) {
            this.estudianteDestacado = identificador;
            setTimeout(() => {
                if (this.estudianteDestacado === identificador) {
                    this.estudianteDestacado = null;
                }
            }, 3000);
        },

        get estudiantesFiltrados() {
            if (!this.filtro.trim()) {
                return this.estudiantes;
            }
            const q = this.filtro.toLowerCase();
            return this.estudiantes.filter(e => 
                (e.nombre && e.nombre.toLowerCase().includes(q)) || 
                (e.run && e.run.toLowerCase().includes(q))
            );
        },

        get totalEstudiantes() {
            return this.estudiantes.length + this.nuevosEstudiantes.length;
        },

        get totalPresentes() {
            const pres = this.estudiantes.filter(e => e.presente).length;
            return pres + this.nuevosEstudiantes.length;
        },

        get totalAusentes() {
            return this.estudiantes.filter(e => !e.presente).length;
        },

        get todosMarcados() {
            if (this.estudiantesFiltrados.length === 0) return false;
            return this.estudiantesFiltrados.every(e => e.presente);
        },

        toggleTodos(estado) {
            this.estudiantesFiltrados.forEach(e => e.presente = estado);
        },

        marcarTodos(estado) {
            this.estudiantes.forEach(e => e.presente = estado);
        },

        recalcular() {
            // Forzar reactividad de Alpine
        },

        agregarNuevoEstudiante() {
            const run = this.nuevoRun.trim().toUpperCase().replace(/[^0-9K]/g, '');
            const nombre = this.nuevoNombre.trim();

            if (!run || !nombre) {
                alert('Por favor ingresa tanto el RUN como el Nombre del estudiante.');
                return;
            }

            // Verificar si ya existe en la lista
            const yaExiste = this.estudiantes.some(e => this.coincideRun(e.run, run)) || this.nuevosEstudiantes.some(e => this.coincideRun(e.run, run));
            if (yaExiste) {
                alert('Este RUN ya se encuentra en la lista de asistencia.');
                return;
            }

            this.nuevosEstudiantes.push({
                run: run,
                nombre: nombre,
                presente: true,
                inscrito: false
            });

            this.destacarEstudiante(run);
            this.ultimoEscaneado = {
                nombre: nombre,
                run: run,
                hora: new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            };

            this.nuevoRun = '';
            this.nuevoNombre = '';
            this.mostrarModalNuevo = false;
        },

        eliminarNuevo(index) {
            this.nuevosEstudiantes.splice(index, 1);
        }
    }
}

window.asistenciaListHandler = asistenciaListHandler;
