<x-app-layout>
    <x-header>
        {{ __('forbidden.title') }}
    </x-header>

    <x-container>
        <filament::section class="flex flex-col items-center min-h-screen pt-6 sm:pt-0">
            <div
                class="w-full p-6 mt-6 overflow-hidden prose bg-white shadow-md sm:max-w-2xl dark:bg-gray-900 sm:rounded-lg dark:prose-invert">
                <div class="text-center">
                    <div
                        class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-lock-closed"
                            class="w-10 h-10 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('forbidden.heading') }}
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        {{ __('forbidden.message') }}
                    </p>
                    <x-filament::button :href="url('/')" tag="a" wire:navigate icon="heroicon-o-home"
                        class="no-underline">
                        <span>{{ __('forbidden.return_home') }}</span>
                    </x-filament::button>
                </div>
            </div>
        </filament::section>
    </x-container>
</x-app-layout>
