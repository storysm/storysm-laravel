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

        User::factory()->guest()->create();
    }

    #[RunInSeparateProcess]
    public function test_guest_role_query_is_cached_and_executed_only_once(): void
    {
        // Enable query logging
        \Illuminate\Support\Facades\DB::enableQueryLog();

        // Ensure the 'Guest' role exists
        Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);

        // Create multiple users with the guest state
        User::factory()->guest()->create();
        User::factory()->guest()->create();
        User::factory()->guest()->create();

        // Get all executed queries
        $queries = \Illuminate\Support\Facades\DB::getQueryLog();

        // Filter for queries related to fetching the 'Guest' role
        $guestRoleQueries = array_filter($queries, function ($query) {
            /** @var array<string, string> $query */
            return str_contains($query['query'], 'select * from "roles" where "name" = ? limit 1');
        });

        // Assert that the guest role query was executed exactly once
        $this->assertCount(1, $guestRoleQueries);

        // Disable query logging
        \Illuminate\Support\Facades\DB::disableQueryLog();
    }
}
