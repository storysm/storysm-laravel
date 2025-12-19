@props(['story'])

<div x-cloak x-show="showToolbar" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-8"
    class="fixed z-[60] flex items-center gap-2 p-2 shadow-xl rounded-full border transition-all duration-300 bottom-6 left-1/2 -translate-x-1/2 md:bottom-auto md:left-auto md:top-24 md:right-8 md:translate-x-0"
    :class="{
        'bg-white/90 border-gray-200 text-gray-700': theme === 'light',
        'bg-sepia-200/90 border-sepia-300 text-sepia-900': theme === 'sepia',
        'bg-gray-800/90 border-gray-700 text-gray-200': theme === 'dark'
    }">
    {{-- Theme Cycle --}}
    <button @click="theme = (theme === 'light' ? 'sepia' : (theme === 'sepia' ? 'dark' : 'light'))"
        class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
        <x-heroicon-m-swatch class="w-5 h-5" />
    </button>

    {{-- Font Cycle --}}
    <button @click="font = (font === 'sans' ? 'serif' : (font === 'serif' ? 'mono' : 'sans'))"
        class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
        <span x-text="font.toUpperCase().substring(0, 1)" class="text-xs font-bold"></span>
    </button>

    {{-- Scaling --}}
    <div class="flex items-center border-l border-r border-gray-300 dark:border-gray-600 px-1 mx-1">
        <button @click="fontSize = Math.max(70, fontSize - 10)" class="p-2">
            <x-heroicon-m-minus class="w-4 h-4" />
        </button>
        <button @click="fontSize = Math.min(200, fontSize + 10)" class="p-2">
            <x-heroicon-m-plus class="w-4 h-4" />
        </button>
    </div>

    {{-- Focus Mode Toggle --}}
    <button @click="toggleFullscreen()"
        class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10 text-primary-600">
        <x-heroicon-m-arrows-pointing-out x-show="!fullscreen" class="w-5 h-5" />
        <x-heroicon-m-arrows-pointing-in x-show="fullscreen" class="w-5 h-5" />
    </button>
</div>
