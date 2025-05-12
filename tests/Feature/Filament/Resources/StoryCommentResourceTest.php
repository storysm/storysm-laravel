<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages\ListStoryComments;
use App\Filament\Resources\StoryCommentResource;
use App\Models\Permission;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class StoryCommentResourceTest extends TestCase
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

    public function test_renders_the_comment_resource_table(): void
    {
        $this->get(StoryCommentResource::getUrl('index'))->assertOk();
    }

    public function test_displays_comment_data_in_table(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'body' => 'Test StoryComment Body',
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
            'reply_count' => 5,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableColumnStateSet('body', $storyComment->body, $storyComment);
        $testable->assertTableColumnStateSet('story.title', $story->title, $storyComment);
        $testable->assertTableColumnStateSet('reply_count', $storyComment->reply_count, $storyComment);
    }

    public function test_displays_creator_name_column_if_user_can_view_all(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_comment']);
        $this->user->givePermissionTo('view_all_comment');

        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableColumnStateSet('creator.name', $this->user->name, $storyComment);
    }

    public function test_does_not_display_creator_name_column_if_user_cannot_view_all(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableColumnDoesNotExist('creator.name');
    }

    public function test_only_shows_comments_created_by_the_current_user_if_they_cannot_view_all(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'body' => ['en' => 'My StoryComment'],
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        $otherUser = User::factory()->create();
        $otherStory = Story::factory()->create(['creator_id' => $otherUser->id]);
        $otherComment = StoryComment::factory()->create([
            'body' => ['en' => 'Other StoryComment'],
            'story_id' => $otherStory->id,
            'creator_id' => $otherUser->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertCanNotSeeTableRecords([$otherComment]);
    }

    public function test_shows_all_comments_if_the_user_can_view_all(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_comment']);
        $this->user->givePermissionTo('view_all_comment');

        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'body' => 'My StoryComment',
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        $otherUser = User::factory()->create();
        $otherStory = Story::factory()->create(['creator_id' => $otherUser->id]);
        $otherComment = StoryComment::factory()->create([
            'body' => 'Other StoryComment',
            'story_id' => $otherStory->id,
            'creator_id' => $otherUser->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment, $otherComment]);
    }

    public function test_renders_the_comment_resource_table_with_actions(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableActionExists('view');
        $testable->assertTableActionExists('edit');
        $testable->assertTableActionExists('delete');
    }

    public function test_view_action_links_to_story_show_page(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableActionHasUrl('view', route('stories.show', $story), $storyComment);
    }

    public function test_renders_the_comment_resource_table_with_bulk_actions(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $storyComment = StoryComment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class);

        $testable->assertCanSeeTableRecords([$storyComment]);
        $testable->assertTableBulkActionExists('delete');
    }
}
