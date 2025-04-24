<?php

namespace Tests\Feature\Livewire\Vote;

use App\Enums\Vote\Type;
use App\Livewire\Vote\UpvoteAction;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class UpvoteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_mounts_with_story(): void
    {
        $story = Story::factory()->create();
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story]);
        $testable->assertSet('story.id', $story->id);
    }

    public function test_authenticated_user_can_upvote_a_story(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story]);

        $testable->callAction('upvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseHas('votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);
    }

    public function test_authenticated_user_can_remove_their_upvote(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story]);

        /** @var UpvoteAction $component */
        $component = $testable->instance();

        $testable->callAction('upvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseMissing('votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
    }

    public function test_authenticated_user_can_change_downvote_to_upvote(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(1, $story->downvote_count);

        $this->actingAs($user);

        /** @var Testable $testable */
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story]);

        $testable->callAction('upvote');

        $testable->assertHasNoErrors();
        $testable->assertDispatched('vote-updated', storyId: $story->id);

        $this->assertDatabaseHas('votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $this->assertDatabaseMissing('votes', [
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);

        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
    }

    public function test_guest_cannot_upvote_and_receives_notification(): void
    {
        $story = Story::factory()->create();
        /** @var Testable */
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story])
            ->callAction('upvote');

        $testable->assertNotified(
            Notification::make()
                ->title(__('vote.notification.login_required.title'))
                ->body(__('vote.notification.login_required.body'))
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('login')
                        ->label(__('vote.notification.login_required.action.login'))
                        ->url(route('login'))
                        ->button(),
                ])
        );

        $this->assertDatabaseMissing('votes', [
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);

        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
    }

    public function test_refresh_story_updates_story_property_when_id_matches(): void
    {
        // Create a story
        $story = Story::factory()->create();

        // Simulate initial state: 5 upvotes
        Vote::factory()->count(5)->create([
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $story->updateVoteCountsAndScore();
        $story->refresh(); // Refresh model to get updated counts from DB
        $this->assertEquals(5, $story->upvote_count);

        /** @var Testable $testable */
        $testable = Livewire::test(UpvoteAction::class, ['story' => $story]);
        $testable->assertSet('story.upvote_count', 5);

        // Simulate first change in the database: 15 upvotes
        Vote::where('story_id', $story->id)->delete(); // Remove existing votes
        Vote::factory()->count(15)->create([
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $story->updateVoteCountsAndScore();
        $story->refresh(); // Refresh model to get updated counts from DB
        $this->assertEquals(15, $story->upvote_count);

        // Dispatch event for the same story
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert the story property on the component is refreshed
        $testable->assertSet('story.upvote_count', 15);
        /** @var Story */
        $testableStory = $testable->get('story');
        $this->assertEquals(15, $testableStory->upvote_count); // Double check the object property

        // Dispatch event for a different story
        $otherStory = Story::factory()->create(); // Create a different story
        $testable->dispatch('vote-updated', storyId: $otherStory->id);

        // Assert the story property on the component is *not* refreshed
        $testable->assertSet('story.upvote_count', 15);
    }

    public function test_upvote_action_label_and_icon_reflect_current_user_vote_state(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        // Create 5 upvotes by other users to set an initial count
        Vote::factory()->count(5)->create([
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $story->updateVoteCountsAndScore(); // Calculate initial counts
        $story->refresh(); // Refresh model to get updated counts

        $this->assertEquals(5, $story->upvote_count);
        $this->assertNull($story->currentUserVote()); // Ensure the current user has no vote initially

        // Test initial state (No vote by current user)
        /** @var Testable $testable */
        $testable = Livewire::actingAs($user)->test(UpvoteAction::class, ['story' => $story]);

        $testable->assertActionVisible('upvote');
        $testable->assertActionHasLabel('upvote', $story->formattedUpvoteCount());
        $testable->assertActionHasIcon('upvote', 'heroicon-o-hand-thumb-up'); // Outlined icon when not upvoted

        // Simulate user upvoting
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Up,
        ]);
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model to get updated counts and currentUserVote
        $this->assertEquals(6, $story->upvote_count);
        $this->assertNotNull($story->currentUserVote());
        $this->assertEquals(Type::Up, $story->currentUserVote()->type);

        // Dispatch event to notify the component of the change
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after upvoting
        $testable->assertActionVisible('upvote');
        $testable->assertActionHasLabel('upvote', $story->formattedUpvoteCount());
        $testable->assertActionHasIcon('upvote', 'heroicon-m-hand-thumb-up'); // Solid icon when upvoted

        // Simulate user changing to downvote
        Vote::where('creator_id', $user->id)->where('story_id', $story->id)->delete(); // Remove existing vote
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'type' => Type::Down,
        ]);
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model
        $this->assertEquals(5, $story->upvote_count); // Upvote count goes back to 5
        $this->assertNotNull($story->currentUserVote());
        $this->assertEquals(Type::Down, $story->currentUserVote()->type);

        // Dispatch event
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after downvoting
        $testable->assertActionVisible('upvote');
        $testable->assertActionHasLabel('upvote', $story->formattedUpvoteCount()); // Label shows upvote count
        $testable->assertActionHasIcon('upvote', 'heroicon-o-hand-thumb-up'); // Outlined icon when not upvoted

        // Simulate user removing their vote (e.g., clicking downvote again)
        Vote::where('creator_id', $user->id)->where('story_id', $story->id)->delete(); // Remove existing vote
        $story->updateVoteCountsAndScore(); // Recalculate counts
        $story->refresh(); // Refresh model
        $this->assertEquals(5, $story->upvote_count); // Upvote count remains 5
        $this->assertNull($story->currentUserVote()); // No vote by current user

        // Dispatch event
        $testable->dispatch('vote-updated', storyId: $story->id);

        // Assert state after removing vote
        $testable->assertActionVisible('upvote');
        $testable->assertActionHasLabel('upvote', $story->formattedUpvoteCount()); // Label shows upvote count
        $testable->assertActionHasIcon('upvote', 'heroicon-o-hand-thumb-up'); // Outlined icon when not upvoted
    }
}
