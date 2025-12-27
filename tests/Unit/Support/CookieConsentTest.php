<?php

namespace Tests\Unit\Support;

use App\Support\CookieConsent;
use Illuminate\Support\Facades\Cookie;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_it_identifies_when_consent_has_not_been_given(): void
    {
        Cookie::shouldReceive('get')
            ->with('cookie-consent')
            ->andReturn(null);

        $this->assertFalse(CookieConsent::hasConsented());
    }

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

    public function test_it_queues_a_cookie_when_consenting(): void
    {
        Cookie::shouldReceive('queue')
            ->once()
            ->with(
                CookieConsent::COOKIE_NAME,
                CookieConsent::VALUE_ACCEPTED,
                525600,
                null,
                null,
                false,
                false,
                'lax'
            );

        CookieConsent::consent();
    }
}
