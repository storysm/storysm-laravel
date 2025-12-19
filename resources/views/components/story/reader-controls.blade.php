@props(['story'])

<div>
    {{-- Controls Toolbar --}}
    <div x-cloak x-show="showToolbar" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed z-[60] flex items-center gap-2 p-2 shadow-xl rounded-2xl border transition-all duration-300"
        :class="{
            'bottom-6 left-1/2 -translate-x-1/2': true,
            'sm:bottom-auto sm:left-auto sm:top-24 sm:right-8 sm:translate-x-0': !fullscreen,
            'bg-white/90 border-gray-200 text-gray-700': theme === 'light' && fullscreen,
            'bg-sepia-200/90 border-sepia-300 text-sepia-900': theme === 'sepia' && fullscreen,
            'bg-gray-800/90 border-gray-700 text-gray-200': theme === 'dark' && fullscreen,
            'bg-white/90 dark:bg-gray-800/90 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200': !
                fullscreen
        }">

        {{-- Show only fullscreen toggle when NOT in fullscreen --}}
        <template x-if="!fullscreen">
            <button @click="toggleFullscreen()" title="Enter focus mode"
                class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                <x-heroicon-m-arrows-pointing-out class="w-5 h-5" />
            </button>
        </template>

        {{-- Show all controls when IN fullscreen --}}
        <template x-if="fullscreen">
            <div class="flex items-center gap-2">
                {{-- Theme Cycle --}}
                <button @click="theme = (theme === 'light' ? 'sepia' : (theme === 'sepia' ? 'dark' : 'light'))"
                    title="Change theme" class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-swatch class="w-5 h-5" />
                </button>

                {{-- Font Cycle --}}
                <button @click="font = (font === 'sans' ? 'serif' : (font === 'serif' ? 'mono' : 'sans'))"
                    title="Change font" class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <span x-text="font.toUpperCase().substring(0, 1)" class="text-xs font-bold"></span>
                </button>

                {{-- Font Size Controls --}}
                <div class="flex items-center border-l border-r px-1 mx-1"
                    :class="{
                        'border-gray-300': theme === 'light',
                        'border-sepia-400': theme === 'sepia',
                        'border-gray-600': theme === 'dark'
                    }">
                    <button @click="fontSize = Math.max(70, fontSize - 10)" title="Decrease font size"
                        class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded">
                        <span class="text-sm font-semibold">A-</span>
                    </button>
                    <button @click="fontSize = Math.min(200, fontSize + 10)" title="Increase font size"
                        class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded">
                        <span class="text-base font-semibold">A+</span>
                    </button>
                </div>

                {{-- Reset Button --}}
                <button @click="resetToDefaults()" title="Reset to defaults"
                    class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10">
                    <x-heroicon-m-arrow-path class="w-5 h-5" />
                </button>

                {{-- Exit Fullscreen --}}
                <button @click="toggleFullscreen()" title="Exit focus mode"
                    class="p-2 rounded-full hover:bg-black/10 dark:hover:bg-white/10 text-primary-600 dark:text-primary-400">
                    <x-heroicon-m-arrows-pointing-in class="w-5 h-5" />
                </button>
            </div>
        </template>
    </div>

    {{-- Reset Confirmation Modal --}}
    <div x-show="showResetModal" x-cloak @click.self="cancelReset()"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Reset Reader Preferences?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        This will reset your theme, font, and text size preferences to their default values. This action
                        cannot be undone.
                    </p>

                    <div class="flex gap-3 justify-end">
                        <button @click="cancelReset()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button @click="confirmReset()"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                            Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
