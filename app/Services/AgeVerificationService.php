<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AgeVerificationService
{
    /**
     * Set the user's age in session and optionally in cookie
     */
    public function setAge(int $age, bool $remember = false): void
    {
        // Store age in session
        Session::put('user_age', $age);

        // If remember is true, create a cookie that lasts 30 days
        if ($remember) {
            Cookie::queue('user_age', $age, 60 * 24 * 30); // 30 days in minutes
        }
    }

    /**
     * Calculate age from date of birth
     */
    public function calculateAge(string $dateOfBirth): int
    {
        return Carbon::parse($dateOfBirth)->age;
    }

    /**
     * Get the user's age from session or cookie
     */
    public function getAge(): ?int
    {
        // First check session
        /** @var int|null $age */
        $age = Session::get('user_age');

        // If not in session, check cookie and sync to session if found
        if ($age === null) {
            /** @var int|null $age */
            $age = Cookie::get('user_age');
            if ($age !== null) {
                Session::put('user_age', $age);
            }
        }

        return $age;
    }

    /**
     * Check if age has been set (in session or cookie)
     */
    public function hasAgeSet(): bool
    {
        return $this->getAge() !== null;
    }
}
