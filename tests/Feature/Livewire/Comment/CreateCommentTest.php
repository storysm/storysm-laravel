<?php

namespace Tests\Feature\Livewire\Comment;

use App\Livewire\StoryComment\CreateStoryComment;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Filament\Notifications\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
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
}
