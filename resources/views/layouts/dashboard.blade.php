<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <div class="max-w-2xl mx-auto">
            <h3 class="mb-4 text-lg font-semibold">Lector de Códigos QR</h3>

            <!-- Campo de entrada oculto para capturar el escaneo -->
            <input type="text" id="qr-input"
                class="w-full p-2 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Escanea un código QR..." autofocus>

            <!-- Resultado del escaneo -->
            <div class="p-4 bg-gray-100 rounded-lg">
                <h4 class="mb-2 font-medium">Contenido del código QR:</h4>
                <p id="qr-result" class="text-gray-700">Esperando escaneo...</p>
            </div>

            <!-- Historial de escaneos -->
            <div class="mt-6">
                <h4 class="mb-2 font-medium">Historial de escaneos:</h4>
                <div id="scan-history" class="overflow-y-auto max-h-60">
                    <!-- Los escaneos se agregarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-xl font-bold mb-4">Escáner QR</h2>
        <div class="mb-2">
            <span class="font-semibold">RUN Escaneado:</span>
            <span id="dashboard-run" class="ml-2">--</span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Usuario:</span>
            <span id="dashboard-nombre" class="ml-2">--</span>
        </div>
        <!-- Input invisible para el escáner físico -->
        <input type="text" id="dashboard-qr-input" class="opacity-0 absolute" autofocus>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrInput = document.getElementById('qr-input');
            const qrResult = document.getElementById('qr-result');
            const scanHistory = document.getElementById('scan-history');
            let scanBuffer = '';
            let lastScanTime = 0;

            function corregirTextoQR(texto) {
                return texto
                    .replace("httpsÑ--", "https://")
                    .replace(/¿/g, "=")
                    .replace(/'/g, "-")
                    .replace(/\/docstatus/, "/docstatus?") // asegúrate que tenga '?'
                    .replace(/\/(RUN|type|serial|mrz)/g, "&$1"); // parámetros como &RUN, &type...
            }

            qrInput.addEventListener('keypress', function(e) {
                const currentTime = new Date().getTime();
                if (currentTime - lastScanTime > 100) {
                    scanBuffer = '';
                }
                lastScanTime = currentTime;

                if (e.key === 'Enter') {
                    e.preventDefault();

                    const corregido = corregirTextoQR(scanBuffer);

                    qrResult.textContent = corregido;

                    const historyItem = document.createElement('div');
                    historyItem.className = 'p-2 mb-2 bg-white rounded border border-gray-200';
                    historyItem.innerHTML = `
                    <div class="text-sm text-gray-600">${new Date().toLocaleTimeString()}</div>
                    <div class="font-medium break-all">${corregido}</div>
                `;
                    scanHistory.insertBefore(historyItem, scanHistory.firstChild);

                    scanBuffer = '';
                    qrInput.value = '';
                } else {
                    scanBuffer += e.key;
                }
            });

            qrInput.focus();
            document.addEventListener('click', function() {
                qrInput.focus();
            });
        });

        let dashboardBufferQR = '';

        function dashboardManejarInputEscanner(event) {
            if (event.key.length === 1) {
                dashboardBufferQR += event.key;
            }
            const match = dashboardBufferQR.match(/RUN¿(\d+)'/);
            if (match) {
                const run = match[1];
                document.getElementById('dashboard-run').textContent = run;
                // Consultar la API para obtener el nombre
                fetch(`/api/user/${run}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.user) {
                            document.getElementById('dashboard-nombre').textContent = data.user.name;
                        } else {
                            document.getElementById('dashboard-nombre').textContent = '--';
                        }
                    })
                    .catch(() => {
                        document.getElementById('dashboard-nombre').textContent = '--';
                    });
                dashboardBufferQR = '';
                event.target.value = '';
            }
            if (event.key === 'Escape' || dashboardBufferQR.length > 30) {
                dashboardBufferQR = '';
                event.target.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('dashboard-qr-input');
            input.addEventListener('keydown', dashboardManejarInputEscanner);
            document.addEventListener('click', function() { input.focus(); });
            input.focus();
        });
    </script>

</x-app-layout>
