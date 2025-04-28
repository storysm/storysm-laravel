<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CommentResource;
use App\Filament\Resources\CommentResource\Pages\ListComments;
use App\Models\Comment;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CommentResourceTest extends TestCase
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
        $this->get(CommentResource::getUrl('index'))->assertOk();
    }

    public function test_displays_comment_data_in_table(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'body' => 'Test Comment Body',
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
            'reply_count' => 5,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableColumnStateSet('body', $comment->body, $comment);
        $testable->assertTableColumnStateSet('story.title', $story->title, $comment);
        $testable->assertTableColumnStateSet('reply_count', $comment->reply_count, $comment);
    }

    public function test_displays_creator_name_column_if_user_can_view_all(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_comment']);
        $this->user->givePermissionTo('view_all_comment');

        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableColumnStateSet('creator.name', $this->user->name, $comment);
    }

    public function test_does_not_display_creator_name_column_if_user_cannot_view_all(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableColumnDoesNotExist('creator.name');
    }

    public function test_only_shows_comments_created_by_the_current_user_if_they_cannot_view_all(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'body' => ['en' => 'My Comment'],
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        $otherUser = User::factory()->create();
        $otherStory = Story::factory()->create(['creator_id' => $otherUser->id]);
        $otherComment = Comment::factory()->create([
            'body' => ['en' => 'Other Comment'],
            'story_id' => $otherStory->id,
            'creator_id' => $otherUser->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertCanNotSeeTableRecords([$otherComment]);
    }

    public function test_shows_all_comments_if_the_user_can_view_all(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_comment']);
        $this->user->givePermissionTo('view_all_comment');

        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'body' => 'My Comment',
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        $otherUser = User::factory()->create();
        $otherStory = Story::factory()->create(['creator_id' => $otherUser->id]);
        $otherComment = Comment::factory()->create([
            'body' => 'Other Comment',
            'story_id' => $otherStory->id,
            'creator_id' => $otherUser->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment, $otherComment]);
    }

    public function test_renders_the_comment_resource_table_with_actions(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableActionExists('view');
        $testable->assertTableActionExists('edit');
        $testable->assertTableActionExists('delete');
    }

    public function test_view_action_links_to_story_show_page(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableActionHasUrl('view', route('stories.show', $story), $comment);
    }

    public function test_renders_the_comment_resource_table_with_bulk_actions(): void
    {
        $story = Story::factory()->create(['creator_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'story_id' => $story->id,
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class);

        $testable->assertCanSeeTableRecords([$comment]);
        $testable->assertTableBulkActionExists('delete');
    }
}
