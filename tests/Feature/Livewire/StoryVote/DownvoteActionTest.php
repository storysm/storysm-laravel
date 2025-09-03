<?php

namespace Tests\Feature\Livewire\StoryVote;

use App\Enums\Vote\Type;
use App\Livewire\StoryVote\DownvoteAction;
use App\Models\Story;
use App\Models\StoryVote;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class DownvoteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_mounts_with_story(): void
    {
        $story = Story::factory()->create();
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story]);
        $testable->assertSet('story.id', $story->id);
    }

    public function test_authenticated_user_can_downvote_a_story(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story]);

        $testable->callAction('downvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseHas('story_votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->downvote_count);
    }

    public function test_authenticated_user_can_remove_their_downvote(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        StoryVote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->downvote_count);

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story]);

        $testable->callAction('downvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseMissing('story_votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->downvote_count);
    }

    public function test_authenticated_user_can_change_upvote_to_downvote(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        StoryVote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story]);

        $testable->callAction('downvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseHas('story_votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);
        $this->assertDatabaseMissing('story_votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(1, $story->downvote_count);
    }

    public function test_guest_cannot_downvote_and_receives_notification(): void
    {
        $story = Story::factory()->create();
        /** @var Testable */
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story])
            ->callAction('downvote');

        $testable->assertNotified(
            Notification::make()
                ->title(__('story-vote.notification.login_required.title'))
                ->body(__('story-vote.notification.login_required.body'))
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('login')
                        ->label(__('story-vote.notification.login_required.action.login'))
                        ->url(route('login'))
                        ->button(),
                ])
        );

        $this->assertDatabaseMissing('story_votes', [
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->downvote_count);
    }

    public function test_refresh_story_updates_story_property_when_id_matches(): void
    {
        $story = Story::factory()->create();
        $testable = Livewire::test(DownvoteAction::class, ['story' => $story]);

        // Simulate change in the database
        $story->downvote_count = 10; // Manually set for test simulation
        $story->save();
        $story->refresh();

        $testable->dispatch('vote-updated', storyId: $story->id);

        $testable->assertSet('story.downvote_count', 10);

        // Dispatch event for a different story
        $otherStory = Story::factory()->create();
        $testable->dispatch('vote-updated', storyId: $otherStory->id);

        // Assert the story property on the component is *not* refreshed
        $testable->assertSet('story.downvote_count', 10);
    }

    public function test_downvote_action_label_and_icon_reflect_current_user_vote_state(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        // Create 5 downvotes by other users to set an initial count
        StoryVote::factory()->count(5)->create([
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);
        $story->updateVoteCountsAndScore(); // Calculate initial counts
        $story->refresh(); // Refresh model to get updated counts

        $this->assertEquals(5, $story->downvote_count);
        $this->assertNull($story->currentUserVote()); // Ensure the current user has no vote initially

        // Test initial state (No vote by current user)
        /** @var Testable $testable */
        $testable = Livewire::actingAs($user)->test(DownvoteAction::class, ['story' => $story]);

        $testable->assertActionVisible('downvote');
        $testable->assertActionHasLabel('downvote', $story->formattedDownvoteCount());
        $testable->assertActionHasIcon('downvote', 'heroicon-o-hand-thumb-down'); // Outlined icon when not downvoted

        // Simulate user downvoting
        StoryVote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model to get updated counts and currentUserVote
        $this->assertEquals(6, $story->downvote_count);
        $this->assertNotNull($story->currentUserVote());
        $this->assertEquals(Type::Down, $story->currentUserVote()->type);

        // Dispatch event to notify the component of the change
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after downvoting
        $testable->assertActionVisible('downvote');
        $testable->assertActionHasLabel('downvote', $story->formattedDownvoteCount());
        $testable->assertActionHasIcon('downvote', 'heroicon-m-hand-thumb-down'); // Solid icon when downvoted

        // Simulate user changing to upvote
        StoryVote::where('creator_id', $user->id)->where('story_id', $story->id)->delete(); // Remove existing vote
        StoryVote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model
        $this->assertEquals(5, $story->downvote_count); // Downvote count goes back to 5
        $this->assertNotNull($story->currentUserVote());
        $this->assertEquals(Type::Up, $story->currentUserVote()->type);

        // Dispatch event
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after upvoting
        $testable->assertActionVisible('downvote');
        $testable->assertActionHasLabel('downvote', $story->formattedDownvoteCount()); // Label shows downvote count
        $testable->assertActionHasIcon('downvote', 'heroicon-o-hand-thumb-down'); // Outlined icon when not downvoted

        // Simulate user removing their vote (e.g., clicking upvote again)
        StoryVote::where('creator_id', $user->id)->where('story_id', $story->id)->delete(); // Remove existing vote
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model
        $this->assertEquals(5, $story->downvote_count); // Downvote count remains 5
        $this->assertNull($story->currentUserVote()); // No vote by current user

        // Dispatch event
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after removing vote
        $testable->assertActionVisible('downvote');
        $testable->assertActionHasLabel('downvote', $story->formattedDownvoteCount()); // Label shows downvote count
        $testable->assertActionHasIcon('downvote', 'heroicon-o-hand-thumb-down'); // Outlined icon when not downvoted
    }
}
