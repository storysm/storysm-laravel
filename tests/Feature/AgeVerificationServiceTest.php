<?php

namespace Tests\Feature;

use App\Services\AgeVerificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AgeVerificationService $ageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ageService = app(AgeVerificationService::class);
    }

    public function test_calculate_age(): void
    {
        // Test with a known date (20 years ago from today)
        $dob = Carbon::now()->subYears(20)->toDateString();
        $age = $this->ageService->calculateAge($dob);

        $this->assertEquals(20, $age);
    }

    public function test_set_and_get_age_in_session(): void
    {
        // Set age in session
        $this->ageService->setAge(25, false);

        // Get age from session
        $age = $this->ageService->getAge();

        $this->assertEquals(25, $age);
        $this->assertTrue($this->ageService->hasAgeSet());
    }

    public function test_set_age_with_remember(): void
    {
        // Set age with remember flag
        $this->ageService->setAge(30, true);

        // Verify age is set
        $age = $this->ageService->getAge();

        $this->assertEquals(30, $age);
        $this->assertTrue($this->ageService->hasAgeSet());

        // Note: Cookie testing would require additional setup with Http tests
    }

    public function test_get_age_from_cookie_fallback(): void
    {
        // Simulate cookie being set but not session
        $this->app['session']->forget('user_age');

        // Set the cookie in the request
        $this->app['request']->cookies->set('user_age', 28);

        // Get age should sync from cookie to session
        $age = $this->ageService->getAge();

        $this->assertEquals(28, $age);
        $this->assertEquals(28, $this->app['session']->get('user_age'));
    }

    public function test_has_age_set_when_not_set(): void
    {
        $this->app['session']->forget('user_age');
        $this->app['cookie']->forget('user_age');

        $this->assertFalse($this->ageService->hasAgeSet());
        $this->assertNull($this->ageService->getAge());
    }

    public function test_calculate_age_boundary_today(): void
    {
        $service = app(AgeVerificationService::class);
        $dob = now()->subYears(18)->format('Y-m-d');

        $this->assertEquals(18, $service->calculateAge($dob));
    }

    public function test_calculate_age_boundary_tomorrow(): void
    {
        $service = app(AgeVerificationService::class);
        // Birthday is tomorrow, should still be 17
        $dob = now()->subYears(18)->addDay()->format('Y-m-d');

        $this->assertEquals(17, $service->calculateAge($dob));
    }
}
