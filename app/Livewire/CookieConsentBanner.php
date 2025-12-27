<?php

namespace App\Livewire;

use App\Support\CookieConsent;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CookieConsentBanner extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public bool $visible = false;

    public function mount(): void
    {
        $this->visible = ! CookieConsent::hasConsented();
    }

    public function accept(): void
    {
        CookieConsent::consent();
        $this->visible = false;
    }

    public function acceptAction(): Action
    {
        return Action::make('accept')
            ->label(__('cookie-consent.accept'))
            ->color('primary')
            ->action('accept');
    }

    public function render(): View
    {
        return view('livewire.cookie-consent-banner');
    }
}
