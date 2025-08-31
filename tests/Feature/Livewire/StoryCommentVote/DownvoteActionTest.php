<?php

namespace Tests\Feature\Livewire\StoryCommentVote;

use App\Enums\Vote\Type;
use App\Livewire\StoryCommentVote\DownvoteAction;
use App\Models\StoryComment;
use App\Models\StoryCommentVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DownvoteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_receives_login_required_notification_and_vote_is_not_cast(): void
    {
        $storyComment = StoryComment::factory()->create();

        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertNotDispatched('story-comment-vote-updated');
        $livewire->assertNotified();

        $this->assertDatabaseEmpty('story_comment_votes');
    }

    public function test_authenticated_user_can_successfully_cast_a_downvote_on_a_comment_they_havent_voted_on(): void
    {
        $user = User::factory()->create();
        $storyComment = StoryComment::factory()->create();

        $this->actingAs($user);

        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertDispatched('story-comment-vote-updated');

        $this->assertDatabaseHas('story_comment_votes', [
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Down,
        ]);

    }

    public function test_authenticated_user_can_successfully_retract_their_downvote(): void
    {
        $user = User::factory()->create();
        $storyComment = StoryComment::factory()->create();
        StoryCommentVote::factory()->create([
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Down,
        ]);

        $this->actingAs($user);

        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertDispatched('story-comment-vote-updated');

        $this->assertDatabaseMissing('story_comment_votes', [
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Down,
        ]);

    }

    public function test_authenticated_user_can_change_their_vote_from_an_upvote_to_a_downvote(): void
    {
        $user = User::factory()->create();
        $storyComment = StoryComment::factory()->create();
        StoryCommentVote::factory()->create([
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Up,
        ]);

        $this->actingAs($user);

        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertDispatched('story-comment-vote-updated');

        $this->assertDatabaseHas('story_comment_votes', [
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Down,
        ]);

        $this->assertDatabaseMissing('story_comment_votes', [
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Up,
        ]);

    }

    public function test_hitting_the_rate_limit_triggers_the_too_many_requests_notification(): void
    {
        $user = User::factory()->create();
        $storyComment = StoryComment::factory()->create();

        $this->actingAs($user);

        // Call the action 30 times to hit the rate limit
        for ($i = 0; $i < 30; $i++) {
            Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment])
                ->callAction('downvoteAction');
        }

        // The 31st call should trigger the rate limit notification
        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertNotDispatched('story-comment-vote-updated');
        $livewire->assertNotified();

        // Ensure no vote was cast on the 31st attempt
        $this->assertDatabaseEmpty('story_comment_votes');
    }

    public function test_the_story_comment_vote_updated_event_is_dispatched_after_a_vote_is_cast(): void
    {
        $user = User::factory()->create();
        $storyComment = StoryComment::factory()->create();

        $this->actingAs($user);

        $livewire = Livewire::test(DownvoteAction::class, ['storyComment' => $storyComment]);
        $livewire->callAction('downvoteAction');
        $livewire->assertDispatched('story-comment-vote-updated', storyCommentId: $storyComment->id);

        $this->assertDatabaseHas('story_comment_votes', [
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id,
            'type' => Type::Down,
        ]);
    }
}
