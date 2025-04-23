<?php

namespace Tests\Feature\Models;

use App\Enums\Story\Status;
use App\Enums\Vote\Type;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, Story>
     */
    private function createStories(): array
    {
        return [
            'publishedStoryPast' => Story::factory()->create([
                'status' => Status::Publish,
                'published_at' => Carbon::now()->subDay(),
            ]),
            'pendingStory' => Story::factory()->create([
                'status' => Status::Publish,
                'published_at' => Carbon::now()->addDay(),
            ]),
            'draftStory' => Story::factory()->create([
                'status' => Status::Draft,
                'published_at' => Carbon::now()->subDay(),
            ]),
        ];
    }

    /**
     * @return array<string, array<int|string>>
     */
    public static function formattingProvider(): array
    {
        return [
            'less than 1000' => [999, '999'],
            'exactly 1000' => [1000, '1K'],
            'thousands with decimal' => [1300, '1.3K'],
            'thousands with whole number' => [2500, '2.5K'],
            'thousands just under 1000K' => [999000, '999K'],
            'just under 1 million' => [999999, '999.9K'],
            'exactly 1 million' => [1000000, '1M'],
            'millions with decimal' => [1500000, '1.5M'],
            'millions with whole number' => [5000000, '5M'],
            'millions just under 1000M' => [999000000, '999M'],
            'just under 1 billion' => [999999999, '999.9M'],
            'exactly 1 billion' => [1000000000, '1B'],
            'billions with decimal' => [1200000000, '1.2B'],
            'billions just under 1000B' => [999000000000, '999B'],
            'just under 1 trillion' => [999999999999, '999.9B'],
            'exactly 1 trillion' => [1000000000000, '1T'],
            'trillions with decimal' => [2500000000000, '2.5T'],
            'zero' => [0, '0'],
        ];
    }

    /**
     * Test the currentUserVote method.
     */
    public function test_current_user_vote(): void
    {
        /** @var Story */
        $story = Story::factory()->create();
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();

        // Test when no user is authenticated
        $this->assertNull($story->currentUserVote());

        // Authenticate user1
        $this->actingAs($user1);

        // Test when authenticated user has not voted
        $currentUserVote = $story->currentUserVote();
        $this->assertNull($currentUserVote); // @phpstan-ignore-line

        // Create a vote for user1
        $vote1 = Vote::factory()->for($story)->for($user1, 'creator')->create(['type' => Type::Up]);

        // Test when authenticated user has voted
        $currentUserVote = $story->currentUserVote();
        $this->assertNotNull($currentUserVote); // @phpstan-ignore-line
        $this->assertEquals($vote1->id, $currentUserVote->id);
        $this->assertEquals(Type::Up, $currentUserVote->type);

        // Create a vote for user2 (should not affect user1's view)
        Vote::factory()->for($story)->for($user2, 'creator')->create(['type' => Type::Down]);

        // Test that currentUserVote still returns user1's vote
        $currentUserVote = $story->currentUserVote();
        $this->assertNotNull($currentUserVote); // @phpstan-ignore-line
        $this->assertEquals($vote1->id, $currentUserVote->id);
        $this->assertEquals(Type::Up, $currentUserVote->type);

        // Log out user1
        $this->actingAs($user2); // Authenticate user2 instead

        // Test that currentUserVote now returns user2's vote
        $currentUserVote = $story->currentUserVote();
        $this->assertNotNull($currentUserVote); // @phpstan-ignore-line
        $this->assertEquals(Type::Down, $currentUserVote->type);

        // Log out user2
        Auth::logout();

        // Test when no user is authenticated again
        $this->assertNull($story->currentUserVote());
    }

    /**
     * Test that formattedDownvoteCount formats the downvote count correctly with suffixes.
     *
     * @dataProvider formattingProvider
     */
    public function test_formatted_downvote_count_formats_correctly(int $count, string $expectedFormat): void
    {
        $story = Story::factory()->create(['downvote_count' => $count]);

        $this->assertEquals($expectedFormat, $story->formattedDownvoteCount());
    }

    /**
     * Test that formattedUpvoteCount formats the upvote count correctly with suffixes.
     *
     * @dataProvider formattingProvider
     */
    public function test_formatted_upvote_count_formats_correctly(int $count, string $expectedFormat): void
    {
        $story = Story::factory()->create(['upvote_count' => $count]);

        $this->assertEquals($expectedFormat, $story->formattedUpvoteCount());
    }

    /**
     * Test that formattedViewCount formats the view count correctly with suffixes.
     *
     * @dataProvider formattingProvider
     */
    public function test_formatted_view_count_formats_correctly(int $viewCount, string $expectedFormat): void
    {
        $story = Story::factory()->create(['view_count' => $viewCount]);

        $this->assertEquals($expectedFormat, $story->formattedViewCount());
    }

    /**
     * Test that formattedVoteCount formats the total vote count correctly with suffixes.
     *
     * @dataProvider formattingProvider
     */
    public function test_formatted_vote_count_formats_correctly(int $count, string $expectedFormat): void
    {
        $story = Story::factory()->create(['vote_count' => $count]);

        $this->assertEquals($expectedFormat, $story->formattedVoteCount());
    }

    public function test_increment_view_count_protected_by_session_and_time(): void
    {
        // Use Carbon to control time during the test
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $story = Story::factory()->create(['view_count' => 0]);

        // 1. First call: Should increment
        $story->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story->fresh()?->view_count, 'First call should increment');
        $this->assertArrayHasKey($story->id, $viewedStories, 'Session should record first view');
        $this->assertEquals($now->timestamp, $viewedStories[$story->id], 'Session timestamp should be recorded');

        // 2. Second call immediately: Should NOT increment (session protection)
        $story->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story->fresh()?->view_count, 'Second call immediately should not increment');
        $this->assertEquals($now->timestamp, $viewedStories[$story->id], 'Session timestamp should not change on immediate second call');

        // 3. Advance time by less than 60 seconds (e.g., 30 seconds)
        Carbon::setTestNow($now->copy()->addSeconds(30));
        $story = $story->fresh(); // Reload the model to ensure state is fresh if needed, though increment updates it

        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story?->fresh()?->view_count, 'Call after < 60s should not increment');
        $this->assertEquals($now->timestamp, $viewedStories[$story?->id], 'Session timestamp should not change after < 60s');

        // 4. Advance time by more than 60 seconds (e.g., 61 seconds from the *original* $now)
        Carbon::setTestNow($now->copy()->addSeconds(61));
        $story = $story?->fresh(); // Reload

        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(2, $story?->fresh()?->view_count, 'Call after > 60s should increment');
        $this->assertEquals(Carbon::now()->timestamp, $viewedStories[$story?->id], 'Session timestamp should be updated after > 60s');

        // 5. Fifth call immediately after the time elapsed increment: Should NOT increment (session protection resets)
        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(2, $story?->fresh()?->view_count, 'Fifth call immediately should not increment');
        $this->assertEquals(Carbon::now()->timestamp, $viewedStories[$story?->id], 'Session timestamp should not change on immediate fifth call');

        // Clean up Carbon test time
        Carbon::setTestNow(null);
    }

    public function test_only_returns_published_stories(): void
    {
        $stories = $this->createStories();
        $publishedStories = Story::published()->get();

        $this->assertCount(1, $publishedStories);
        $this->assertTrue($publishedStories->contains($stories['publishedStoryPast']));
        $this->assertFalse($publishedStories->contains($stories['pendingStory']));
        $this->assertFalse($publishedStories->contains($stories['draftStory']));
    }

    public function test_only_returns_pending_stories(): void
    {
        $stories = $this->createStories();
        $pendingStories = Story::pending()->get();

        $this->assertCount(1, $pendingStories);
        $this->assertTrue($pendingStories->contains($stories['pendingStory']));
        $this->assertFalse($pendingStories->contains($stories['publishedStoryPast']));
        $this->assertFalse($pendingStories->contains($stories['draftStory']));
    }

    /**
     * Test the orderByVoteScore scope.
     */
    public function test_scope_order_by_vote_score(): void
    {
        // Create stories with different vote scores
        $story1 = Story::factory()->create(['vote_score' => 10.5]);
        $story2 = Story::factory()->create(['vote_score' => -5.2]);
        $story3 = Story::factory()->create(['vote_score' => 20.0]);
        $story4 = Story::factory()->create(['vote_score' => 0.0]);

        // Test default (descending) order
        $storiesDesc = Story::orderByVoteScore()->get();
        $this->assertEquals($story3->id, $storiesDesc->get(0)?->id); // 20.0
        $this->assertEquals($story1->id, $storiesDesc->get(1)?->id); // 10.5
        $this->assertEquals($story4->id, $storiesDesc->get(2)?->id); // 0.0
        $this->assertEquals($story2->id, $storiesDesc->get(3)?->id); // -5.2

        // Test ascending order
        $storiesAsc = Story::orderByVoteScore('asc')->get();
        $this->assertEquals($story2->id, $storiesAsc->get(0)?->id); // -5.2
        $this->assertEquals($story4->id, $storiesAsc->get(1)?->id); // 0.0
        $this->assertEquals($story1->id, $storiesAsc->get(2)?->id); // 10.5
        $this->assertEquals($story3->id, $storiesAsc->get(3)?->id); // 20.0
    }

    /**
     * Test the orderByUpvotes scope.
     */
    public function test_scope_order_by_upvotes(): void
    {
        // Create stories with different upvote counts
        $story1 = Story::factory()->create(['upvote_count' => 5]);
        $story2 = Story::factory()->create(['upvote_count' => 15]);
        $story3 = Story::factory()->create(['upvote_count' => 0]);
        $story4 = Story::factory()->create(['upvote_count' => 10]);

        // Test default (descending) order
        $storiesDesc = Story::orderByUpvotes()->get();
        $this->assertEquals($story2->id, $storiesDesc->get(0)?->id); // 15
        $this->assertEquals($story4->id, $storiesDesc->get(1)?->id); // 10
        $this->assertEquals($story1->id, $storiesDesc->get(2)?->id); // 5
        $this->assertEquals($story3->id, $storiesDesc->get(3)?->id); // 0

        // Test ascending order
        $storiesAsc = Story::orderByUpvotes('asc')->get();
        $this->assertEquals($story3->id, $storiesAsc->get(0)?->id); // 0
        $this->assertEquals($story1->id, $storiesAsc->get(1)?->id); // 5
        $this->assertEquals($story4->id, $storiesAsc->get(2)?->id); // 10
        $this->assertEquals($story2->id, $storiesAsc->get(3)?->id); // 15
    }

    /**
     * Test the orderByDownvotes scope.
     */
    public function test_scope_order_by_downvotes(): void
    {
        // Create stories with different downvote counts
        $story1 = Story::factory()->create(['downvote_count' => 8]);
        $story2 = Story::factory()->create(['downvote_count' => 2]);
        $story3 = Story::factory()->create(['downvote_count' => 12]);
        $story4 = Story::factory()->create(['downvote_count' => 0]);

        // Test default (descending) order
        $storiesDesc = Story::orderByDownvotes()->get();
        $this->assertEquals($story3->id, $storiesDesc->get(0)?->id); // 12
        $this->assertEquals($story1->id, $storiesDesc->get(1)?->id); // 8
        $this->assertEquals($story2->id, $storiesDesc->get(2)?->id); // 2
        $this->assertEquals($story4->id, $storiesDesc->get(3)?->id); // 0

        // Test ascending order
        $storiesAsc = Story::orderByDownvotes('asc')->get();
        $this->assertEquals($story4->id, $storiesAsc->get(0)?->id); // 0
        $this->assertEquals($story2->id, $storiesAsc->get(1)?->id); // 2
        $this->assertEquals($story1->id, $storiesAsc->get(2)?->id); // 8
        $this->assertEquals($story3->id, $storiesAsc->get(3)?->id); // 12
    }

    public function test_story_can_have_many_voters(): void
    {
        // Arrange
        /** @var Story */
        $story = Story::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create(); // This user will not vote

        // Act
        // Simulate votes by creating Vote model instances
        Vote::create([
            'story_id' => $story->id,
            'creator_id' => $user1->id,
            'type' => Type::Up, // Specify a vote type
        ]);
        Vote::create([
            'story_id' => $story->id,
            'creator_id' => $user2->id,
            'type' => Type::Up, // Specify a vote type
        ]);

        // Reload the story to get the relationship data
        $story->load('voters');

        // Assert
        // Assert that the relationship is a BelongsToMany
        $this->assertInstanceOf(BelongsToMany::class, $story->voters());

        // Assert that the relationship is related to the User model
        $this->assertInstanceOf(User::class, $story->voters()->getRelated());

        // Assert that the relationship uses the correct pivot table
        $this->assertEquals('votes', $story->voters()->getTable());

        // Assert that the relationship uses the correct foreign keys
        $this->assertEquals('story_id', $story->voters()->getForeignPivotKeyName());
        $this->assertEquals('creator_id', $story->voters()->getRelatedPivotKeyName());

        // Assert that the story has the correct number of voters
        $this->assertCount(2, $story->voters);

        // Assert that the correct users are associated as voters
        $this->assertTrue($story->voters->contains($user1));
        $this->assertTrue($story->voters->contains($user2));
        $this->assertFalse($story->voters->contains($user3));

        // Assert that pivot timestamps exist (these come from the Vote model's timestamps)
        /** @var ?Vote */
        $voter1Pivot = $story->voters->find($user1->id)?->pivot; // @phpstan-ignore-line
        /** @var ?Vote */
        $voter2Pivot = $story->voters->find($user2->id)?->pivot; // @phpstan-ignore-line

        $this->assertNotNull($voter1Pivot?->created_at);
        $this->assertNotNull($voter1Pivot->updated_at);
        $this->assertNotNull($voter2Pivot?->created_at);
        $this->assertNotNull($voter2Pivot->updated_at);
    }

    public function test_story_has_many_votes(): void
    {
        $story = Story::factory()->create();
        $votes = Vote::factory()->count(3)->for($story)->create();

        $this->assertInstanceOf(Collection::class, $story->votes);
        $this->assertCount(3, $story->votes);
        $this->assertTrue($story->votes->contains($votes->firstOrFail()));
    }

    /**
     * Test the updateVoteCountsAndScore method with fractional penalty.
     */
    public function test_update_vote_counts_and_score_with_penalty(): void
    {
        $story = Story::factory()->create([
            'upvote_count' => 0,
            'downvote_count' => 0,
            'vote_count' => 0,
            'vote_score' => 0,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user4 = User::factory()->create();

        // Add some votes
        Vote::factory()->for($story)->for($user1, 'creator')->create(['type' => Type::Up]); // +1
        Vote::factory()->for($story)->for($user2, 'creator')->create(['type' => Type::Up]); // +1
        Vote::factory()->for($story)->for($user3, 'creator')->create(['type' => Type::Down]); // -1.1
        Vote::factory()->for($story)->for($user4, 'creator')->create(['type' => Type::Down]); // -1.1

        // Expected counts and score (assuming penalty weight 1.1)
        $expectedUpvotes = 2;
        $expectedDownvotes = 2;
        $expectedVoteCount = 4;
        $expectedVoteScore = $expectedUpvotes - ($expectedDownvotes * 1.1); // 2 - (2 * 1.1) = 2 - 2.2 = -0.2

        // Call the method
        $story->updateVoteCountsAndScore();

        // Reload the story to get updated values from DB
        $story->refresh();

        // Assert the counts and score are updated correctly
        $this->assertEquals($expectedUpvotes, $story->upvote_count);
        $this->assertEquals($expectedDownvotes, $story->downvote_count);
        $this->assertEquals($expectedVoteCount, $story->vote_count);
        // Use round for float comparison to match database precision (decimal 8, 2)
        $this->assertEquals(round($expectedVoteScore, 2), round($story->vote_score, 2));
    }

    /**
     * Test the vote method scenarios.
     */
    public function test_vote_method(): void
    {
        /** @var Story */
        $story = Story::factory()->create();
        /** @var User */
        $user = User::factory()->create();

        // Scenario 1: Voting while unauthenticated - Should do nothing
        Auth::logout();
        $story->vote(Type::Up);
        $this->assertDatabaseCount('votes', 0);
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0.0, $story->vote_score); // Score should be 0.0

        // Authenticate the user for subsequent tests
        $this->actingAs($user);

        // Scenario 2: Upvoting for the first time
        $story->vote(Type::Up);
        $this->assertDatabaseHas('votes', [
            'story_id' => $story->id,
            'creator_id' => $user->id,
            'type' => Type::Up,
        ]);
        $this->assertDatabaseCount('votes', 1);
        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(1, $story->vote_count);
        $this->assertEquals(1.0, $story->vote_score); // 1 - (0 * 1.1) = 1

        // Scenario 3: Changing vote from Up to Down
        $story->vote(Type::Down);
        $this->assertDatabaseHas('votes', [
            'story_id' => $story->id,
            'creator_id' => $user->id,
            'type' => Type::Down, // Type should be updated
        ]);
        $this->assertDatabaseCount('votes', 1); // Still only one vote for this user/story
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(1, $story->downvote_count);
        $this->assertEquals(1, $story->vote_count);
        $this->assertEquals(-1.1, $story->vote_score); // 0 - (1 * 1.1) = -1.1

        // Scenario 4: Changing vote from Down to Up
        $story->vote(Type::Up);
        $this->assertDatabaseHas('votes', [
            'story_id' => $story->id,
            'creator_id' => $user->id,
            'type' => Type::Up, // Type should be updated back
        ]);
        $this->assertDatabaseCount('votes', 1);
        $story->refresh();
        $this->assertEquals(1, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(1, $story->vote_count);
        $this->assertEquals(1.0, $story->vote_score); // 1 - (0 * 1.1) = 1

        // Scenario 5: Removing an Upvote (passing null)
        $story->vote(null);
        $this->assertDatabaseMissing('votes', [ // Vote should be deleted
            'story_id' => $story->id,
            'creator_id' => $user->id,
        ]);
        $this->assertDatabaseCount('votes', 0);
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0.0, $story->vote_score); // Score should be 0.0

        // Scenario 6: Downvoting for the first time (resetting state)
        $story = Story::factory()->create(); // New story to reset state
        $this->actingAs($user); // Ensure user is still authenticated
        $story->vote(Type::Down);
        $this->assertDatabaseHas('votes', [
            'story_id' => $story->id,
            'creator_id' => $user->id,
            'type' => Type::Down,
        ]);
        $this->assertDatabaseCount('votes', 1);
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(1, $story->downvote_count);
        $this->assertEquals(1, $story->vote_count);
        $this->assertEquals(-1.1, $story->vote_score); // 0 - (1 * 1.1) = -1.1

        // Scenario 7: Removing a Downvote (passing null)
        $story->vote(null);
        $this->assertDatabaseMissing('votes', [ // Vote should be deleted
            'story_id' => $story->id,
            'creator_id' => $user->id,
        ]);
        $this->assertDatabaseCount('votes', 0);
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0.0, $story->vote_score); // Score should be 0.0

        // Scenario 8: Clicking the same vote type (Up) again should remove it
        $story = Story::factory()->create(); // New story
        $this->actingAs($user);
        $story->vote(Type::Up); // First Upvote
        $this->assertDatabaseCount('votes', 1);
        $story->refresh();
        $this->assertEquals(1.0, $story->vote_score);

        $story->vote(Type::Up); // Click Upvote again
        $this->assertDatabaseCount('votes', 0); // Vote should be removed
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0.0, $story->vote_score); // Score should be 0.0

        // Scenario 9: Clicking the same vote type (Down) again should remove it
        $story = Story::factory()->create(); // New story
        $this->actingAs($user);
        $story->vote(Type::Down); // First Downvote
        $this->assertDatabaseCount('votes', 1);
        $story->refresh();
        $this->assertEquals(-1.1, $story->vote_score);

        $story->vote(Type::Down); // Click Downvote again
        $this->assertDatabaseCount('votes', 0); // Vote should be removed
        $story->refresh();
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0.0, $story->vote_score); // Score should be 0.0
    }

    public function test_vote_related_attributes_are_guarded(): void
    {
        /** @var array<string, mixed> */
        $data = Story::factory()->make()->toArray();

        // Attempt to mass assign guarded attributes
        $data['upvote_count'] = 99;
        $data['downvote_count'] = 88;
        $data['vote_count'] = 77;
        $data['vote_score'] = 66;

        // Use create to test mass assignment protection
        $story = Story::create($data);

        // The guarded attributes should not be set to the attempted values
        $this->assertNotEquals(99, $story->upvote_count);
        $this->assertNotEquals(88, $story->downvote_count);
        $this->assertNotEquals(77, $story->vote_count);
        $this->assertNotEquals(66, $story->vote_score);

        // They should retain their default values (0 based on the migration)
        $this->assertEquals(0, $story->upvote_count);
        $this->assertEquals(0, $story->downvote_count);
        $this->assertEquals(0, $story->vote_count);
        $this->assertEquals(0, $story->vote_score);

        // Verify in the database as well
        $this->assertDatabaseHas('stories', [
            'id' => $story->id,
            'upvote_count' => 0,
            'downvote_count' => 0,
            'vote_count' => 0,
            'vote_score' => 0,
        ]);

        // Ensure other attributes were set correctly
        $locale = app()->getLocale();
        /** @var string[] */
        $titles = $data['title'];
        /** @var string[] */
        $contents = $data['content'];
        $this->assertEquals($titles[$locale], $story->title);
        $this->assertEquals($contents[$locale], $story->content);
    }
}
