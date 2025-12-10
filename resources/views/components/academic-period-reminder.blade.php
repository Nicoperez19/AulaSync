@if($showReminder)
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="relative flex items-center px-3 py-2 text-sm bg-yellow-100 border border-yellow-300 text-yellow-800 rounded-md mr-3">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <span class="hidden lg:inline">{{ $reminderMessage }}</span>
        <span class="lg:hidden">Configurar períodos</span>
        <a href="{{ route('data.index') }}" 
           class="ml-2 px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 transition">
            Configurar
        </a>
        <button @click="show = false" class="ml-2 text-yellow-600 hover:text-yellow-800">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif
