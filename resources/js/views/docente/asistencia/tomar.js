        function cambiarFechaSesion(nuevaFecha) {
            if (!nuevaFecha) return;
            const hidden = document.getElementById('fecha_asistencia_hidden');
            if (hidden) {
                hidden.value = nuevaFecha;
            }
            const baseUrl = (window.DocenteTomarConfig?.baseUrl ?? '');
            window.location.href = baseUrl + '?fecha=' + encodeURIComponent(nuevaFecha);
        }

        // Sincronizar el contexto con el agente local y reflejar en vivo los escaneos QR guardados en el servidor.
        (function iniciarSincronizacionAsistencia() {
            const idAsignaturaActual = (window.DocenteTomarConfig?.idAsignatura ?? '');
            const tenantIdActual = (window.DocenteTomarConfig?.tenantId ?? null);
            const urlEstadoEnVivo = (window.DocenteTomarConfig?.urlEstadoEnVivo ?? '');
            const runsSincronizados = new Map();
            let consultaEnCurso = false;
            let formularioEnviando = false;

            const formAsistencia = document.querySelector('form[action*="asistencia"]');
            if (formAsistencia) {
                formAsistencia.addEventListener('submit', () => {
                    formularioEnviando = true;
                });
            }

            async function notificarAgente() {
                try {
                    await fetch(`http://127.0.0.1:8181/set-asignatura?id=${encodeURIComponent(idAsignaturaActual)}&tenant_id=${encodeURIComponent(tenantIdActual || '')}`, {
                        method: 'GET',
                        mode: 'cors'
                    });
                } catch (e) {
                    // El agente puede no estar ejecutándose en este equipo.
                }
            }

            async function sincronizarDesdeServidor() {
                if (consultaEnCurso || formularioEnviando || document.hidden) return;
                consultaEnCurso = true;
                try {
                    const res = await fetch(urlEstadoEnVivo + '&t=' + Date.now(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        cache: 'no-store'
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (!data || !data.ok) return;

                    if (Array.isArray(data.presentes)) {
                        for (const p of data.presentes) {
                            const key = String(p.run || p.estudiante_id || '').toUpperCase().replace(/[^0-9K]/g, '');
                            if (!key) continue;
                            const horaMarca = p.hora || '';
                            const horaAnterior = runsSincronizados.get(key);
                            if (horaAnterior === undefined || (horaMarca && horaAnterior !== horaMarca)) {
                                runsSincronizados.set(key, horaMarca);
                                if (typeof window.asistenciaMarcarRun === 'function') {
                                    window.asistenciaMarcarRun(p.run, p.nombre, p.metodo || 'qr', p.hora || null);
                                }
                            }
                        }
                    }

                    const statusEl = document.getElementById('estado-sync-en-vivo');
                    const btnLabelEl = document.getElementById('btn-guardar-asistencia-label');
                    if (data.hay_sesion) {
                        if (btnLabelEl) {
                            btnLabelEl.textContent = 'Guardar Modificaciones';
                        }
                        if (statusEl) {
                            statusEl.textContent = `Sincronizado en vivo (${data.total_presentes || 0} presente(s) guardado(s) automáticamente).`;
                        }
                    }
                } catch (e) {
                    // Error de red transitorio: reintentará en el próximo ciclo
                } finally {
                    consultaEnCurso = false;
                }
            }

            notificarAgente();
            sincronizarDesdeServidor();
            setInterval(notificarAgente, 15000);
            setInterval(sincronizarDesdeServidor, 2000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    sincronizarDesdeServidor();
                }
            });
        })();

if (typeof cambiarFechaSesion === 'function') window.cambiarFechaSesion = cambiarFechaSesion;
