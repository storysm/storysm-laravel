<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\Story\Status;
use App\Filament\Resources\StoryResource;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
