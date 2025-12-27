<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CookieConsentBanner;
use App\Support\CookieConsent;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;
use Tests\TestCase;

class CookieConsentBannerTest extends TestCase
{
    public function test_banner_is_visible_when_no_consent_exists(): void
    {
        // Ensure no cookie consent exists
        Cookie::forget(CookieConsent::COOKIE_NAME);

        $component = Livewire::test(CookieConsentBanner::class);
        $component->assertSet('visible', true);
        $component->assertSeeHtml(__('cookie-consent.title'));
    }

    public function test_banner_is_hidden_when_consent_exists(): void
    {
        Livewire::withCookie(CookieConsent::COOKIE_NAME, CookieConsent::VALUE_ACCEPTED);
        $component = Livewire::test(CookieConsentBanner::class);
        $component->assertSet('visible', false);
        $component->assertDontSeeHtml(__('cookie-consent.title'));
    }

    public function test_user_can_accept_cookies(): void
    {
        $component = Livewire::test(CookieConsentBanner::class);

        $component->call('accept');

        $component->assertSet('visible', false);

        $queuedCookies = Cookie::queued(CookieConsent::COOKIE_NAME);

        $this->assertNotNull($queuedCookies);
        $this->assertEquals(CookieConsent::VALUE_ACCEPTED, $queuedCookies->getValue());
    }
}
