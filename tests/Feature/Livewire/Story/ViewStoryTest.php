<?php

namespace Tests\Feature\Livewire\Story;

use App\Enums\Story\Status;
use App\Livewire\Story\ViewStory;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $story = Story::factory()
            ->ensurePublished()
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
}
