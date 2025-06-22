<?php

namespace Tests\Feature\Livewire\StoryComment;

use App\Livewire\StoryComment\StoryCommentCard;
use App\Models\Permission;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StoryCommentCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_mounts_correctly_and_sets_properties(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        Livewire::actingAs($user);
        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertSet('storyComment.id', $storyComment->id);
        $component->assertSet('showReplyButton', true);
        $component->assertSet('showActions', true);
        $component->assertSet('hasUserReplied', false); // No reply from this user yet

        // Test with a user who has replied
        $replier = User::factory()->create();
        StoryComment::factory()->for($replier, 'creator')->for($storyComment, 'parent')->create(); // User replies to the comment

        Livewire::actingAs($replier);
        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertSet('hasUserReplied', true);
    }

    public function test_delete_action_is_permitted_for_comment_creator(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();
        $this->actingAs($user);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionVisible('delete');
        $component->assertActionEnabled('delete');
    }

    public function test_delete_action_is_permitted_for_authorized_users(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $admin = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story::comment']);
        $admin->givePermissionTo('view_all_story::comment');
        $this->actingAs($admin);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionVisible('delete');
        $component->assertActionEnabled('delete');
    }

    public function test_delete_action_is_not_permitted_for_unauthorized_users(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionHidden('delete');
        $component->assertActionDisabled('delete');
    }

    public function test_edit_action_is_permitted_for_comment_creator(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();
        $this->actingAs($user);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionVisible('edit');
        $component->assertActionEnabled('edit');
    }

    public function test_edit_action_is_permitted_for_authorized_users(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $admin = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story::comment']);
        $admin->givePermissionTo('view_all_story::comment');
        $this->actingAs($admin);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionVisible('edit');
        $component->assertActionEnabled('edit');
    }

    public function test_edit_action_is_not_permitted_for_unauthorized_users(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->assertActionHidden('edit');
        $component->assertActionDisabled('edit');
    }

    public function test_delete_action_dispatches_event_on_successful_delete(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $admin = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story::comment']);
        $admin->givePermissionTo('view_all_story::comment');
        $this->actingAs($admin);

        $component = Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment]);
        $component->callAction('delete'); // Call the delete action
        $component->assertDispatched('storyCommentDeleted'); // Assert the event was dispatched

        $this->assertDatabaseMissing('story_comments', ['id' => $storyComment->id]); // Verify comment was deleted
    }

    public function test_edit_action_returns_correct_url(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->for($user, 'creator')->for($story)->create();

        $admin = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story::comment']);
        $admin->givePermissionTo('view_all_story::comment');
        $this->actingAs($admin);

        Livewire::test(StoryCommentCard::class, ['storyComment' => $storyComment])
            ->call('editAction')
            ->assertSet('storyComment.id', $storyComment->id);
    }
}
