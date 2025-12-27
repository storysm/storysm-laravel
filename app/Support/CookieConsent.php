<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;

final class CookieConsent
{
    public const COOKIE_NAME = 'cookie_consent';

    public const VALUE_ACCEPTED = 'accepted';

    public static function hasConsented(): bool
    {
        return Cookie::get(self::COOKIE_NAME) === self::VALUE_ACCEPTED;
    }

    public static function consent(int $days = 365): void
    {
        Cookie::queue(
            self::COOKIE_NAME,
            self::VALUE_ACCEPTED,
            $days * 24 * 60,
            null,
            null,
            true,
            false,
            'lax'
        );
    }
}
