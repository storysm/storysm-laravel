<?php

namespace Tests\Feature\E2E;

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Enums\Story\Status;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestUserNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected User $guestUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $permission = Permission::firstOrCreate(['name' => Permissions::ACT_AS_GUEST_USER, 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        // Create users
        $this->guestUser = User::factory()->create();
        $this->guestUser->assignRole($role);

        $this->regularUser = User::factory()->create();

        $this->actingAs($this->regularUser);
    }

    public function test_guest_user_notice_is_displayed_for_guest_user(): void
    {
        // Create a story by a guest user
        $story = Story::factory()->create([
            'creator_id' => $this->guestUser->id,
            'status' => Status::Publish,
        ]);

        // Visit the story page
        $response = $this->get(route('stories.show', $story));

        // Assert that the guest user notice is present
        $response->assertSee(__('user.resource.guest_user_notice'));
    }

    public function test_guest_user_notice_is_not_displayed_for_regular_user(): void
    {
        // Create a story by a regular user
        $story = Story::factory()->create([
            'creator_id' => $this->regularUser->id,
            'status' => Status::Publish,
        ]);

        // Visit the story page
        $response = $this->get(route('stories.show', $story));

        // Assert that the guest user notice is absent
        $response->assertDontSee(__('user.resource.guest_user_notice'));
    }
}
