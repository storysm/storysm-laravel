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
        return Carbon::parse($dob)->age;
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
            Cookie::queue(
                Cookie::make('user_age', (string) $age, 60 * 24 * 30)
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
