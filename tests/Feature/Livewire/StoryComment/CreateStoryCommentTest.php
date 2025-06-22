<?php

namespace Tests\Feature\Livewire\StoryComment;

use App\Livewire\StoryComment\CreateStoryComment;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CreateStoryCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateStoryComment::class, ['story' => $story])
            ->assertStatus(200);
    }

    public function test_can_create_a_comment(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(CreateStoryComment::class, ['story' => $story]);
        $testable->fillForm([
            'body' => ['en' => 'This is a test StoryComment.'],
        ]);
        $testable->call('createComment');
        $testable->assertHasNoFormErrors();
        $testable->assertNotified(
            Notification::make()
                ->title(__('story-comment.form.notification.created'))
                ->success()
        );

        $this->assertDatabaseHas('story_comments', [
            'story_id' => $story->id,
            'creator_id' => $user->id,
        ]);

        // Now, fetch the specific StoryComment record
        $storyComment = StoryComment::where('story_id', $story->id)
            ->where('creator_id', $user->id)
            ->first();

        // Assert that the StoryComment was found
        $this->assertNotNull($storyComment, 'Comment not found in the database.');

        $body = $storyComment->body;
        $this->assertEquals('This is a test StoryComment.', $body);
    }

    public function test_requires_content_to_create_a_comment(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(CreateStoryComment::class, ['story' => $story]);
        $testable->fillForm([
            'body' => null, // Empty content
        ]);
        $testable->call('createComment');
        $testable->assertHasFormErrors(['body.en']);
    }

    public function test_dispatches_comment_created_event_on_successful_creation(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(CreateStoryComment::class, ['story' => $story]);
        $testable->fillForm([
            'body' => ['en' => 'This is a test StoryComment.'],
        ]);
        $testable->call('createComment');
        $testable->assertDispatched('storyCommentCreated');
    }

    public function test_guest_cannot_create_a_comment(): void
    {
        $story = Story::factory()->create();

        // No actingAs() call
        /** @var Testable */
        $testable = Livewire::test(CreateStoryComment::class, ['story' => $story]);
        $testable->fillForm(['body' => ['en' => 'Guest comment']]);
        $testable->call('createComment');
        $testable->assertNotified(
            Notification::make()
                ->title(__('story-comment.form.section.description.login_required'))
                ->danger()
        );

        $this->assertDatabaseCount('story_comments', 0);
    }

    public function test_can_create_a_reply_to_another_comment(): void
    {
        $user = User::factory()->create();
        $parentComment = StoryComment::factory()->create();
        $story = $parentComment->story;

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(CreateStoryComment::class, ['storyComment' => $parentComment]);
        $testable->fillForm(['body' => ['en' => 'This is a reply.']]);
        $testable->call('createComment');
        $testable->assertHasNoFormErrors();

        $this->assertDatabaseHas('story_comments', [
            'body->en' => 'This is a reply.',
            'parent_id' => $parentComment->id,
            'story_id' => $story->id, // Important: ensures it's linked to the original story
        ]);

        $parentComment->refresh();
        $this->assertEquals(1, $parentComment->reply_count);

        $story->refresh();
        $this->assertEquals(2, $story->comment_count); // Parent + Reply
    }
}
