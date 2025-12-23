<x-filament::section class="max-w-md w-full mx-auto">
    <x-slot name="heading">
        {{ __('age-verification.age_verification') }}
    </x-slot>

    <form wire:submit="verify" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full">
            {{ __('age-verification.confirm_access') }}
        </x-filament::button>
    </form>
</x-filament::section>
