<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AgeVerificationForm;
use Livewire\Livewire;
use Tests\TestCase;

class AgeVerificationFormTest extends TestCase
{
    public function test_it_redirects_if_under_required_age(): void
    {
        // 17 years old
        $dob = now()->subYears(17)->format('Y-m-d');

        Livewire::test(AgeVerificationForm::class, ['requiredAge' => 18])
            ->fillForm(['dob' => $dob])
            ->call('verify')
            ->assertRedirect(route('content.forbidden'));
    }

    public function test_it_dispatches_event_if_age_met(): void
    {
        // 20 years old
        $dob = now()->subYears(20)->format('Y-m-d');

        Livewire::test(AgeVerificationForm::class, ['requiredAge' => 18])
            ->fillForm(['dob' => $dob])
            ->call('verify')
            ->assertDispatched('ageVerified');
    }
}
