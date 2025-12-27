<?php

namespace App\Livewire;

use App\Support\CookieConsent;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CookieConsentBanner extends Component
{
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

    public function render(): View
    {
        return view('livewire.cookie-consent-banner');
    }
}
