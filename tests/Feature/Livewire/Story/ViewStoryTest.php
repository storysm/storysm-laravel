<?php

namespace Tests\Feature\Livewire\Story;

use App\Enums\Story\Status;
use App\Facades\AgeVerification;
use App\Livewire\Story\ViewStory;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use App\Scopes\StoryFilterScope;
use Artesaos\SEOTools\Facades\SEOTools;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class ViewStoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_view_story_component_renders_with_story(): void
    {
        AgeVerification::setAge(20);
        $story = Story::factory()
            ->ensurePublished()
            ->ensureHasAgeRating(18)
            ->create([
                'title' => 'Test Story Title',
                'content' => '<p>This is the test story content.</p>',
            ]);

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertViewIs('livewire.story.view-story')
            ->assertSee($story->title)
            ->assertSee(strip_tags($story->content));
    }

    public function test_view_story_component_sets_seo_metadata_without_cover(): void
    {
        $story = Story::factory()
            ->ensurePublished()
            ->create([
                'title' => 'Test Story Title',
                'content' => '<p>This is the test story content.</p>',
            ]);

        $expectedDescription = Str::limit(strip_tags($story->content), 160);

        SEOTools::shouldReceive('setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('setDescription')->once()->with($expectedDescription);

        SEOTools::shouldReceive('opengraph->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('opengraph->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('twitter->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('twitter->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('jsonLd->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setType')->once()->with('Article');

        $mockOpengraph = \Mockery::mock();
        $mockOpengraph->shouldNotReceive('addImage');

        $mockTwitter = \Mockery::mock();
        $mockTwitter->shouldNotReceive('addImage');

        $mockJsonLd = \Mockery::mock();
        $mockJsonLd->shouldNotReceive('addImage');

        Livewire::test(ViewStory::class, ['story' => $story]);
    }

    public function test_view_story_component_sets_seo_metadata_with_cover(): void
    {
        /** @var Story */
        $story = Story::factory()
            ->ensurePublished()
            ->create([
                'title' => 'Test Story Title',
                'content' => '<p>This is the test story content.</p>',
            ]);

        $originalPath = 'test.png';
        $image = Image::canvas(100, 100, 'ffffff');
        Storage::disk('public')->put($originalPath, $image->stream('png'));
        $media = Media::factory()->create([
            'name' => 'Test Image',
            'path' => $originalPath,
            'disk' => 'public',
            'size' => 1024,
            'type' => 'image/png',
            'ext' => 'png',
        ]);

        $story->coverMedia()->associate($media);
        $story->save();
        $coverImageUrl = $story->coverMedia?->url;

        $this->assertNotNull($coverImageUrl);
        $this->assertNotEmpty($coverImageUrl);

        $expectedDescription = Str::limit(strip_tags($story->content), 160);

        SEOTools::shouldReceive('setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('opengraph->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('opengraph->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('twitter->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('twitter->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('jsonLd->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setType')->once()->with('Article');

        SEOTools::shouldReceive('opengraph->addImage')->once()->with($coverImageUrl);
        SEOTools::shouldReceive('twitter->addImage')->once()->with($coverImageUrl);
        SEOTools::shouldReceive('jsonLd->addImage')->once()->with($coverImageUrl);

        Livewire::test(ViewStory::class, ['story' => $story]);
    }

    public function test_view_story_component_generates_breadcrumbs(): void
    {
        $story = Story::factory()->create([
            'title' => 'A Very Long Story Title That Needs Truncating For Breadcrumbs',
        ]);

        $component = new ViewStory;
        $component->story = $story;

        $breadcrumbs = $component->getBreadcrumbs();

        $this->assertCount(3, $breadcrumbs);

        $this->assertArrayHasKey(route('home'), $breadcrumbs);
        $this->assertArrayHasKey(route('stories.index'), $breadcrumbs);
        $this->assertArrayHasKey(0, $breadcrumbs); // The last item has key 0

        $this->assertEquals(__('navigation-menu.menu.home'), $breadcrumbs[route('home')]);
        $this->assertEquals(trans_choice('story.resource.model_label', 2), $breadcrumbs[route('stories.index')]);
        $this->assertEquals(Str::limit($story->title, 50), $breadcrumbs[0]);
    }

    public function test_allows_guest_to_view_published_story(): void
    {
        $story = Story::factory()
            ->ensurePublished()
            ->create();

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertStatus(200);
    }

    public function test_returns_404_for_guest_trying_to_view_draft_story(): void
    {
        $story = Story::factory()->create(['status' => Status::Draft]);

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertStatus(404);
    }

    public function test_allows_authenticated_user_to_view_published_story(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()
            ->ensurePublished()
            ->create();

        /** @var Testable */
        $testable = Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);
        $testable->assertStatus(200);
    }

    public function test_returns_404_for_authenticated_user_trying_to_view_draft_story_they_did_not_create_and_have_no_permission_for(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->create(['status' => Status::Draft]); // Created by another user

        /** @var Testable */
        $testable = Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);
        $testable->assertStatus(404);
    }

    public function test_allows_story_creator_to_view_their_own_draft_story(): void
    {
        $creator = User::factory()->create();
        $story = Story::factory()->create(['status' => Status::Draft, 'creator_id' => $creator->id]);

        /** @var Testable */
        $testable = Livewire::actingAs($creator)
            ->test(ViewStory::class, ['story' => $story]);
        $testable->assertStatus(200);
    }

    // Note: Test for user with 'view_all_story' permission is covered by the existing
    // test_view_story_component_generates_edit_action_for_user_with_view_all_permission

    public function test_view_story_component_generates_edit_action_when_authorized(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $story = Story::factory()->create([
            'creator_id' => $user->id,
        ]);

        $component = new ViewStory;
        $component->story = $story;

        $actions = $component->getActions();

        $this->assertCount(1, $actions);
        $action = $actions[0];

        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals('edit', $action->getName());
        $this->assertEquals(__('story.action.edit'), $action->getLabel());
        $this->assertEquals(
            route('filament.admin.resources.stories.edit', $story),
            $action->getUrl()
        );
        $this->assertTrue($action->isAuthorized()); // Assert the action is authorized
    }

    public function test_view_story_component_generates_edit_action_when_not_authorized(): void
    {
        $story = Story::factory()->create();

        $component = new ViewStory;
        $component->story = $story;

        $actions = $component->getActions();

        $this->assertCount(1, $actions);
        $action = $actions[0];

        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals('edit', $action->getName());
        $this->assertEquals(__('story.action.edit'), $action->getLabel());
        $this->assertEquals(
            route('filament.admin.resources.stories.edit', $story),
            $action->getUrl()
        );
        $this->assertFalse($action->isAuthorized()); // Assert the action is NOT authorized
    }

    public function test_view_story_component_generates_edit_action_for_user_with_view_all_permission(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_story']);

        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->givePermissionTo('view_all_story');

        // Create a story where the user is NOT the creator
        $story = Story::factory()->create();
        $this->assertNotEquals($user->id, $story->creator_id);

        $component = new ViewStory;
        $component->story = $story;

        $actions = $component->getActions();

        $this->assertCount(1, $actions);
        $action = $actions[0];

        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals('edit', $action->getName());
        $this->assertTrue($action->isAuthorized()); // Assert the action is authorized
    }

    public function test_refreshes_story_model_when_comment_created_event_is_received(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->ensurePublished()->create(['creator_id' => $user->id]);

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(ViewStory::class, ['story' => $story]);

        // Assert the initial StoryComment count is displayed
        $testable->assertSee($story->formattedCommentCount());

        // Simulate a new StoryComment being created by another component (or user)
        // This updates the database record for the story's StoryComment count
        StoryComment::factory()->create(['story_id' => $story->id, 'creator_id' => $user->id]);

        // The $story model instance currently held by the Livewire component
        // does NOT yet reflect the new StoryComment count from the database.

        // Dispatch the event that the ViewStory component listens for.
        // This should trigger the refreshStory method, which calls $this->story->refresh().
        $testable->dispatch('storyCommentCreated');

        // After the event and refresh, the $story model instance on the component
        // should now have the updated StoryComment count.
        // We need to refresh the $story object in the test scope to get the new count for assertion.
        $story->refresh();

        // Assert that the view now displays the incremented StoryComment count.
        // The component's view will re-render after the refresh.
        $testable->assertSee($story->formattedCommentCount());
    }

    public function test_view_count_is_conditionally_displayed(): void
    {
        AgeVerification::setAge(18);
        // Story with view count <= 500 (should not be displayed)
        $storyLowViews = Story::factory()
            ->ensurePublished()
            ->create(['view_count' => 499]);

        Livewire::test(ViewStory::class, ['story' => $storyLowViews])
            ->assertDontSeeHtml('<p class="text-sm">500</p>');

        // Story with view count > 500 (should be displayed)
        $storyHighViews = Story::factory()
            ->ensurePublished()
            ->create(['view_count' => 500]);

        Livewire::test(ViewStory::class, ['story' => $storyHighViews])
            ->assertSeeHtml('<p class="text-sm">501</p>');
    }

    public function test_increments_view_count_for_guest_visitors(): void
    {
        $story = Story::factory()->ensurePublished()->create(['view_count' => 0]);

        Livewire::test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(1, $story->fresh()?->view_count);
    }

    public function test_does_not_increment_view_count_for_the_author(): void
    {
        $author = User::factory()->create();
        $story = Story::factory()->ensurePublished()->create(['creator_id' => $author->id, 'view_count' => 0]);

        $this->actingAs($author);
        Livewire::test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(0, $story->fresh()?->view_count);
    }

    public function test_does_not_increment_view_count_for_users_with_act_as_guest_permission(): void
    {
        $privilegedUser = User::factory()->create();
        Permission::create(['name' => 'act_as_guest']);
        $privilegedUser->givePermissionTo('act_as_guest');
        $story = Story::factory()->ensurePublished()->create(['view_count' => 0]);

        $this->actingAs($privilegedUser);
        Livewire::test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(0, $story->fresh()?->view_count);
    }

    public function test_does_not_increment_view_count_for_users_with_view_all_story_permission(): void
    {
        $admin = User::factory()->create();
        Permission::create(['name' => 'view_all_story']);
        $admin->givePermissionTo('view_all_story');
        $story = Story::factory()->ensurePublished()->create(['view_count' => 0]);

        $this->actingAs($admin);
        Livewire::test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(0, $story->fresh()?->view_count);
    }

    public function test_increments_view_count_for_regular_authenticated_users(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->ensurePublished()->create(['view_count' => 0]);

        $this->actingAs($user);
        Livewire::test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(1, $story->fresh()?->view_count);
    }

    public function test_view_count_session_persistence_and_multi_role_logic(): void
    {
        // Setup a user who is both the creator and has admin permissions
        $adminCreator = User::factory()->create();
        Permission::firstOrCreate(['name' => 'view_all_story']);
        $adminCreator->givePermissionTo('view_all_story');

        $story = Story::factory()->ensurePublished()->create([
            'creator_id' => $adminCreator->id,
            'view_count' => 0,
        ]);

        // Multi-role check: Creator + Admin should not increment
        Livewire::actingAs($adminCreator)
            ->test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(0, $story->fresh()?->view_count);

        // Session Persistence check for regular user
        $user = User::factory()->create();
        Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);

        $this->assertEquals(1, $story->fresh()?->view_count);

        // Verify session structure: [id => timestamp]
        /** @var array<int, mixed> $viewedStories */
        $viewedStories = session()->get('viewed_stories');
        $this->assertArrayHasKey($story->id, $viewedStories);
        $this->assertIsInt($viewedStories[$story->id]);
    }

    public function test_view_count_throttle_integration_with_time_travel(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->ensurePublished()->create(['view_count' => 0]);
        $startTime = Carbon::now()->startOfMinute();

        Carbon::setTestNow($startTime);

        // First view at T+0s: Increment to 1
        Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);
        $this->assertEquals(1, $story->fresh()?->view_count);

        // Second view at T+30s: Should NOT increment
        Carbon::setTestNow($startTime->copy()->addSeconds(30));
        Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);
        $this->assertEquals(1, $story->fresh()?->view_count);

        // Third view at T+61s: Should increment to 2
        Carbon::setTestNow($startTime->copy()->addSeconds(61));
        Livewire::actingAs($user)
            ->test(ViewStory::class, ['story' => $story]);
        $this->assertEquals(2, $story->fresh()?->view_count);

        Carbon::setTestNow(); // Reset mock time
    }

    public function test_guest_without_age_sees_age_gate(): void
    {
        $story = Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        $component = Livewire::test(ViewStory::class, ['story' => $story]);
        $component->assertOk();
        // @phpstan-ignore-next-line
        $component->assertSeeLivewire('age-verification-form');
        $component->assertSet('isAgeVerified', false);
    }

    public function test_guest_too_young_is_redirected(): void
    {
        AgeVerification::setAge(13);

        $story = Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertRedirect(route('content.forbidden'));
    }

    public function test_guest_old_enough_sees_content(): void
    {
        AgeVerification::setAge(20);

        $story = Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create([
            'title' => 'Mature Story',
            'content' => 'This is mature content.',
        ]);

        $component = Livewire::test(ViewStory::class, ['story' => $story]);
        $component->assertOk();
        $component->assertSee('Mature Story');
        $component->assertSee('This is mature content.');
        $component->assertSet('isAgeVerified', true);
    }

    public function test_global_query_returns_all_stories_when_no_age_set(): void
    {
        Story::factory()->ensurePublished()->count(3)->ensureHasAgeRating(18)->create();

        $count = Story::withGlobalScope('filter', new StoryFilterScope)->count();
        $this->assertEquals(3, $count);
    }

    public function test_global_query_filters_stories_when_age_is_set(): void
    {
        AgeVerification::setAge(15);

        // Allowed
        Story::factory()->ensurePublished()->ensureHasAgeRating(12)->create();

        // Forbidden
        Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        $stories = Story::withGlobalScope('filter', new StoryFilterScope)->get();

        $this->assertCount(1, $stories);
        $this->assertEquals(12, $stories->first()?->age_rating_effective_value);
    }
}
