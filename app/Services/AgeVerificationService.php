<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AgeVerificationService
{
    private const MINIMUM_AGE = 13;

    public function calculateAge(string $dob): int
    {
        // Use application timezone for consistent boundary checks
        /** @var string $timezone */
        $timezone = config('app.timezone');

        // Use startOfDay to ensure consistent age calculation regardless of the current time
        return Carbon::parse($dob, $timezone)->startOfDay()->age;
    }

    public function setAge(int $age, bool $remember = false): void
    {
        if ($age < self::MINIMUM_AGE) {
            // Never persist under-13 data
            $this->clearAge();

            throw new \DomainException('User is under minimum age');
        }

        Session::put('user_age', $age);

        if ($remember) {
            /** @var int $minutes */
            $minutes = config('age_rating.cookie_duration_minutes');
            Cookie::queue(
                Cookie::make('user_age', (string) $age, $minutes)
            );
        }
    }

    public function getAge(): ?int
    {
        if (Session::has('user_age')) {
            /** @var int $age */
            $age = Session::get('user_age');

            return $age;
        }

        if ($age = request()->cookie('user_age')) {
            Session::put('user_age', (int) $age);

            return (int) $age;
        }

        return null;
    }

    public function hasAgeSet(): bool
    {
        return $this->getAge() !== null;
    }

    public function clearAge(): void
    {
        Session::forget('user_age');
        Cookie::queue(Cookie::forget('user_age'));
    }
}
