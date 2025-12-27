<?php

namespace Tests\Unit\Support;

use App\Support\CookieConsent;
use Illuminate\Support\Facades\Cookie;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_it_queues_consent_cookie(): void
    {
        Cookie::shouldReceive('queue')
            ->once()
            ->with(
                CookieConsent::COOKIE_NAME,
                CookieConsent::VALUE_ACCEPTED,
                525600, // 365 days in minutes
                null,
                null,
                false,
                false,
                'lax'
            );

        CookieConsent::consent();
    }

    public function test_it_checks_if_user_has_consented(): void
    {
        // Simulate the cookie being present in the request
        request()->cookies->set(CookieConsent::COOKIE_NAME, CookieConsent::VALUE_ACCEPTED);

        $this->assertTrue(CookieConsent::hasConsented());

        // Simulate cookie absence
        request()->cookies->remove(CookieConsent::COOKIE_NAME);

        $this->assertFalse(CookieConsent::hasConsented());
    }
}
