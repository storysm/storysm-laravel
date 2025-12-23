@php
    use App\Facades\AgeVerification;
@endphp

<div>
    @if (AgeVerification::hasAgeSet() && AgeVerification::getAge() < 18)
        <x-filament::section icon="heroicon-o-information-circle" icon-color="info" collapsible>
            <x-slot name="heading">
                {{ __('age-verification.info') }}
            </x-slot>

            {{ __('age-verification.some_contents_are_hidden') }}

            <div class="mt-4">
                <div class="flex flex-row gap-1 text-sm">
                    {{ __('age-verification.entered_the_wrong_birthday') }}

                    <button type="button" wire:click="resetAge" class="font-bold hover:underline text-primary-500">
                        {{ __('age-verification.reset') }}
                    </button>
                </div>
            </div>
        </x-filament::section>
    @endif
</div>
