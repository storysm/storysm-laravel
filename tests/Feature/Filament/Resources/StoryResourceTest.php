<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\Story\Status;
use App\Filament\Resources\StoryResource;
use App\Filament\Resources\StoryResource\Pages\CreateStory;
use App\Filament\Resources\StoryResource\Pages\EditStory;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Models\Category;
use App\Models\Genre;
use App\Models\License;
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

    private User $adminUser;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Ensure permissions exist for Story resource
        foreach (StoryResource::getPermissionPrefixes() as $prefix) {
            Permission::firstOrCreate(['name' => $prefix.'_story']);
        }

        // Assign all story permissions to adminUser
        $this->adminUser->givePermissionTo(collect(StoryResource::getPermissionPrefixes())->map(fn ($prefix) => $prefix.'_story')->toArray());

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

    public function test_admin_user_can_create_story_with_genres(): void
    {
        $this->actingAs($this->adminUser);
        $genres = Genre::factory()->count(2)->create();

        $livewire = Livewire::test(CreateStory::class);
        $livewire->fillForm([
            'title' => [
                'en' => 'Test Story EN',
                'id' => 'Test Story ID',
            ],
            'description' => [
                'en' => 'Test Description EN',
                'id' => 'Test Description ID',
            ],
            'genres' => $genres->pluck('id')->toArray(),
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $story = Story::first();
        $this->assertNotNull($story);
        $this->assertCount(2, $story->genres);
        $this->assertTrue($story->genres->contains('id', $genres[0]?->id));
        $this->assertTrue($story->genres->contains('id', $genres[1]?->id));

        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[0]?->id,
        ]);
        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[1]?->id,
        ]);
    }

    public function test_admin_user_can_create_story_with_categories(): void
    {
        $this->actingAs($this->adminUser);
        $categories = Category::factory()->count(2)->create();

        $livewire = Livewire::test(CreateStory::class);
        $livewire->fillForm([
            'title' => [
                'en' => 'Test Story EN',
                'id' => 'Test Story ID',
            ],
            'description' => [
                'en' => 'Test Description EN',
                'id' => 'Test Description ID',
            ],
            'categories' => $categories->pluck('id')->toArray(),
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $story = Story::first();
        $this->assertNotNull($story);
        $this->assertCount(2, $story->categories);
        $this->assertTrue($story->categories->contains('id', $categories[0]?->id));
        $this->assertTrue($story->categories->contains('id', $categories[1]?->id));

        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[0]?->id,
        ]);
        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[1]?->id,
        ]);
    }

    public function test_admin_user_can_create_story_without_categories(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateStory::class);
        $livewire->fillForm([
            'title' => [
                'en' => 'Test Story Without Categories EN',
                'id' => 'Test Story Without Categories ID',
            ],
            'description' => [
                'en' => 'Test Description Without Categories EN',
                'id' => 'Test Description Without Categories ID',
            ],
            // Intentionally not setting 'categories' field
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $story = Story::first();
        $this->assertNotNull($story);
        $this->assertCount(0, $story->categories);
        $this->assertDatabaseMissing('category_story', [
            'story_id' => $story->id,
        ]);
    }

    public function test_admin_user_can_create_story_with_licenses(): void
    {
        $this->actingAs($this->adminUser);
        $licenses = License::factory()->count(2)->create();

        $livewire = Livewire::test(CreateStory::class);
        $livewire->fillForm([
            'title' => [
                'en' => 'Test Story with Licenses EN',
                'id' => 'Test Story with Licenses ID',
            ],
            'description' => [
                'en' => 'Test Description EN',
                'id' => 'Test Description ID',
            ],
            'licenses' => $licenses->pluck('id')->toArray(),
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $story = Story::latest()->first(); // Use latest() to get the newly created story
        $this->assertNotNull($story);
        $this->assertCount(2, $story->licenses);
        $this->assertTrue($story->licenses->contains('id', $licenses[0]?->id));
        $this->assertTrue($story->licenses->contains('id', $licenses[1]?->id));

        $this->assertDatabaseHas('license_story', [
            'story_id' => $story->id,
            'license_id' => $licenses[0]?->id,
        ]);
        $this->assertDatabaseHas('license_story', [
            'story_id' => $story->id,
            'license_id' => $licenses[1]?->id,
        ]);
    }

    public function test_admin_user_can_create_story_without_licenses(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateStory::class);
        $livewire->fillForm([
            'title' => [
                'en' => 'Test Story Without Licenses EN',
                'id' => 'Test Story Without Licenses ID',
            ],
            'description' => [
                'en' => 'Test Description Without Licenses EN',
                'id' => 'Test Description Without Licenses ID',
            ],
            // Intentionally not setting 'licenses' field
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $story = Story::latest()->first();
        $this->assertNotNull($story);
        $this->assertCount(0, $story->licenses);
        $this->assertDatabaseMissing('license_story', [
            'story_id' => $story->id,
        ]);
    }

    public function test_admin_user_can_update_story_licenses(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $initialLicense = License::factory()->create();
        $newLicenses = License::factory()->count(2)->create();
        $story->licenses()->attach($initialLicense);

        $this->assertCount(1, $story->licenses()->get());
        $this->assertDatabaseHas('license_story', ['story_id' => $story->id, 'license_id' => $initialLicense->id]);

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'licenses' => [
                $newLicenses[0]?->id,
                $newLicenses[1]?->id,
            ],
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(2, $story->licenses);
        $this->assertTrue($story->licenses->contains('id', $newLicenses[0]?->id));
        $this->assertTrue($story->licenses->contains('id', $newLicenses[1]?->id));
        $this->assertFalse($story->licenses->contains('id', $initialLicense->id));

        $this->assertDatabaseMissing('license_story', [
            'story_id' => $story->id,
            'license_id' => $initialLicense->id,
        ]);
        $this->assertDatabaseHas('license_story', [
            'story_id' => $story->id,
            'license_id' => $newLicenses[0]?->id,
        ]);
    }

    public function test_admin_user_can_remove_all_licenses_from_story(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $licenses = License::factory()->count(2)->create();
        $story->licenses()->attach($licenses->pluck('id'));

        $this->assertCount(2, $story->licenses()->get());
        $this->assertDatabaseHas('license_story', ['story_id' => $story->id, 'license_id' => $licenses[0]?->id]);

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        // Remove all licenses by passing an empty array
        $livewire->fillForm([
            'licenses' => [],
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(0, $story->licenses);

        $this->assertDatabaseMissing('license_story', [
            'story_id' => $story->id,
            'license_id' => $licenses[0]?->id,
        ]);
        $this->assertDatabaseMissing('license_story', [
            'story_id' => $story->id,
            'license_id' => $licenses[1]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_adding_genres(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $initialGenre = Genre::factory()->create();
        $story->genres()->attach($initialGenre);

        $newGenres = Genre::factory()->count(2)->create();

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'genres' => array_merge([$initialGenre->id], $newGenres->pluck('id')->toArray()),
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(3, $story->genres);
        $this->assertTrue($story->genres->contains($initialGenre));
        $this->assertTrue($story->genres->contains('id', $newGenres[0]?->id));
        $this->assertTrue($story->genres->contains('id', $newGenres[1]?->id));

        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $initialGenre->id,
        ]);
        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $newGenres[0]?->id,
        ]);
        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $newGenres[1]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_adding_categories(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $initialCategory = Category::factory()->create();
        $story->categories()->attach($initialCategory);

        $newCategories = Category::factory()->count(2)->create();

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'categories' => array_merge([$initialCategory->id], $newCategories->pluck('id')->toArray()),
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(3, $story->categories);
        $this->assertTrue($story->categories->contains($initialCategory));
        $this->assertTrue($story->categories->contains('id', $newCategories[0]?->id));
        $this->assertTrue($story->categories->contains('id', $newCategories[1]?->id));

        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $initialCategory->id,
        ]);
        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $newCategories[0]?->id,
        ]);
        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $newCategories[1]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_removing_genres(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $genres = Genre::factory()->count(3)->create();
        $story->genres()->attach($genres->pluck('id'));

        // Remove one genre
        $genresToKeep = [$genres[0]?->id, $genres[1]?->id];

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'genres' => $genresToKeep,
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(2, $story->genres);
        $this->assertTrue($story->genres->contains('id', $genres[0]?->id));
        $this->assertTrue($story->genres->contains('id', $genres[1]?->id));
        $this->assertFalse($story->genres->contains('id', $genres[2]?->id));

        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[0]?->id,
        ]);
        $this->assertDatabaseHas('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[1]?->id,
        ]);
        $this->assertDatabaseMissing('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[2]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_removing_categories(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $story->categories()->attach($categories->pluck('id'));

        // Remove one category
        $categoriesToKeep = [$categories[0]?->id, $categories[1]?->id];

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'categories' => $categoriesToKeep,
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(2, $story->categories);
        $this->assertTrue($story->categories->contains('id', $categories[0]?->id));
        $this->assertTrue($story->categories->contains('id', $categories[1]?->id));
        $this->assertFalse($story->categories->contains('id', $categories[2]?->id));

        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[0]?->id,
        ]);
        $this->assertDatabaseHas('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[1]?->id,
        ]);
        $this->assertDatabaseMissing('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[2]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_clearing_all_genres(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $story->genres()->attach($genres->pluck('id'));

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'genres' => [], // Clear all genres
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(0, $story->genres);
        $this->assertDatabaseMissing('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[0]?->id,
        ]);
        $this->assertDatabaseMissing('genre_story', [
            'story_id' => $story->id,
            'genre_id' => $genres[1]?->id,
        ]);
    }

    public function test_admin_user_can_update_story_by_clearing_all_categories(): void
    {
        $this->actingAs($this->adminUser);
        $story = Story::factory()->create();
        $categories = Category::factory()->count(2)->create();
        $story->categories()->attach($categories->pluck('id'));

        $livewire = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'categories' => [], // Clear all categories
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertCount(0, $story->categories);
        $this->assertDatabaseMissing('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[0]?->id,
        ]);
        $this->assertDatabaseMissing('category_story', [
            'story_id' => $story->id,
            'category_id' => $categories[1]?->id,
        ]);
    }
}
