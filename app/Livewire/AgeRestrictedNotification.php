<?php

namespace App\Livewire;

use App\Facades\AgeVerification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AgeRestrictedNotification extends Component
{
    public function resetAge(): void
    {
        AgeVerification::clearAge();
    }

    /**
     * Determine if the age verification notification should be shown.
     */
    public function shouldShow(): bool
    {
        return ! AgeVerification::hasAgeSet();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('livewire.age-restricted-notification');
    }
}
