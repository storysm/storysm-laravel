<?php

use App\Constants\Roles;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_created_with_guest_state_has_the_guest_role(): void
    {
        // Ensure the 'Guest' role exists in the database
        Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);

        $user = User::factory()->guest()->create();

        $this->assertTrue($user->hasRole(Roles::GUEST));
    }

    public function test_a_user_created_without_guest_state_does_not_have_the_guest_role(): void
    {
        // Ensure the 'Guest' role exists, but the user shouldn't get it
        Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);

        $user = User::factory()->create();

        $this->assertFalse($user->hasRole(Roles::GUEST));
    }

    #[RunInSeparateProcess]
    public function test_guest_state_throws_exception_if_guest_role_does_not_exist(): void
    {
        // Do NOT create the 'Guest' role in the database

        // Expect an exception when trying to create a user with the guest state
        $this->expectException(ModelNotFoundException::class);

        $user = User::factory()->guest()->create();
    }
}
