<?php

namespace Tests\Unit\Support;

use App\Support\CookieConsent;
use Illuminate\Cookie\CookieJar;
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
                'cookie-consent',
                'accepted',
                525600, // 365 days in minutes
                null,
                null,
                false, // assuming not in production during tests
                false,
                'lax'
            );

        CookieConsent::consent();
    }

    public function test_consent_flow(): void
    {
        // Simulate existing cookie
        $this->withCookie('cookie-consent', 'accepted')
            ->get(route('home'));

        $this->assertTrue(CookieConsent::hasConsented());
    }

    public function test_it_queues_a_cookie_when_consenting(): void
    {
        CookieConsent::consent();

        /** @var CookieJar $jar */
        $jar = app('cookie');

        $cookies = collect($jar->getQueuedCookies());

        $this->assertTrue(
            $cookies->contains(fn ($cookie) => $cookie->getName() === 'cookie-consent'
                && $cookie->getValue() === 'accepted'
            )
        );
    }
}
