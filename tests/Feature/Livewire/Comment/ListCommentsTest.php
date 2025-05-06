<?php

namespace Tests\Feature\Livewire\Comment;

use App\Livewire\Comment\ListComments;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class ListCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListComments::class, ['story' => $story])
            ->assertStatus(200);
    }

    public function test_displays_comments_for_the_given_story(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $otherStory = Story::factory()->create();

        // Comments for the target story
        $comments = Comment::factory()->count(3)->for($story)->create();
        // Comments for another story (should not be displayed)
        Comment::factory()->count(2)->for($otherStory)->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class, ['story' => $story]);
        $testable->assertCanSeeTableRecords($comments);
        $testable->assertCanNotSeeTableRecords(Comment::where('story_id', $otherStory->id)->get());
    }

    public function test_refreshes_table_when_comment_created_event_is_received(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create(['creator_id' => $user->id]);
        $initialCommentCount = 3;
        // Create some initial comments
        $initialComments = Comment::factory()->count($initialCommentCount)->for($story)->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(ListComments::class, ['story' => $story]);

        // Assert the initial comments are displayed
        $testable->assertCanSeeTableRecords($initialComments);

        // Create a new comment in the database
        $newComment = Comment::factory()->for($story)->create();

        // Get all comments for the story, including the new one
        $allComments = $story->comments()->get();

        // Dispatch the event
        $testable->dispatch('commentCreated');

        // Assert the table refreshes and now shows all comments, including the new one
        $testable->assertCanSeeTableRecords($allComments);
        // Optionally, assert the new comment specifically is visible
        $testable->assertCanSeeTableRecords([$newComment]);
    }
}
