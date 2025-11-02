<?php

namespace Tests\Feature\E2E;

use App\Constants\Roles;
use App\Enums\Story\Status;
use App\Models\AgeRating;
use App\Models\Role;
use App\Models\Story;
use App\Models\User;
use Database\Seeders\GuestRoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GuestUserNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected User $guestUser;

    protected User $regularUser;

    protected AgeRating $ageRating;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles and Permissions
        $this->seed(GuestRoleAndPermissionSeeder::class);

        // Create users
        $this->guestUser = User::factory()->create();
        /** @var Role */
        $guestRole = Role::where('name', Roles::GUEST)->first();
        $this->guestUser->assignRole($guestRole);

        $this->regularUser = User::factory()->create();

        Config::set('age_rating.guest_limit_years', 16);

        $this->ageRating = AgeRating::factory()->create([
            'age_representation' => 7,
        ]);

        $this->actingAs($this->regularUser);
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

    public function test_guest_user_notice_is_displayed_for_unauthenticated_visitor_viewing_guest_user_story(): void
    {
        // Create a story by a guest user
        /** @var Story */
        $story = Story::factory()->create([
            'creator_id' => $this->guestUser->id,
            'status' => Status::Publish,
        ]);
        $story->ageRatings()->attach($this->ageRating);
        $story->save();

        // Act as unauthenticated user
        Auth::logout();

        // Visit the story page
        $response = $this->get(route('stories.show', $story));

        // Assert that the guest user notice is present
        $response->assertSee(__('user.resource.guest_user_notice'));
    }

    public function test_guest_user_notice_is_not_displayed_for_guest_user_story_in_draft_status(): void
    {
        // Create a story by a guest user in draft status
        $story = Story::factory()->create([
            'creator_id' => $this->guestUser->id,
            'status' => Status::Draft,
        ]);

        // Visit the story page
        $response = $this->get(route('stories.show', $story));

        // Assert that the guest user notice is absent
        $response->assertDontSee(__('user.resource.guest_user_notice'));
    }
}
