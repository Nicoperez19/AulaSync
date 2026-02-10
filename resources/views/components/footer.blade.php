<footer class="flex-shrink-0 px-6 py-4 mt-10 border-t border-gray-300 dark:border-gray-700">
    <p class="text-sm text-center text-black dark:text-white">
        © {{ date('Y') }} - Impulsado por Universidad Católica de la Santísima Concepción<br>
        @php
        $pkg = json_decode(file_get_contents(base_path('package.json')), true);
        $version = $pkg['version'] ?? 'n/a';
        @endphp
        <span class="font-medium">Versión {{ $version }}</span></p>
</footer>

