<?php

namespace Tests\Feature\Seeders;

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\GuestRoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRoleAndPermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_role_and_permission_are_created_and_linked(): void
    {
        // Run the seeder
        $this->seed(GuestRoleAndPermissionSeeder::class);

        // Assert that the 'Guest' role exists
        $this->assertSame(1, Role::where('name', Roles::GUEST)->count(), 'Guest role should exist.');

        // Assert that the 'act_as_guest' permission exists
        $this->assertSame(1, Permission::where('name', Permissions::ACT_AS_GUEST)->count(), 'act_as_guest permission should exist.');

        // Assert that the 'Guest' role has the 'act_as_guest' permission
        $guestRole = Role::where('name', Roles::GUEST)->first();
        $this->assertTrue($guestRole?->hasPermissionTo(Permissions::ACT_AS_GUEST), 'Guest role should have act_as_guest permission.');
    }

    public function test_seeder_is_idempotent(): void
    {
        // Run the seeder twice
        $this->seed(GuestRoleAndPermissionSeeder::class);
        $this->seed(GuestRoleAndPermissionSeeder::class);

        // Assert that only one 'Guest' role exists
        $this->assertSame(1, Role::where('name', Roles::GUEST)->count(), 'There should be exactly one Guest role.');

        // Assert that only one 'act_as_guest' permission exists
        $this->assertSame(1, Permission::where('name', Permissions::ACT_AS_GUEST)->count(), 'There should be exactly one act_as_guest permission.');
    }
}
