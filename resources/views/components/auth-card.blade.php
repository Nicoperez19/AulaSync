<main class="flex flex-col items-center flex-1 px-4 pt-6 sm:justify-center">
    <div class="w-full py-8 my-6 overflow-hidden bg-white rounded-md shadow-md px-9 sm:max-w-md dark:bg-dark-eval-1">
        <div class="mb-8">
            <a href="/">
                <x-application-logo class="w-64 h-64" />
            </a>
        </div>
        {{ $slot }}
    </div>
</main>