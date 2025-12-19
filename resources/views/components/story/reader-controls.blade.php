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
            <button @click="$store.reader.toggleFullscreen()" title="{{ __('reader-controls.enter_focus_mode') }}"
                class="p-2 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                <x-heroicon-m-arrows-pointing-out class="w-5 h-5" />
            </button>
        </template>

        <template x-if="$store.reader.fullscreen">
            <div class="flex items-center gap-2">
                <button
                    @click="$store.reader.theme = ($store.reader.theme === 'light' ? 'sepia' : ($store.reader.theme === 'sepia' ? 'dark' : 'light'))"
                    title="{{ __('reader-controls.change_theme') }}" class="p-2 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-swatch class="w-5 h-5" />
                </button>

                <button
                    @click="$store.reader.font = ($store.reader.font === 'sans' ? 'serif' : ($store.reader.font === 'serif' ? 'mono' : 'sans'))"
                    title="{{ __('reader-controls.change_font') }}" class="p-2 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10">
                    <span x-text="$store.reader.font.charAt(0).toUpperCase() + $store.reader.font.slice(1)"
                        class="text-xs font-bold"></span>
                </button>

                <div class="flex items-center border-l border-r px-1 mx-1"
                    :class="{
                        'border-gray-300': $store.reader.theme === 'light',
                        'border-sepia-400': $store.reader.theme === 'sepia',
                        'border-gray-600': $store.reader.theme === 'dark'
                    }">
                    <button @click="$store.reader.fontSize = Math.max(70, $store.reader.fontSize - 10)"
                        title="{{ __('reader-controls.decrease_font_size') }}" class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded-2xl">
                        <span class="text-sm font-semibold">A-</span>
                    </button>
                    <button @click="$store.reader.fontSize = Math.min(200, $store.reader.fontSize + 10)"
                        title="{{ __('reader-controls.increase_font_size') }}" class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded-2xl">
                        <span class="text-base font-semibold">A+</span>
                    </button>
                </div>

                <button @click="$dispatch('open-modal', { id: 'reset-reader-prefs' })" title="{{ __('reader-controls.reset_to_defaults') }}"
                    class="p-2 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-arrow-path class="w-5 h-5" />
                </button>

                <button @click="$store.reader.toggleFullscreen()" title="{{ __('reader-controls.exit_focus_mode') }}"
                    class="p-2 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                    <x-heroicon-m-arrows-pointing-in class="w-5 h-5" />
                </button>
            </div>
        </template>
    </div>

    <x-filament::modal id="reset-reader-prefs" icon="heroicon-o-exclamation-triangle" icon-color="warning">
        <x-slot name="heading">{{ __('reader-controls.reset_preferences_heading') }}</x-slot>
        <x-slot name="description">{{ __('reader-controls.reset_preferences_description') }}</x-slot>
        <x-slot name="footerActions">
            <x-filament::button color="gray" @click="close">{{ __('reader-controls.cancel') }}</x-filament::button>
            <x-filament::button color="primary" @click="$store.reader.resetToDefaults(); close()">{{ __('reader-controls.reset_button') }}</x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
