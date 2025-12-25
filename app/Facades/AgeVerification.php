<?php

namespace App\Facades;

use App\Services\AgeVerificationService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void setAge(int $age, bool $remember = false)
 * @method static int calculateAge(string $dob)
 * @method static ?int getAge()
 * @method static bool hasAgeSet()
 * @method static void clearAge()
 *
 * @see AgeVerificationService
 */
class AgeVerification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AgeVerificationService::class;
    }
}
