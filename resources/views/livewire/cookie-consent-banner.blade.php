<div>
    @if ($visible)
        <div role="region" aria-label="{{ __('cookie-consent.cookie_preferences') }}"
            class="fixed bottom-4 right-4 left-4 z-50 sm:left-auto sm:w-full sm:max-w-md">
            <x-filament::section>
                <div class="mx-auto flex max-w-7xl flex-col gap-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-bold text-lg">
                            {{ __('cookie-consent.title') }}
                        </p>
                        <p class="mt-1">
                            {{ __('cookie-consent.description') }}
                            <a href="{{ route('cookie.show') }}" class="underline">
                                {{ __('cookie-consent.learn_more') }}
                            </a>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-filament::modal icon="heroicon-o-information-circle" icon-color="info">
                            <x-slot name="trigger">
                                <x-filament::button color="gray" outlined>
                                    {{ __('cookie-consent.preferences') }}
                                </x-filament::button>
                            </x-slot>
                            <x-slot name="heading">
                                {{ __('cookie-consent.preferences') }}
                            </x-slot>
                            <x-cookie-consent-preferences />
                        </x-filament::modal>

                        {{ $this->acceptAction }}
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif
</div>
