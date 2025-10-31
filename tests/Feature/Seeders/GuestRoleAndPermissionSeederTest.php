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
        $guestRole = Role::where('name', Roles::GUEST)->first();
        $this->assertNotNull($guestRole, 'Guest role should exist.');

        // Assert that the 'act_as_guest' permission exists
        $actAsGuestPermission = Permission::where('name', Permissions::ACT_AS_GUEST)->first();
        $this->assertNotNull($actAsGuestPermission, 'act_as_guest permission should exist.');

        // Assert that the 'Guest' role has the 'act_as_guest' permission
        $this->assertTrue($guestRole->hasPermissionTo(Permissions::ACT_AS_GUEST), 'Guest role should have act_as_guest permission.');
    }
}
