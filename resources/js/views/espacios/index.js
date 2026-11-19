        document.addEventListener('DOMContentLoaded', function () {
            // No cargar pisos automáticamente, se cargarán cuando se seleccione facultad
        });

        function loadSedes() {
            const universidadId = document.getElementById('universidad').value;
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar y deshabilitar selectores dependientes
            sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
            sedeSelect.disabled = true;
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!universidadId) return;

            fetch(`/sedes/${universidadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";

                    if (data && data.length > 0) {
                        data.forEach(sede => {
                            const option = document.createElement("option");
                            option.value = sede.id_sede;
                            option.textContent = sede.nombre_sede;
                            sedeSelect.appendChild(option);
                        });
                        sedeSelect.disabled = false;
                    } else {
                        sedeSelect.innerHTML = "<option value=''>No hay sedes disponibles</option>";
                        sedeSelect.disabled = true;
                    }
                })
                .catch(error => {
                    sedeSelect.innerHTML = "<option value=''>Error cargando sedes</option>";
                    sedeSelect.disabled = true;
                });
        }

        function loadFacultades() {
            const sedeId = document.getElementById('sede').value;
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar y deshabilitar selectores dependientes
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!sedeId) return;

            fetch(`/facultades-por-sede/${sedeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";

                    if (data && data.length > 0) {
                        data.forEach(facultad => {
                            const option = document.createElement("option");
                            option.value = facultad.id_facultad;
                            option.textContent = facultad.nombre_facultad;
                            facultadSelect.appendChild(option);
                        });
                        facultadSelect.disabled = false;
                    } else {
                        facultadSelect.innerHTML = "<option value=''>No hay facultades disponibles</option>";
                        facultadSelect.disabled = true;
                    }
                })
                .catch(error => {
                    facultadSelect.innerHTML = "<option value=''>Error cargando facultades</option>";
                    facultadSelect.disabled = true;
                });
        }

        function loadPisos() {
            const facultadId = document.getElementById('facultad').value;
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar selector de pisos
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!facultadId) return;

            fetch(`/pisos/${facultadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";

                    if (data && data.length > 0) {
                        data.forEach(piso => {
                            const option = document.createElement("option");
                            option.value = piso.id;
                            let nombrePiso = piso.nombre_piso;
                            if (facultadId === 'IT_LA') {
                                if (piso.numero_piso == 1) nombrePiso = 'CAUPOLICÁN 276';
                                else if (piso.numero_piso == 2) nombrePiso = 'VILLAGRÁN 220';
                                else if (piso.numero_piso == 3) nombrePiso = 'VILLAGRÁN 251';
                            }
                            option.textContent = nombrePiso ? nombrePiso : `Piso ${piso.numero_piso}`;
                            pisoSelect.appendChild(option);
                        });
                        pisoSelect.disabled = false;
                    } else {
                        pisoSelect.innerHTML = "<option value=''>No hay pisos disponibles</option>";
                        pisoSelect.disabled = true;
                    }
                })
                .catch(error => {
                    pisoSelect.innerHTML = "<option value=''>Error cargando pisos</option>";
                    pisoSelect.disabled = true;
                });
        }

        // Validación de puestos disponibles
        document.addEventListener('DOMContentLoaded', function () {
            const puestosInput = document.getElementById('puestos_disponibles');
            if (puestosInput) {
                puestosInput.addEventListener('input', function () {
                    const value = parseInt(this.value);
                    if (value < 1) {
                        this.value = 1;
                    }
                });

                puestosInput.addEventListener('blur', function () {
                    const value = parseInt(this.value);
                    if (value < 1 || isNaN(value)) {
                        this.value = 1;
                    }
                });
            }
        });

        // Recargar datos cuando se abre el modal
        document.addEventListener('livewire:load', function () {
            // Escuchar el evento de apertura del modal
            window.addEventListener('open-modal', function (event) {
                if (event.detail === 'add-espacio') {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }
            });
        });

        // También resetear cuando se hace clic en el botón de agregar
        document.addEventListener('click', function (event) {
            if (event.target.closest('[x-on\\:click*="open-modal"]')) {
                setTimeout(() => {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }, 100);
            }
        });

Object.assign(window, { loadSedes, loadFacultades, loadPisos });
