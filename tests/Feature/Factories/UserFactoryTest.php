<?php

namespace Tests\Feature\Factories;

use App\Constants\Roles;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_created_with_guest_state_has_the_guest_role(): void
    {
        // Ensure the 'Guest' role exists in the database
        Role::create(['name' => Roles::GUEST, 'guard_name' => 'web']);

        $user = User::factory()->guest()->create();

        $this->assertTrue($user->hasRole(Roles::GUEST));
    }

    public function test_a_user_created_without_guest_state_does_not_have_the_guest_role(): void
    {
        // Ensure the 'Guest' role exists, but the user shouldn't get it
        Role::create(['name' => Roles::GUEST, 'guard_name' => 'web']);

        $user = User::factory()->create();

        $this->assertFalse($user->hasRole(Roles::GUEST));
    }

    public function test_guest_state_throws_exception_if_guest_role_does_not_exist(): void
    {
        // Do NOT create the 'Guest' role in the database

        // Expect an exception when trying to create a user with the guest state
        // The current implementation fails silently, so this test will initially fail.
        // This test is to guide the next step (002-03) to add explicit error handling.
        $this->expectException(\Exception::class); // This will be updated to ModelNotFoundException later

        User::factory()->guest()->create();
    }
}
