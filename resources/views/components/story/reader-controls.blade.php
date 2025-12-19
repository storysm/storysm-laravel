@props(['story'])

<div x-data="readerControls" @reader-scroll.window="handleScroll($event.detail.event)">
    {{-- Controls Toolbar --}}
    <div x-cloak x-show="showToolbar" x-transition:enter="transition-transform duration-500"
        x-transition:enter-start="translate-y-24" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition-transform duration-500" x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-24"
        class="fixed z-[60] flex items-center gap-2 p-2 shadow-xl rounded-2xl border transition-transform duration-500"
        :class="{
            'bottom-6 -translate-x-1/2 z-50': true,
            'right-8 translate-x-0': !$store.reader.fullscreen,
            'left-1/2': $store.reader.fullscreen,
            'bg-white/90 border-gray-200 text-gray-700': $store.reader.theme === 'light' && $store.reader.fullscreen,
            'bg-sepia-200/90 border-sepia-300 text-sepia-900': $store.reader.theme === 'sepia' && $store.reader
                .fullscreen,
            'bg-gray-800/90 border-gray-700 text-gray-200': $store.reader.theme === 'dark' && $store.reader
                .fullscreen,
            'bg-white/90 dark:bg-gray-800/90 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200': !
                $store.reader.fullscreen
        }">

        <template x-if="!$store.reader.fullscreen">
            <button @click="$store.reader.toggleFullscreen()" title="Enter focus mode"
                class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                <x-heroicon-m-arrows-pointing-out class="w-5 h-5" />
            </button>
        </template>

        <template x-if="$store.reader.fullscreen">
            <div class="flex items-center gap-2">
                <button
                    @click="$store.reader.theme = ($store.reader.theme === 'light' ? 'sepia' : ($store.reader.theme === 'sepia' ? 'dark' : 'light'))"
                    title="Change theme" class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-swatch class="w-5 h-5" />
                </button>

                <button
                    @click="$store.reader.font = ($store.reader.font === 'sans' ? 'serif' : ($store.reader.font === 'serif' ? 'mono' : 'sans'))"
                    title="Change font" class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <span x-text="$store.reader.font.toUpperCase().substring(0, 1)" class="text-xs font-bold"></span>
                </button>

                <div class="flex items-center border-l border-r px-1 mx-1"
                    :class="{
                        'border-gray-300': $store.reader.theme === 'light',
                        'border-sepia-400': $store.reader.theme === 'sepia',
                        'border-gray-600': $store.reader.theme === 'dark'
                    }">
                    <button @click="$store.reader.fontSize = Math.max(70, $store.reader.fontSize - 10)"
                        title="Decrease font size" class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded">
                        <span class="text-sm font-semibold">A-</span>
                    </button>
                    <button @click="$store.reader.fontSize = Math.min(200, $store.reader.fontSize + 10)"
                        title="Increase font size" class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded">
                        <span class="text-base font-semibold">A+</span>
                    </button>
                </div>

                <button @click="$dispatch('open-modal', { id: 'reset-reader-prefs' })" title="Reset to defaults"
                    class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-arrow-path class="w-5 h-5" />
                </button>

                <button @click="$store.reader.toggleFullscreen()" title="Exit focus mode"
                    class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                    <x-heroicon-m-arrows-pointing-in class="w-5 h-5" />
                </button>
            </div>
        </template>
    </div>

    <x-filament::modal id="reset-reader-prefs" icon="heroicon-o-exclamation-triangle" icon-color="warning">
        <x-slot name="heading">Reset Reader Preferences?</x-slot>
        <x-slot name="description">This will reset your theme, font, and text size preferences.</x-slot>
        <x-slot name="footerActions">
            <x-filament::button color="gray" @click="close">Cancel</x-filament::button>
            <x-filament::button color="primary" @click="$store.reader.resetToDefaults(); close()">Reset to
                Defaults</x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
