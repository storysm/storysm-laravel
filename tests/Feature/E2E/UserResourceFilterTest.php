<?php

namespace Tests\Feature\Filament\E2E;

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Filament\Resources\UserResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $guestUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $permission = Permission::firstOrCreate(['name' => Permissions::ACT_AS_GUEST_USER, 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        // Create an admin user with all permissions
        $this->adminUser = User::factory()->create();
        Config::set('auth.super_users', [$this->adminUser->email]);

        // Create a guest user
        $this->guestUser = User::factory()->create();
        $this->guestUser->assignRole($role);

        // Create a regular user (without guest role)
        $this->regularUser = User::factory()->create();
    }

    public function test_can_filter_users_by_author_type(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(UserResource\Pages\ListUsers::class);

        // 1. Assert both users are visible initially
        $livewire->assertCanSeeTableRecords([$this->guestUser, $this->regularUser]);

        // 2. Apply 'Guest Users' filter and assert only guest user is visible
        $livewire->filterTable('user_type', true);
        $livewire->assertCanSeeTableRecords([$this->guestUser]);
        $livewire->assertCanNotSeeTableRecords([$this->regularUser]);

        // 3. Apply 'Regular Users' filter and assert only regular user is visible
        $livewire->filterTable('user_type', false);
        $livewire->assertCanSeeTableRecords([$this->regularUser]);
        $livewire->assertCanNotSeeTableRecords([$this->guestUser]);

        // 4. Clear the filter and assert both users are visible
        $livewire->filterTable('user_type', null); // Clear filter
        $livewire->assertCanSeeTableRecords([$this->guestUser, $this->regularUser]);
    }
}
