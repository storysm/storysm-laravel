@if ($visible)
    <div
        class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white px-6 py-4 shadow-lg dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <p class="font-medium">
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
                {{ \Filament\Actions\Action::make('preferences')->label(__('cookie-consent::preferences'))->color('gray')->outlined()->modalHeading(__('cookie-consent::cookie_preferences'))->modalSubmitAction(false)->modalCancelActionLabel(__('cookie-consent::close'))->modalContent(view('cookie-consent.preferences')) }}

                {{ \Filament\Actions\Action::make('accept')->label(__('cookie-consent::accept'))->color('primary')->action('accept') }}
            </div>
        </div>
    </div>
@endif
