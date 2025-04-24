<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\Story\Status;
use App\Filament\Resources\StoryResource;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class StoryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_displays_pending_status_badge_when_story_is_published_in_the_future(): void
    {
        $futureDate = now()->addDay();

        Story::factory()->create([
            'title' => ['en' => 'Future Story'],
            'status' => Status::Publish,
            'published_at' => $futureDate,
            'creator_id' => $this->user->id,
        ]);

        $this->get(StoryResource::getUrl('index'))
            ->assertOk()
            ->assertSee(__('story.resource.status.pending'));
    }

    public function test_displays_the_actual_status_badge_when_story_is_not_published(): void
    {
        $story = Story::factory()->create([
            'title' => ['en' => 'Draft Story'],
            'status' => Status::Draft,
            'creator_id' => $this->user->id,
        ]);

        $this->get(StoryResource::getUrl('index'))
            ->assertOk()
            ->assertSee(ucfirst($story->status->value));
    }

    public function test_displays_the_actual_status_badge_when_story_is_published_in_the_past(): void
    {
        $pastDate = now()->subDay();

        $story = Story::factory()->create([
            'title' => ['en' => 'Published Story'],
            'status' => Status::Publish,
            'published_at' => $pastDate,
            'creator_id' => $this->user->id,
        ]);

        $this->get(StoryResource::getUrl('index'))
            ->assertOk()
            ->assertSee($story->status->value);
    }

    /**
     * Test that the upvote and downvote counts are displayed correctly in the table, using the formatted methods from the Story model.
     */
    public function test_displays_upvote_and_downvote_counts_in_table(): void
    {
        // Test with counts that might be formatted (e.g., > 999)
        // Create the story first, then update the guarded attributes
        $storyLargeVotes = Story::factory()->create(['title' => ['en' => 'Story with Many Votes'], 'creator_id' => $this->user->id]);
        $storyLargeVotes->upvote_count = 1234567;
        $storyLargeVotes->downvote_count = 98765;
        $storyLargeVotes->save();

        // Test with counts below 1000 (no formatting expected)
        // Create the story first, then update the guarded attributes
        $storySmallVotes = Story::factory()->create(['title' => ['en' => 'Story with Few Votes'], 'creator_id' => $this->user->id]);
        $storySmallVotes->upvote_count = 500;
        $storySmallVotes->downvote_count = 10;
        $storySmallVotes->save();

        // Test with zero votes
        // Create the story first, then update the guarded attributes
        $storyZeroVotes = Story::factory()->create(['title' => ['en' => 'Story with Zero Votes'], 'creator_id' => $this->user->id]);
        $storyZeroVotes->upvote_count = 0;
        $storyZeroVotes->downvote_count = 0;
        $storyZeroVotes->save();

        /** @var Testable */
        $testable = Livewire::test(ListStories::class);

        $testable->assertCanSeeTableRecords([$storyLargeVotes, $storySmallVotes, $storyZeroVotes]);

        // Assert the columns display the correct formatted values by calling the model methods
        $testable->assertTableColumnStateSet('upvote_count', $storyLargeVotes->formattedUpvoteCount(), $storyLargeVotes);
        $testable->assertTableColumnStateSet('downvote_count', $storyLargeVotes->formattedDownvoteCount(), $storyLargeVotes);

        $testable->assertTableColumnStateSet('upvote_count', $storySmallVotes->formattedUpvoteCount(), $storySmallVotes);
        $testable->assertTableColumnStateSet('downvote_count', $storySmallVotes->formattedDownvoteCount(), $storySmallVotes);

        $testable->assertTableColumnStateSet('upvote_count', $storyZeroVotes->formattedUpvoteCount(), $storyZeroVotes);
        $testable->assertTableColumnStateSet('downvote_count', $storyZeroVotes->formattedDownvoteCount(), $storyZeroVotes);
    }

    public function test_only_shows_stories_created_by_the_current_user_if_they_cannot_view_all(): void
    {
        $story = Story::factory()->create([
            'title' => ['en' => 'My Story'],
            'creator_id' => $this->user->id,
        ]);

        $otherStory = Story::factory()->create([
            'title' => ['en' => 'Other Story'],
            'creator_id' => User::factory()->create()->id,
        ]);

        $this->get(StoryResource::getUrl('index'))
            ->assertOk()
            ->assertSee($story->title)
            ->assertDontSee($otherStory->title);
    }

    public function test_shows_all_stories_if_the_user_can_view_all(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_story']);
        $this->user->givePermissionTo('view_all_story');

        $story = Story::factory()->create([
            'title' => ['en' => 'My Story'],
            'creator_id' => $this->user->id,
        ]);

        $otherStory = Story::factory()->create([
            'title' => ['en' => 'Other Story'],
            'creator_id' => User::factory()->create()->id,
        ]);

        $this->get(StoryResource::getUrl('index'))
            ->assertOk()
            ->assertSee($story->title)
            ->assertSee($otherStory->title);
    }

    public function test_renders_the_story_resource_table_with_view_action_and_record_url(): void
    {
        $this->actingAs($this->user);

        $story = Story::factory()->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStories::class);

        $testable->assertTableActionExists('view');
        $testable->assertTableActionHasUrl('view', route('stories.show', $story), $story);
        $testable->assertCanSeeTableRecords([$story]);

        /** @var ListStories */
        $instance = $testable->instance();
        $table = $instance->getTable();

        $this->assertEquals($table->getRecordUrl($story), route('filament.admin.resources.stories.edit', $story));
    }
}
