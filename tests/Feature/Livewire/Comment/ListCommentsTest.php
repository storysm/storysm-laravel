<?php

namespace Tests\Feature\Livewire\Comment;

use App\Livewire\StoryComment\ListStoryComments;
use App\Models\Story;
use App\Models\StoryComment;
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

        Livewire::test(ListStoryComments::class, ['story' => $story])
            ->assertStatus(200);
    }

    public function test_displays_comments_for_the_given_story(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create();
        $otherStory = Story::factory()->create();

        // StoryComments for the target story
        $storyComments = StoryComment::factory()->count(3)->for($story)->create();
        // StoryComments for another story (should not be displayed)
        StoryComment::factory()->count(2)->for($otherStory)->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class, ['story' => $story]);
        $testable->assertCanSeeTableRecords($storyComments);
        $testable->assertCanNotSeeTableRecords(StoryComment::where('story_id', $otherStory->id)->get());
    }

    public function test_refreshes_table_when_comment_created_event_is_received(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $story = Story::factory()->create(['creator_id' => $user->id]);
        $initialCommentCount = 3;
        // Create some initial StoryComments
        $initialComments = StoryComment::factory()->count($initialCommentCount)->for($story)->create();

        $this->actingAs($user);

        /** @var Testable */
        $testable = Livewire::test(ListStoryComments::class, ['story' => $story]);

        // Assert the initial StoryComments are displayed
        $testable->assertCanSeeTableRecords($initialComments);

        // Create a new StoryComment in the database
        $newComment = StoryComment::factory()->for($story)->create();

        // Get all StoryComments for the story, including the new one
        $allComments = $story->storyComments()->get();

        // Dispatch the event
        $testable->dispatch('commentCreated');

        // Assert the table refreshes and now shows all StoryComments, including the new one
        $testable->assertCanSeeTableRecords($allComments);
        // Optionally, assert the new StoryComment specifically is visible
        $testable->assertCanSeeTableRecords([$newComment]);
    }
}
