<?php

namespace Tests\Feature\Models;

use App\Models\Story;
use App\Models\StoryComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_can_be_created(): void
    {
        // Arrange: Create a user and a story
        $user = User::factory()->create();
        $story = Story::factory()->create();

        // Act: Create a StoryComment
        $storyComment = StoryComment::create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'body' => ['en' => 'This is a test StoryComment.', 'id' => 'Ini adalah komentar uji.'],
        ]);

        // Assert: Check if the StoryComment exists in the database
        $this->assertDatabaseHas('story_comments', [
            'id' => $storyComment->id,
            'creator_id' => $user->id,
            'story_id' => $story->id,
        ]);

        // Assert: Check the body content (Spatie handles JSON)
        $this->assertEquals('This is a test StoryComment.', $storyComment->getTranslation('body', 'en'));
        $this->assertEquals('Ini adalah komentar uji.', $storyComment->getTranslation('body', 'id'));
    }

    public function test_comment_belongs_to_a_creator(): void
    {
        // Arrange: Create a StoryComment using the factory
        $storyComment = StoryComment::factory()->create();

        // Act: Retrieve the creator via the relationship
        $creator = $storyComment->creator;

        // Assert: Check if the creator is a User instance and matches the StoryComment's creator_id
        $this->assertInstanceOf(User::class, $creator);
        $this->assertEquals($storyComment->creator_id, $creator->id);
    }

    public function test_comment_belongs_to_a_story(): void
    {
        // Arrange: Create a StoryComment using the factory
        $storyComment = StoryComment::factory()->create();

        // Act: Retrieve the story via the relationship
        $story = $storyComment->story;

        // Assert: Check if the story is a Story instance and matches the StoryComment's story_id
        $this->assertInstanceOf(Story::class, $story);
        $this->assertEquals($storyComment->story_id, $story->id);
    }

    public function test_user_can_have_many_comments(): void
    {
        // Arrange: Create a user and multiple StoryComments for that user
        /** @var User */
        $user = User::factory()->create();
        StoryComment::factory()->count(3)->create(['creator_id' => $user->id]);

        // Act: Retrieve the user's StoryComments via the relationship
        $userComments = $user->storyComments;

        // Assert: Check if the relationship returns a collection of StoryComments
        $this->assertCount(3, $userComments);
        $this->assertTrue($userComments->every(fn ($storyComment) => $storyComment->creator->is($user)));
    }

    public function test_story_can_have_many_comments(): void
    {
        // Arrange: Create a story and multiple StoryComments for that story
        $story = Story::factory()->create();
        StoryComment::factory()->count(5)->create(['story_id' => $story->id]);

        // Act: Retrieve the story's StoryComments via the relationship
        $storyComments = $story->storyComments;

        // Assert: Check if the relationship returns a collection of StoryComments
        $this->assertCount(5, $storyComments);
        $this->assertTrue($storyComments->every(fn ($storyComment) => $storyComment->story_id === $story->id));
    }

    public function test_comment_can_have_a_parent(): void
    {
        // Arrange: Create a parent StoryComment and a child StoryComment
        $parentComment = StoryComment::factory()->create();
        $childComment = StoryComment::factory()->create(['parent_id' => $parentComment->id]);

        // Act: Retrieve the parent via the relationship from the child
        $retrievedParent = $childComment->parent;

        // Assert: Check if the retrieved parent is a StoryComment instance and matches the parent StoryComment
        $this->assertInstanceOf(StoryComment::class, $retrievedParent);
        $this->assertEquals($parentComment->id, $retrievedParent->id);
    }

    public function test_comment_can_have_many_children(): void
    {
        // Arrange: Create a parent StoryComment and multiple child StoryComments
        $parentComment = StoryComment::factory()->create();
        $childComments = StoryComment::factory()->count(3)->create(['parent_id' => $parentComment->id]);

        // Act: Retrieve the children via the relationship from the parent
        $retrievedChildren = $parentComment->storyComments;

        // Assert: Check if the relationship returns a collection of StoryComments and containsthe correct children
        $this->assertCount(3, $retrievedChildren);
        $this->assertTrue($retrievedChildren->every(fn ($storyComment) => $storyComment->parent_id === $parentComment->id));
        $childComments->each(fn ($child) => $this->assertTrue($retrievedChildren->contains($child)));
    }

    public function test_story_comment_count_increments_on_comment_creation(): void
    {
        // Arrange: Create a story
        $story = Story::factory()->create();
        $initialCommentCount = $story->comment_count;

        // Act: Create a StoryComment for the story
        StoryComment::factory()->create(['story_id' => $story->id]);

        // Assert: Reload the story and check if the StoryComment_count has incremented
        $story->refresh();
        $this->assertEquals($initialCommentCount + 1, $story->comment_count);
    }

    public function test_story_comment_count_decrements_on_comment_deletion(): void
    {
        // Arrange: Create a story and a StoryComment for it
        $story = Story::factory()->create();
        $storyComment = StoryComment::factory()->create(['story_id' => $story->id]);

        // Ensure the count is 1 after creation
        $story->refresh();
        $this->assertEquals(1, $story->comment_count);

        // Act: Delete the StoryComment
        $storyComment->delete();

        // Assert: Reload the story and check if the StoryComment_count has decremented
        $story->refresh();
        $this->assertEquals(0, $story->comment_count);
    }

    public function test_parent_reply_count_increments_on_child_comment_creation(): void
    {
        // Arrange: Create a parent StoryComment
        $parentComment = StoryComment::factory()->create();
        $this->assertEquals(0, $parentComment->reply_count);

        // Act: Create a child StoryComment for the parent
        StoryComment::factory()->create(['parent_id' => $parentComment->id]);

        // Assert: Reload the parent and check if reply_count has incremented
        $parentComment->refresh();
        $this->assertEquals(1, $parentComment->reply_count);

        // Create another child
        StoryComment::factory()->create(['parent_id' => $parentComment->id]);
        $parentComment->refresh();
        $this->assertEquals(2, $parentComment->reply_count);
    }

    public function test_parent_reply_count_decrements_on_child_comment_deletion(): void
    {
        // Arrange: Create a parent StoryComment and two child StoryComments
        $parentComment = StoryComment::factory()->create();
        $childComment1 = StoryComment::factory()->create(['parent_id' => $parentComment->id]);
        $childComment2 = StoryComment::factory()->create(['parent_id' => $parentComment->id]);

        // Ensure the count is 2 after creation
        $parentComment->refresh();
        $this->assertEquals(2, $parentComment->reply_count);

        // Act: Delete one child StoryComment
        $childComment1->delete();

        // Assert: Reload the parent and check if reply_count has decremented
        $parentComment->refresh();
        $this->assertEquals(1, $parentComment->reply_count);

        // Delete the second child StoryComment
        $childComment2->delete();

        // Assert: Reload the parent and check if reply_count has decremented to 0
        $parentComment->refresh();
        $this->assertEquals(0, $parentComment->reply_count);
    }
}
