 <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Universidad -->
                    <div>
                        <label class="block mb-1">Universidad</label>
                        <select id="selectedUniversidad" name="selectedUniversidad" class="w-full border rounded p-2">
                            <option value="">Seleccione</option>
                            @foreach($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Facultad -->
                    <div>
                        <label class="block mb-1">Facultad</label>
                        <select id="selectedFacultad" name="selectedFacultad" class="w-full border rounded p-2" disabled>
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <!-- Piso -->
                    <div>
                        <label class="block mb-1">Piso</label>
                        <select id="selectedPiso" name="selectedPiso" class="w-full border rounded p-2" disabled>
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <!-- Espacio -->
                    <div>
                        <label class="block mb-1">Espacio</label>
                        <select id="selectedEspacio" name="selectedEspacio" class="w-full border rounded p-2" disabled>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                </div>
            </div>

            <form action="{{ route('mapas.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="text" name="mapName" placeholder="Nombre del mapa" class="border p-2 rounded w-full mb-2" required>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Guardar Mapa</button>
                    <button type="button" id="clearCanvas" class="bg-red-500 text-white px-4 py-2 rounded ml-2">Borrar Bloques</button>
                </div>

                <button type="button" id="addBlock" class="bg-gray-700 text-white px-4 py-2 rounded mb-4">Agregar Bloque</button>

                <canvas id="myCanvas" width="500" height="500" style="border:1px solid #000000;"></canvas>

                <input type="hidden" name="canvasData" id="canvasData">
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById("myCanvas");
            const ctx = canvas.getContext("2d");
            let squares = [];
            let selectedSquare = null;
            let offsetX, offsetY;

            function drawSquares() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                squares.forEach(function(square) {
                    ctx.fillStyle = "#fff";
                    ctx.strokeStyle = "#000";
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.roundRect(square.x, square.y, 50, 50, 10);
                    ctx.fill();
                    ctx.stroke();
                });
            }

            document.getElementById("addBlock").addEventListener("click", function() {
                squares.push({ x: canvas.width / 2 - 25, y: canvas.height / 2 - 25 });
                drawSquares();
            });

            document.getElementById("clearCanvas").addEventListener("click", function() {
                squares = [];
                drawSquares();
            });

            canvas.addEventListener("mousedown", function(event) {
                const rect = canvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                selectedSquare = squares.find(sq =>
                    x > sq.x && x < sq.x + 50 && y > sq.y && y < sq.y + 50
                );

                if (selectedSquare) {
                    offsetX = x - selectedSquare.x;
                    offsetY = y - selectedSquare.y;
                }
            });

            canvas.addEventListener("mousemove", function(event) {
                if (selectedSquare) {
                    const rect = canvas.getBoundingClientRect();
                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;

                    selectedSquare.x = x - offsetX;
                    selectedSquare.y = y - offsetY;
                    drawSquares();
                }
            });

            canvas.addEventListener("mouseup", () => selectedSquare = null);
            canvas.addEventListener("mouseout", () => selectedSquare = null);

            // Cargar facultades
            document.getElementById("selectedUniversidad").addEventListener("change", function() {
                const universidadId = this.value;
                if (universidadId) {
                    fetch(`/mapas/facultades/${universidadId}`)
                        .then(response => response.json())
                        .then(data => {
                            const selectFacultad = document.getElementById("selectedFacultad");
                            selectFacultad.innerHTML = "<option value=''>Seleccione</option>";
                            data.forEach(facultad => {
                                const option = document.createElement("option");
                                option.value = facultad.id_facultad;
                                option.textContent = facultad.nombre_facultad;
                                selectFacultad.appendChild(option);
                            });
                            selectFacultad.disabled = false;
                        });
                }
            });

            // Cargar pisos
            document.getElementById("selectedFacultad").addEventListener("change", function() {
                const facultadId = this.value;
                if (facultadId) {
                    fetch(`/mapas/pisos/${facultadId}`)
                        .then(response => response.json())
                        .then(data => {
                            const selectPiso = document.getElementById("selectedPiso");
                            selectPiso.innerHTML = "<option value=''>Seleccione</option>";
                            data.forEach(piso => {
                                const option = document.createElement("option");
                                option.value = piso.id;
                                option.textContent = piso.numero_piso;
                                selectPiso.appendChild(option);
                            });
                            selectPiso.disabled = false;
                        });
                }
            });

            // Cargar espacios
            document.getElementById("selectedPiso").addEventListener("change", function() {
                const pisoId = this.value;
                if (pisoId) {
                    fetch(`/mapas/espacios/${pisoId}`)
                        .then(response => response.json())
                        .then(data => {
                            const selectEspacio = document.getElementById("selectedEspacio");
                            selectEspacio.innerHTML = "<option value=''>Seleccione</option>";
                            data.forEach(espacio => {
                                const option = document.createElement("option");
                                option.value = espacio.id_espacio;
                                option.textContent = espacio.tipo_espacio;
                                selectEspacio.appendChild(option);
                            });
                            selectEspacio.disabled = false;
                        });
                }
            });
        });
    </script>